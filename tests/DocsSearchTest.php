<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\DocsSearch;
use PHPUnit\Framework\TestCase;

class DocsSearchTest extends TestCase
{
    public function testParseHtmlNormalizesVersionedLinks(): void
    {
        $html = <<<'HTML'
<ul class="list-group">
<li class="list-group-item">
<a href="/en/v3/awesome-plugins/twig"><strong>Twig</strong></a>
<p class="text-muted fst-italic">/en/v3/awesome-plugins/twig</p>
<p>Twig is the skeleton default.</p>
</li>
<li class="list-group-item">
<a href="/en/v3/install/install"><strong>Installation Instructions</strong></a>
<p>Start with the skeleton.</p>
</li>
<li class="list-group-item">
<a href="https://github.com/flightphp/core"><strong>Ignore me</strong></a>
</li>
</ul>
HTML;

        $hits = DocsSearch::parseHtml($html);

        $this->assertCount(2, $hits);
        $this->assertSame('Twig', $hits[0]['title']);
        $this->assertSame('https://docs.flightphp.com/awesome-plugins/twig', $hits[0]['url']);
        $this->assertSame('Twig is the skeleton default.', $hits[0]['excerpt']);
        $this->assertSame('https://docs.flightphp.com/install', $hits[1]['url']);
    }

    public function testCatalogHitsFindTwigAndPdoWrapper(): void
    {
        $twig = DocsSearch::catalogHits('twig skeleton');
        $this->assertNotEmpty($twig);
        $this->assertSame('https://docs.flightphp.com/awesome-plugins/twig', $twig[0]['url']);

        $pdo = DocsSearch::catalogHits('PdoWrapper');
        $urls = array_column($pdo, 'url');
        $this->assertContains('https://docs.flightphp.com/learn/pdo-wrapper', $urls);
    }

    public function testMergeKeepsSiteOrderAndAddsCatalogOnlyUrls(): void
    {
        $site = [[
            'title' => 'Twig',
            'url' => 'https://docs.flightphp.com/awesome-plugins/twig',
            'excerpt' => 'from site',
            'source' => 'site',
        ]];
        $catalog = [
            [
                'title' => 'Twig',
                'url' => 'https://docs.flightphp.com/awesome-plugins/twig',
                'excerpt' => 'from catalog',
                'source' => 'catalog',
            ],
            [
                'title' => 'FlightMail',
                'url' => 'https://docs.flightphp.com/awesome-plugins/flightmail',
                'excerpt' => 'mail',
                'source' => 'catalog',
            ],
        ];

        $merged = DocsSearch::merge($site, $catalog);

        $this->assertCount(2, $merged);
        $this->assertSame('from site', $merged[0]['excerpt']);
        $this->assertSame('site', $merged[0]['source']);
        $this->assertSame('https://docs.flightphp.com/awesome-plugins/flightmail', $merged[1]['url']);
    }

    public function testFormatReportsADeadLiveSearch(): void
    {
        $text = DocsSearch::format('twig', [[
            'title' => 'Twig',
            'url' => 'https://docs.flightphp.com/awesome-plugins/twig',
            'excerpt' => 'default engine',
            'source' => 'catalog',
        ]], true);

        $this->assertStringContainsString('Live search was unavailable', $text);
        $this->assertStringContainsString('https://docs.flightphp.com/awesome-plugins/twig', $text);
        $this->assertStringContainsString('(catalog)', $text);
    }
}
