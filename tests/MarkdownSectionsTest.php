<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\MarkdownSections;
use PHPUnit\Framework\TestCase;

class MarkdownSectionsTest extends TestCase
{
    public function testExtractsAHeadingUntilTheNextSameLevelHeading(): void
    {
        $markdown = <<<'MD'
# Routing

## Overview
Intro text.

## Basic Usage
Flight::route('/', function () {});

### Named Parameters
Use @id.

## Advanced Usage
Groups live here.
MD;

        $section = MarkdownSections::extract($markdown, 'basic usage');

        $this->assertStringContainsString('## Basic Usage', $section);
        $this->assertStringContainsString('### Named Parameters', $section);
        $this->assertStringContainsString('Use @id.', $section);
        $this->assertStringNotContainsString('## Advanced Usage', $section);
        $this->assertStringNotContainsString('Intro text.', $section);
    }

    public function testMissingHeadingListsThePageHeadings(): void
    {
        $result = MarkdownSections::extract("# Routing\n\n## Overview\nText.\n", 'Streaming');

        $this->assertStringContainsString("No section matching 'Streaming'", $result);
        $this->assertStringContainsString('- Routing', $result);
        $this->assertStringContainsString('- Overview', $result);
    }
}
