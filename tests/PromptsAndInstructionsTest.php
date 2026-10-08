<?php
declare(strict_types=1);

namespace flight\mcp\Tests;

use flight\mcp\Prompts;
use flight\mcp\ServerInfo;
use PhpMcp\Server\Server;
use PHPUnit\Framework\TestCase;

class PromptsAndInstructionsTest extends TestCase
{
    public function testNewProjectPromptUsesTheSkeleton(): void
    {
        $prompt = (new Prompts())->newFlightphpProject('a book shop');
        $text = $prompt[0]['content'];

        $this->assertStringContainsString("get_docs_page('install')", $text);
        $this->assertStringContainsString("get_docs_page('ai')", $text);
        $this->assertStringContainsString('composer create-project flightphp/skeleton', $text);
        $this->assertStringContainsString('app/config/routes.php', $text);
        $this->assertStringContainsString('Twig', $text);
        $this->assertStringContainsString('AGENTS.md', $text);
        $this->assertStringContainsString('a book shop', $text);
    }

    public function testPluginPromptNamesTheRequestedPlugin(): void
    {
        $text = (new Prompts())->useFlightphpPlugin('twig')[0]['content'];

        $this->assertStringContainsString("get_plugin_docs('twig')", $text);
        $this->assertStringContainsString('app/config/services.php', $text);
    }

    public function testServerInstructionsStateTheSkeletonRules(): void
    {
        $instructions = ServerInfo::INSTRUCTIONS;

        $this->assertStringContainsString('composer create-project flightphp/skeleton', $instructions);
        $this->assertStringContainsString('get_docs_page("install")', $instructions);
        $this->assertStringContainsString('Flight::get()', $instructions);
        $this->assertStringContainsString('PdoWrapper', $instructions);
        $this->assertStringContainsString('Twig', $instructions);
        $this->assertSame('1.2.0', ServerInfo::VERSION);
    }

    public function testDiscoveryRegistersTheCurrentToolSet(): void
    {
        $server = Server::make()
            ->withServerInfo(ServerInfo::NAME, ServerInfo::VERSION)
            ->withInstructions(ServerInfo::INSTRUCTIONS)
            ->build();
        $server->discover(
            basePath: dirname(__DIR__),
            scanDirs: ['src'],
            saveToCache: false,
        );

        $tools = array_keys($server->getRegistry()->getTools());
        foreach ([
            'get_docs_page',
            'get_docs_section',
            'list_docs_pages',
            'get_guide_page',
            'get_plugin_docs',
            'search_docs',
            'lookup_api',
            'fetch_url',
            'generate_learn_page',
            'generate_plugin_page',
            'generate_guide_page',
        ] as $tool) {
            $this->assertContains($tool, $tools);
        }

        $this->assertArrayHasKey('new_flightphp_project', $server->getRegistry()->getPrompts());
        $this->assertArrayHasKey('flightphp://docs/index', $server->getRegistry()->getResources());
        $this->assertArrayHasKey('flightphp://docs/{topic}', $server->getRegistry()->getResourceTemplates());
        $this->assertStringContainsString('skeleton', (string) $server->getConfiguration()->instructions);
    }
}
