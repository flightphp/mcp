<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\ApiNotes;
use PHPUnit\Framework\TestCase;

class ApiNotesTest extends TestCase
{
    public function testFlightGetIsNotARoute(): void
    {
        $note = ApiNotes::explain('Flight::get()');

        $this->assertStringContainsString('does not register a GET route', $note);
        $this->assertStringContainsString('get_docs_page("routing")', $note);
        $this->assertStringNotContainsString('https://docs.flightphp.com/learn/autoloading', $note);
    }

    public function testPdoWrapperIsDeprecated(): void
    {
        $note = ApiNotes::explain('Pdo Wrapper');

        $this->assertStringContainsString('deprecated', strtolower($note));
        $this->assertStringContainsString('get_docs_page("simple-pdo")', $note);
        $this->assertStringContainsString('get_docs_page("pdo-wrapper")', $note);
        $this->assertStringNotContainsString('Closest catalog pages', $note);
    }

    public function testUnknownSymbolDoesNotInventASignature(): void
    {
        $note = ApiNotes::explain('Flight::teleport');

        $this->assertStringContainsString('No curated note', $note);
        $this->assertStringContainsString('Do not invent a Flight method', $note);
        $this->assertStringNotContainsString('function teleport', $note);
    }
}
