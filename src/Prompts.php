<?php
declare(strict_types=1);

namespace flight\mcp;

use flight\mcp\Completion\LearnTopicCompletion;
use flight\mcp\Completion\PluginCompletion;
use PhpMcp\Server\Attributes\CompletionProvider;
use PhpMcp\Server\Attributes\McpPrompt;
use PhpMcp\Server\Attributes\Schema;

class Prompts
{
    #[McpPrompt(
        name: 'new_flightphp_project',
        description: 'Start a new FlightPHP application from the official skeleton. Instructs the AI to read '
            . 'the install, AI, autoloading, and routing documentation before generating any code.',
    )]
    public function newFlightphpProject(
        #[Schema(description: 'Brief description of the application to build')]
        string $description = ''
    ): array {
        $appDesc = $description !== '' ? " The app is: $description." : '';

        return [['role' => 'user', 'content' =>
            "I want to start a new FlightPHP application.$appDesc\n\n"
            . "Before writing any code, you MUST:\n"
            . "1. Call get_docs_page('install') and use the official skeleton: composer create-project flightphp/skeleton\n"
            . "2. Call get_docs_page('ai') for AGENTS.md, Twig, and the \$app / \$this->app layout\n"
            . "3. Call get_docs_page('autoloading') and get_docs_page('routing')\n"
            . "4. Call get_docs_page('configuration') if you need config keys\n\n"
            . "Scaffold the skeleton layout. Put routes in app/config/routes.php and services in app/config/services.php. "
            . "Use App\\ namespaces with PascalCase folders, constructor injection, and Twig. "
            . "Treat the project's AGENTS.md as the source of truth. "
            . "Short Flight:: examples in the docs are for teaching APIs. Do not copy that style into skeleton controllers."
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
        #[Schema(description: 'Primary documentation topic slug, for example "routing", "middleware", "install"')]
        #[CompletionProvider(provider: LearnTopicCompletion::class)]
        string $primaryTopic = 'routing'
    ): array {
        return [['role' => 'user', 'content' =>
            "I need to implement the following feature in my FlightPHP application: $feature\n\n"
            . "Before writing any code, you MUST:\n"
            . "1. Call get_docs_page('$primaryTopic') to read the official FlightPHP documentation for this topic\n"
            . "2. If the feature touches additional topics, call get_docs_page() for those too\n"
            . "3. Call lookup_api() if you are about to use Flight::get(), PdoWrapper, Flight::render(), or a route return value\n"
            . "4. Call list_docs_pages() if you are unsure which topics are relevant\n\n"
            . "Match the layout the project already uses. In a skeleton app that means \$this->app, App\\ namespaces, and Twig. "
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
        #[Schema(description: 'Area of the framework involved, for example "routing" or "middleware"')]
        #[CompletionProvider(provider: LearnTopicCompletion::class)]
        string $area = ''
    ): array {
        $areaHint = $area !== ''
            ? "1. Call get_docs_page('$area') to review the relevant FlightPHP documentation\n"
            : "1. Call list_docs_pages() to identify which topic is most relevant, then call get_docs_page() for that topic\n";

        return [['role' => 'user', 'content' =>
            "I have the following problem in my FlightPHP application: $problem\n\n"
            . "Before diagnosing, you MUST:\n"
            . $areaHint
            . "2. Call lookup_api() if the failure involves routing helpers, PdoWrapper, views, or a route that returns a value\n"
            . "3. Compare the documented behaviour against my code\n\n"
            . "Then provide a diagnosis and fix based on the official documentation."
        ]];
    }

    #[McpPrompt(
        name: 'flightphp_migration_help',
        description: 'Help migrate from another PHP framework or from FlightPHP v2 to v3. '
            . 'Instructs the AI to read the comparison, migration, and install documentation first.',
    )]
    public function flightphpMigrationHelp(
        #[Schema(description: 'The framework or version being migrated from')]
        #[CompletionProvider(values: ['laravel', 'slim', 'lumen', 'symfony', 'flightphp-v2', 'other'])]
        string $fromFramework = 'other'
    ): array {
        $docsCalls = $fromFramework === 'flightphp-v2'
            ? "1. Call get_docs_page('migrating-to-v3') to read the official migration guide\n"
            . "2. Call get_docs_page('install') for the current skeleton layout\n"
            . "3. Call get_docs_page('flight-vs-another-framework') for context on framework differences\n"
            : "1. Call get_docs_page('flight-vs-another-framework') to understand FlightPHP compared to $fromFramework\n"
            . "2. Call get_docs_page('install'), get_docs_page('routing'), and get_docs_page('configuration')\n"
            . "3. Call get_docs_page('ai') if the target app should follow the official skeleton\n";

        return [['role' => 'user', 'content' =>
            "I am migrating a PHP application from $fromFramework to FlightPHP.\n\n"
            . "Before giving migration advice, you MUST:\n"
            . $docsCalls
            . "4. Call list_docs_pages() to identify any other relevant topics\n"
            . "5. Prefer SimplePdo over PdoWrapper, and Twig when the target app uses the official skeleton\n\n"
            . "Then provide a migration plan based on the official FlightPHP documentation."
        ]];
    }

    #[McpPrompt(
        name: 'use_flightphp_plugin',
        description: 'Integrate a FlightPHP plugin or extension into an application. Instructs the AI '
            . 'to read plugin documentation before writing any integration code.',
    )]
    public function useFlightphpPlugin(
        #[Schema(description: 'The plugin to integrate, for example "twig", "active-record", "session"')]
        #[CompletionProvider(provider: PluginCompletion::class)]
        string $plugin
    ): array {
        return [['role' => 'user', 'content' =>
            "I want to integrate the FlightPHP plugin '$plugin' into my application.\n\n"
            . "Before writing any integration code, you MUST:\n"
            . "1. Call get_plugin_docs('$plugin') to read the official plugin documentation\n"
            . "2. Call get_docs_page('dependency-injection-container') if the plugin is registered in the container\n"
            . "3. In a skeleton app, register services in app/config/services.php and keep controllers on \$this->app\n"
            . "4. Call list_plugin_pages() if you are unsure this is the right plugin for the task\n\n"
            . "Only after reading the documentation should you write integration code."
        ]];
    }
}
