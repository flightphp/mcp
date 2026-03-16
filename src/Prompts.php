<?php
declare(strict_types=1);

namespace flight\mcp;

use PhpMcp\Server\Attributes\CompletionProvider;
use PhpMcp\Server\Attributes\McpPrompt;
use PhpMcp\Server\Attributes\Schema;

class Prompts
{
    #[McpPrompt(
        name: 'new_flightphp_project',
        description: 'Start a new FlightPHP application. Instructs the AI to read routing, '
            . 'configuration, and autoloading documentation before generating any code.',
    )]
    public function newFlightphpProject(
        #[Schema(description: 'Brief description of the application to build')]
        string $description = ''
    ): array {
        $appDesc = $description !== '' ? " The app is: $description." : '';
        return [['role' => 'user', 'content' =>
            "I want to start a new FlightPHP application.$appDesc\n\n"
            . "Before writing any code, you MUST:\n"
            . "1. Call get_docs_page('routing') to learn FlightPHP routing\n"
            . "2. Call get_docs_page('configuration') to learn configuration options\n"
            . "3. Call get_docs_page('autoloading') to learn class autoloading\n\n"
            . "After reading the docs, scaffold the project with correct FlightPHP patterns."
        ]];
    }

    #[McpPrompt(
        name: 'implement_flightphp_feature',
        description: 'Implement a feature in a FlightPHP application. Instructs the AI to '
            . 'fetch the relevant documentation topic before writing any code.',
    )]
    public function implementFlightphpFeature(
        #[Schema(description: 'Description of the feature to implement')]
        string $feature,
        #[Schema(description: 'Primary documentation topic slug (e.g. "routing", "middleware", "security")')]
        #[CompletionProvider(values: ['routing', 'middleware', 'requests', 'responses', 'templates',
            'configuration', 'autoloading', 'security', 'events', 'extending', 'filtering',
            'collections', 'json', 'simple-pdo', 'dependency-injection-container', 'unit-testing',
            'uploaded-file', 'ai', 'migrating-to-v3', 'why-frameworks', 'flight-vs-another-framework'])]
        string $primaryTopic = 'routing'
    ): array {
        return [['role' => 'user', 'content' =>
            "I need to implement the following feature in my FlightPHP application: $feature\n\n"
            . "Before writing any code, you MUST:\n"
            . "1. Call get_docs_page('$primaryTopic') to read the official FlightPHP documentation for this topic\n"
            . "2. If the feature touches additional topics, call get_docs_page() for those too\n"
            . "3. Call list_docs_pages() if you are unsure which topics are relevant\n\n"
            . "Only after reading the relevant documentation should you implement the feature."
        ]];
    }

    #[McpPrompt(
        name: 'debug_flightphp_issue',
        description: 'Debug a problem in a FlightPHP application. Instructs the AI to check '
            . 'the relevant documentation before diagnosing.',
    )]
    public function debugFlightphpIssue(
        #[Schema(description: 'Description of the problem or error')]
        string $problem,
        #[Schema(description: 'Area of the framework involved (optional), e.g. "routing", "middleware"')]
        #[CompletionProvider(values: ['routing', 'middleware', 'requests', 'responses', 'templates',
            'configuration', 'autoloading', 'security', 'events', 'extending', 'filtering',
            'collections', 'json', 'simple-pdo', 'dependency-injection-container', 'unit-testing',
            'uploaded-file', 'ai', 'migrating-to-v3', 'why-frameworks', 'flight-vs-another-framework'])]
        string $area = ''
    ): array {
        $areaHint = $area !== ''
            ? "1. Call get_docs_page('$area') to review the relevant FlightPHP documentation\n"
            : "1. Call list_docs_pages() to identify which topic is most relevant, then call get_docs_page() for that topic\n";

        return [['role' => 'user', 'content' =>
            "I have the following problem in my FlightPHP application: $problem\n\n"
            . "Before diagnosing, you MUST:\n"
            . $areaHint
            . "2. Compare the documented behaviour against my code\n\n"
            . "Then provide a diagnosis and fix based on the official documentation."
        ]];
    }

    #[McpPrompt(
        name: 'flightphp_migration_help',
        description: 'Help migrate from another PHP framework or from FlightPHP v2 to v3. '
            . 'Instructs the AI to read the comparison and migration documentation first.',
    )]
    public function flightphpMigrationHelp(
        #[Schema(description: 'The framework or version being migrated from')]
        #[CompletionProvider(values: ['laravel', 'slim', 'lumen', 'symfony', 'flightphp-v2', 'other'])]
        string $fromFramework = 'other'
    ): array {
        $docsCalls = $fromFramework === 'flightphp-v2'
            ? "1. Call get_docs_page('migrating-to-v3') to read the official migration guide\n"
            . "2. Call get_docs_page('flight-vs-another-framework') for context on framework differences\n"
            : "1. Call get_docs_page('flight-vs-another-framework') to understand FlightPHP compared to $fromFramework\n"
            . "2. Call get_docs_page('routing') and get_docs_page('configuration') for FlightPHP fundamentals\n";

        return [['role' => 'user', 'content' =>
            "I am migrating a PHP application from $fromFramework to FlightPHP.\n\n"
            . "Before giving migration advice, you MUST:\n"
            . $docsCalls
            . "3. Call list_docs_pages() to identify any other relevant topics\n\n"
            . "Then provide a migration plan based on the official FlightPHP documentation."
        ]];
    }

    #[McpPrompt(
        name: 'use_flightphp_plugin',
        description: 'Integrate a FlightPHP plugin or extension into an application. Instructs the AI '
            . 'to read plugin documentation before writing any integration code.',
    )]
    public function useFlightphpPlugin(
        #[Schema(description: 'The plugin to integrate, e.g. "active-record", "session", "jwt"')]
        #[CompletionProvider(values: ['active-record', 'apm', 'async', 'comment-template', 'easy-query',
            'ghost-session', 'jwt', 'latte', 'migrations', 'n0nag0n_wordpress', 'permissions',
            'php-cookie', 'php-encryption', 'php-file-cache', 'runway', 'session',
            'simple-job-queue', 'tracy', 'tracy-extensions'])]
        string $plugin
    ): array {
        return [['role' => 'user', 'content' =>
            "I want to integrate the FlightPHP plugin '$plugin' into my application.\n\n"
            . "Before writing any integration code, you MUST:\n"
            . "1. Call get_plugin_docs('$plugin') to read the official plugin documentation\n"
            . "2. Call get_docs_page('dependency-injection-container') if the plugin requires DI registration\n"
            . "3. Call list_plugin_pages() if you are unsure this is the right plugin for the task\n\n"
            . "Only after reading the documentation should you write integration code."
        ]];
    }
}
