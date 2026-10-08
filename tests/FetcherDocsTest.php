<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\DocsClient;
use flight\mcp\Fetcher;
use PHPUnit\Framework\TestCase;

class FetcherDocsTest extends TestCase
{
    public function testGetDocsPageUsesTheCanonicalUrl(): void
    {
        $seen = [];
        $fetcher = $this->fetcher(function (string $url) use (&$seen): array {
            $seen[] = $url;

            return ['status' => 200, 'headers' => [], 'body' => "body of $url"];
        });

        $this->assertSame(
            'body of https://docs.flightphp.com/install',
            $fetcher->getDocsPage('install')
        );
        $this->assertSame(
            'body of https://docs.flightphp.com/awesome-plugins/twig',
            $fetcher->getPluginDocs('twig')
        );
        $this->assertSame(
            'body of https://docs.flightphp.com/guides/blog',
            $fetcher->getGuidePage('blog')
        );
        $this->assertSame([
            'https://docs.flightphp.com/install',
            'https://docs.flightphp.com/awesome-plugins/twig',
            'https://docs.flightphp.com/guides/blog',
        ], $seen);
    }

    public function testGetDocsSectionSlicesTheFetchedPage(): void
    {
        $fetcher = $this->fetcher(function (): array {
            return ['status' => 200, 'headers' => [], 'body' => "# Install\n\n## Recommended Install\nUse the skeleton.\n\n## Basic Install\ncomposer require flightphp/core\n"];
        });

        $section = $fetcher->getDocsSection('install', 'Recommended Install');

        $this->assertStringContainsString('Use the skeleton.', $section);
        $this->assertStringNotContainsString('composer require flightphp/core', $section);
    }

    public function testSearchFallsBackToTheCatalogWhenTheSiteFails(): void
    {
        $fetcher = $this->fetcher(function (): array {
            return ['status' => 500, 'headers' => [], 'body' => ''];
        });

        $results = $fetcher->searchDocs('twig');

        $this->assertStringContainsString('Live search was unavailable', $results);
        $this->assertStringContainsString('https://docs.flightphp.com/awesome-plugins/twig', $results);
    }

    public function testSearchMergesSiteResultsWithTheCatalog(): void
    {
        $fetcher = $this->fetcher(function (string $url, string $accept): array {
            TestCase::assertStringContainsString('/en/v3/search?q=', $url);
            TestCase::assertSame('text/html', $accept);

            return [
                'status' => 200,
                'headers' => [],
                'body' => '<li class="list-group-item"><a href="/en/v3/learn/templates"><strong>Templates</strong></a><p>Twig lives here.</p></li>',
            ];
        });

        $results = $fetcher->searchDocs('twig');

        $this->assertStringContainsString('https://docs.flightphp.com/learn/templates', $results);
        $this->assertStringContainsString('https://docs.flightphp.com/awesome-plugins/twig', $results);
        $this->assertStringNotContainsString('/en/v3/', $results);
    }

    public function testFetchUrlRejectsOtherHosts(): void
    {
        $fetcher = $this->fetcher(function (): array {
            return ['status' => 200, 'headers' => [], 'body' => 'nope'];
        });

        $this->expectException(\InvalidArgumentException::class);
        $fetcher->fetchUrl('https://example.com/learn/routing');
    }

    public function testLookupApiIsExposedThroughTheTool(): void
    {
        $fetcher = new Fetcher();
        $note = $fetcher->lookupApi('Flight::render');

        $this->assertStringContainsString('Twig', $note);
        $this->assertStringContainsString('get_plugin_docs("twig")', $note);
    }

    /**
     * @param callable(string, string): array{status: int, headers: array<string, list<string>>, body: string} $transport
     */
    private function fetcher(callable $transport): Fetcher
    {
        return new Fetcher(new DocsClient(transport: $transport, cacheFile: ''));
    }
}
