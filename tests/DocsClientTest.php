<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\DocsClient;
use PHPUnit\Framework\TestCase;

class DocsClientTest extends TestCase
{
    public function testRequestHeadersIdentifyTheServer(): void
    {
        $headers = DocsClient::requestHeaders('text/html');

        $this->assertStringContainsString('Accept: text/html', $headers);
        $this->assertStringContainsString('User-Agent: FlightPHP-MCP/', $headers);
        $this->assertStringContainsString('github.com/flightphp/mcp', $headers);
    }

    public function testRejectsOffSiteUrlBeforeAnyRequest(): void
    {
        $calls = 0;
        $client = $this->client(function () use (&$calls): array {
            $calls++;

            return ['status' => 200, 'headers' => [], 'body' => 'nope'];
        });

        $this->expectException(\InvalidArgumentException::class);
        try {
            $client->fetch('https://example.com/secret');
        } finally {
            $this->assertSame(0, $calls);
        }
    }

    public function testFollowsSameHostRedirect(): void
    {
        $seen = [];
        $client = $this->client(function (string $url) use (&$seen): array {
            $seen[] = $url;
            if ($url === 'https://docs.flightphp.com/learn/routing') {
                return [
                    'status' => 303,
                    'headers' => ['location' => ['/en/v3/learn/routing']],
                    'body' => '',
                ];
            }

            return ['status' => 200, 'headers' => [], 'body' => '# Routing'];
        });

        $this->assertSame('# Routing', $client->fetch('https://docs.flightphp.com/learn/routing'));
        $this->assertSame([
            'https://docs.flightphp.com/learn/routing',
            'https://docs.flightphp.com/en/v3/learn/routing',
        ], $seen);
    }

    public function testRefusesRedirectOffTheDocsHost(): void
    {
        $client = $this->client(function (): array {
            return [
                'status' => 302,
                'headers' => ['location' => ['https://evil.example/phish']],
                'body' => '',
            ];
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refused to follow a redirect');
        $client->fetch('https://docs.flightphp.com/learn/routing');
    }

    public function testCachesASuccessfulResponse(): void
    {
        $calls = 0;
        $cache = tempnam(sys_get_temp_dir(), 'flight-mcp-');
        $this->assertIsString($cache);
        $client = new DocsClient(
            transport: function () use (&$calls): array {
                $calls++;

                return ['status' => 200, 'headers' => [], 'body' => 'cached-body'];
            },
            cacheFile: $cache,
        );

        try {
            $this->assertSame('cached-body', $client->fetch('https://docs.flightphp.com/install'));
            $this->assertSame('cached-body', $client->fetch('https://docs.flightphp.com/install'));
            $this->assertSame(1, $calls);

            $second = new DocsClient(
                transport: function () use (&$calls): array {
                    $calls++;

                    return ['status' => 200, 'headers' => [], 'body' => 'fresh'];
                },
                cacheFile: $cache,
            );
            $this->assertSame('cached-body', $second->fetch('https://docs.flightphp.com/install'));
            $this->assertSame(1, $calls);
        } finally {
            @unlink($cache);
        }
    }

    /**
     * @param callable(string, string): array{status: int, headers: array<string, list<string>>, body: string} $transport
     */
    private function client(callable $transport): DocsClient
    {
        return new DocsClient(transport: $transport, cacheFile: '');
    }
}
