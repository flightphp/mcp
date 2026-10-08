<?php
declare(strict_types=1);

namespace flight\mcp;

final class DocsSearch
{
    /**
     * @return list<array{title: string, url: string, excerpt: string, source: string}>
     */
    public static function parseHtml(string $html): array
    {
        if (preg_match_all('/<li[^>]*class="[^"]*list-group-item[^"]*"[^>]*>(.*?)<\/li>/is', $html, $items) === 0) {
            return [];
        }

        $results = [];
        foreach ($items[1] as $item) {
            if (preg_match('/<a\s+[^>]*href="([^"]+)"[^>]*>\s*<strong>(.*?)<\/strong>/is', $item, $match) !== 1) {
                continue;
            }
            $url = DocsCatalog::canonicalize($match[1]);
            if ($url === null) {
                continue;
            }
            $excerpt = '';
            if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $item, $paragraphs) !== 0) {
                $text = (string) end($paragraphs[1]);
                $excerpt = self::plainText($text);
            }
            $results[] = [
                'title' => self::plainText($match[2]),
                'url' => $url,
                'excerpt' => $excerpt,
                'source' => 'site',
            ];
        }

        return $results;
    }

    /**
     * @return list<array{title: string, url: string, excerpt: string, source: string}>
     */
    public static function catalogHits(string $query): array
    {
        $tokens = self::tokens($query);
        if ($tokens === []) {
            return [];
        }

        $scored = [];
        foreach (DocsCatalog::entries() as $entry) {
            $description = strtolower($entry['description']);
            $score = 0;
            foreach ($tokens as $token) {
                if (str_contains($entry['slug'], $token)) {
                    $score += strlen($token) < 4 ? 2 : 1;
                } elseif (strlen($token) >= 4 && str_contains($description, $token)) {
                    $score++;
                }
                if ($token === $entry['slug']) {
                    $score += 3;
                }
            }
            if ($score === 0) {
                continue;
            }
            $title = explode(' - ', $entry['description'], 2)[0];
            $scored[] = [
                'score' => $score,
                'hit' => [
                    'title' => $title,
                    'url' => $entry['url'],
                    'excerpt' => $entry['description'],
                    'source' => 'catalog',
                ],
            ];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(static fn (array $row): array => $row['hit'], $scored);
    }

    /**
     * Site hits keep their order. Catalog hits fill in URLs the site list missed.
     *
     * @param list<array{title: string, url: string, excerpt: string, source: string}> $site
     * @param list<array{title: string, url: string, excerpt: string, source: string}> $catalog
     * @return list<array{title: string, url: string, excerpt: string, source: string}>
     */
    public static function merge(array $site, array $catalog): array
    {
        $seen = [];
        $merged = [];
        foreach ([...$site, ...$catalog] as $hit) {
            if (isset($seen[$hit['url']])) {
                continue;
            }
            $seen[$hit['url']] = true;
            $merged[] = $hit;
        }

        return $merged;
    }

    /**
     * @param list<array{title: string, url: string, excerpt: string, source: string}> $results
     */
    public static function format(string $query, array $results, bool $liveFailed): string
    {
        if ($results === []) {
            $prefix = $liveFailed ? "Live search was unavailable. " : '';

            return $prefix . "No results found for '$query' in the FlightPHP documentation.";
        }

        $lines = ["FlightPHP docs search results for '$query':\n"];
        if ($liveFailed) {
            $lines[] = "Live search was unavailable. These matches are from the local page catalog.\n";
        }
        foreach ($results as $i => $hit) {
            $excerpt = $hit['excerpt'] !== '' ? ' — ' . substr($hit['excerpt'], 0, 160) : '';
            $source = $hit['source'] === 'catalog' ? ' (catalog)' : '';
            $lines[] = sprintf(
                "  [%d] %s%s\n      URL: %s%s",
                $i + 1,
                $hit['title'],
                $source,
                $hit['url'],
                $excerpt
            );
        }
        $lines[] = sprintf(
            "\n%d result(s). Fetch a page with get_docs_page(), get_guide_page(), get_plugin_docs(), get_docs_section(), or fetch_url().",
            count($results)
        );

        return implode("\n", $lines);
    }

    /** @return list<string> */
    private static function tokens(string $query): array
    {
        $query = strtolower(trim($query));
        if ($query === '') {
            return [];
        }
        $parts = preg_split('/[^a-z0-9]+/', $query) ?: [];
        $stopwords = ['flight', 'flightphp', 'page', 'docs', 'the', 'and', 'for', 'with'];

        return array_values(array_filter(
            $parts,
            static fn (string $part): bool => $part !== '' && !in_array($part, $stopwords, true)
        ));
    }

    private static function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
