<?php
declare(strict_types=1);

namespace flight\mcp;

/**
 * Fetches docs.flightphp.com pages.
 *
 * Redirects are followed by hand so a Location header cannot leave the docs host.
 * Successful responses are cached for ten minutes.
 */
final class DocsClient
{
    public const USER_AGENT = 'FlightPHP-MCP/' . ServerInfo::VERSION . ' (+https://github.com/flightphp/mcp)';
    private const TTL_SECONDS = 600;
    private const MAX_REDIRECTS = 5;

    /** @var array<string, array{expires: int, body: string}> */
    private array $memory = [];

    /**
     * @param null|callable(string, string): array{status: int, headers: array<string, list<string>>, body: string} $transport
     * @param string|null $cacheFile Path to the JSON cache. Null uses the system temp directory. An empty string disables the file cache.
     */
    public function __construct(
        private mixed $transport = null,
        private ?string $cacheFile = null,
    ) {
    }

    public function fetch(string $url, string $accept = 'text/plain, text/markdown;q=0.9'): string
    {
        $this->assertDocsUrl($url, false);
        $key = $accept . ' ' . $url;
        $cached = $this->cacheGet($key);
        if ($cached !== null) {
            return $cached;
        }

        $body = $this->download($url, $accept);
        $this->cacheSet($key, $body);

        return $body;
    }

    public static function requestHeaders(string $accept): string
    {
        return "Accept: {$accept}\r\nUser-Agent: " . self::USER_AGENT . "\r\n";
    }

    private function download(string $url, string $accept): string
    {
        $current = $url;
        for ($hop = 0; $hop < self::MAX_REDIRECTS; $hop++) {
            $this->assertDocsUrl($current, $hop > 0);
            $response = $this->request($current, $accept);
            $status = $response['status'];
            $location = $response['headers']['location'][0] ?? null;
            if ($status >= 300 && $status < 400 && is_string($location) && $location !== '') {
                $current = $this->resolveUrl($current, $location);
                continue;
            }
            if ($status < 200 || $status >= 300 || $response['body'] === '') {
                throw new \RuntimeException(
                    "Failed to fetch '$url'. The page may not exist or the docs site may be unreachable."
                );
            }

            return $response['body'];
        }

        throw new \RuntimeException("Failed to fetch '$url'. Too many redirects.");
    }

    /**
     * @return array{status: int, headers: array<string, list<string>>, body: string}
     */
    private function request(string $url, string $accept): array
    {
        if ($this->transport !== null) {
            return ($this->transport)($url, $accept);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => self::requestHeaders($accept),
                'timeout' => 15,
                'follow_location' => 0,
                'max_redirects' => 0,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        $rawHeaders = $http_response_header ?? [];
        if ($body === false && $rawHeaders === []) {
            throw new \RuntimeException(
                "Failed to fetch '$url'. The page may not exist or the docs site may be unreachable."
            );
        }

        return [
            'status' => self::statusFrom($rawHeaders),
            'headers' => self::parseHeaders($rawHeaders),
            'body' => $body === false ? '' : $body,
        ];
    }

    private function assertDocsUrl(string $url, bool $fromRedirect): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowed = $scheme === 'https'
            && $host === 'docs.flightphp.com'
            && !isset($parts['user'])
            && !isset($parts['pass']);
        if ($allowed) {
            return;
        }
        if ($fromRedirect) {
            throw new \RuntimeException('Refused to follow a redirect off https://docs.flightphp.com/.');
        }

        throw new \InvalidArgumentException(
            'Only URLs on https://docs.flightphp.com/ are permitted. Use get_docs_page() for standard topics.'
        );
    }

    private function resolveUrl(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location) === 1) {
            return $location;
        }

        $parts = parse_url($base);
        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'docs.flightphp.com');
        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }

        $path = (string) ($parts['path'] ?? '/');
        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $origin . $dir . '/' . $location;
    }

    /** @param list<string> $rawHeaders */
    private static function statusFrom(array $rawHeaders): int
    {
        if ($rawHeaders === []) {
            return 0;
        }
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $rawHeaders[0], $match) === 1) {
            return (int) $match[1];
        }

        return 0;
    }

    /**
     * @param list<string> $rawHeaders
     * @return array<string, list<string>>
     */
    private static function parseHeaders(array $rawHeaders): array
    {
        $headers = [];
        foreach ($rawHeaders as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))][] = trim($value);
        }

        return $headers;
    }

    private function cacheGet(string $key): ?string
    {
        $now = time();
        if (isset($this->memory[$key]) && $this->memory[$key]['expires'] >= $now) {
            return $this->memory[$key]['body'];
        }

        $path = $this->cachePath();
        if ($path === null || !is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded) || !isset($decoded[$key]['body'], $decoded[$key]['expires'])) {
            return null;
        }
        if ((int) $decoded[$key]['expires'] < $now || !is_string($decoded[$key]['body'])) {
            return null;
        }

        $this->memory[$key] = [
            'expires' => (int) $decoded[$key]['expires'],
            'body' => $decoded[$key]['body'],
        ];

        return $decoded[$key]['body'];
    }

    private function cacheSet(string $key, string $body): void
    {
        $entry = ['expires' => time() + self::TTL_SECONDS, 'body' => $body];
        $this->memory[$key] = $entry;

        $path = $this->cachePath();
        if ($path === null) {
            return;
        }

        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return;
            }
            $raw = stream_get_contents($handle);
            $data = is_string($raw) ? json_decode($raw, true) : null;
            if (!is_array($data)) {
                $data = [];
            }
            $now = time();
            foreach ($data as $existingKey => $existing) {
                if (!is_array($existing) || (int) ($existing['expires'] ?? 0) < $now) {
                    unset($data[$existingKey]);
                }
            }
            $data[$key] = $entry;
            $encoded = json_encode($data);
            if (!is_string($encoded)) {
                return;
            }
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, $encoded);
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function cachePath(): ?string
    {
        if ($this->cacheFile === '') {
            return null;
        }

        return $this->cacheFile ?? sys_get_temp_dir() . '/flightphp-mcp-docs.json';
    }
}
