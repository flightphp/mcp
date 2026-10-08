<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\DocsCatalog;
use PHPUnit\Framework\TestCase;

class DocsCatalogTest extends TestCase
{
    public function testContentUrlsAreUnversioned(): void
    {
        $this->assertSame('https://docs.flightphp.com/learn/routing', DocsCatalog::docUrl('routing'));
        $this->assertSame('https://docs.flightphp.com/learn/pdo-wrapper', DocsCatalog::docUrl('pdo-wrapper'));
        $this->assertSame('https://docs.flightphp.com/install', DocsCatalog::docUrl('install'));
        $this->assertSame('https://docs.flightphp.com/guides/blog', DocsCatalog::guideUrl('blog'));
        $this->assertSame('https://docs.flightphp.com/awesome-plugins/twig', DocsCatalog::pluginUrl('twig'));
        $this->assertSame(
            'https://docs.flightphp.com/en/v3/search?q=twig%20mail',
            DocsCatalog::searchUrl('twig mail')
        );
    }

    public function testUnknownSlugNamesTheListTool(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('list_docs_pages()');
        DocsCatalog::docUrl('not-a-page');
    }

    public function testCanonicalizeStripsVersionAndCollapsesIndexPaths(): void
    {
        $this->assertSame(
            'https://docs.flightphp.com/learn/templates',
            DocsCatalog::canonicalize('/en/v3/learn/templates')
        );
        $this->assertSame(
            'https://docs.flightphp.com/awesome-plugins/twig',
            DocsCatalog::canonicalize('https://docs.flightphp.com/en/v3/awesome-plugins/twig')
        );
        $this->assertSame(
            'https://docs.flightphp.com/install',
            DocsCatalog::canonicalize('/en/v3/install/install')
        );
        $this->assertNull(DocsCatalog::canonicalize('https://github.com/flightphp/core'));
        $this->assertNull(DocsCatalog::canonicalize('/en/v3/search'));
    }

    public function testCatalogCoversPublishedLearnPages(): void
    {
        $this->assertCatalogCovers('learn-index.md', 'learn', DocsCatalog::LEARN);
    }

    public function testCatalogCoversPublishedGuides(): void
    {
        $this->assertCatalogCovers('guides-index.md', 'guides', DocsCatalog::GUIDES);
    }

    public function testCatalogCoversPublishedPlugins(): void
    {
        $this->assertCatalogCovers('plugins-index.md', 'awesome-plugins', DocsCatalog::PLUGINS);
    }

    public function testListsMentionNewPages(): void
    {
        $docs = DocsCatalog::listDocs();
        $this->assertStringContainsString('install:', $docs);
        $this->assertStringContainsString('pdo-wrapper:', $docs);

        $plugins = DocsCatalog::listPlugins();
        $this->assertStringContainsString('twig:', $plugins);
        $this->assertStringContainsString('flightmail:', $plugins);
        $this->assertStringContainsString('mcp:', $plugins);
    }

    /** @param array<string, string> $catalog */
    private function assertCatalogCovers(string $fixture, string $prefix, array $catalog): void
    {
        $markdown = (string) file_get_contents(__DIR__ . '/fixtures/' . $fixture);
        preg_match_all('#\]\(/' . preg_quote($prefix, '#') . '/([a-z0-9_-]+)\)#', $markdown, $matches);
        $published = array_values(array_unique($matches[1]));
        $this->assertNotEmpty($published, "Fixture $fixture did not contain /$prefix/ links.");

        $missing = array_values(array_diff($published, array_keys($catalog)));
        $this->assertSame([], $missing, "Catalog is missing slugs from $fixture: " . implode(', ', $missing));
    }
}
