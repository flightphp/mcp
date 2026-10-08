<?php
declare(strict_types=1);

namespace flight\mcp;

use flight\mcp\Completion\GuideCompletion;
use flight\mcp\Completion\LearnTopicCompletion;
use flight\mcp\Completion\PluginCompletion;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\CompletionProvider;
use PhpMcp\Server\Attributes\McpResource;
use PhpMcp\Server\Attributes\McpResourceTemplate;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class Fetcher
{
    public function __construct(private ?DocsClient $docs = null)
    {
    }

    #[McpTool(
        name: 'get_docs_page',
        description: 'ALWAYS call this before writing any FlightPHP code. Fetches the official '
            . 'FlightPHP documentation for a specific topic (install, routing, middleware, requests, '
            . 'responses, templates, security, simple-pdo, DI container, testing, ai, etc.). '
            . 'Use this whenever a user asks how to do something in FlightPHP, before suggesting any '
            . 'implementation. Call list_docs_pages() first if unsure which topic slug to use. '
            . 'For a new app, fetch "install" and "ai" before "routing".',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Documentation Page',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: true,
        )
    )]
    public function getDocsPage(
        #[Schema(description: 'Documentation topic slug, for example "install", "routing", "middleware", "ai". Call list_docs_pages to see all valid values.')]
        #[CompletionProvider(provider: LearnTopicCompletion::class)]
        string $topic
    ): string {
        return $this->fetchDocsUrl(DocsCatalog::docUrl($topic));
    }

    #[McpTool(
        name: 'get_docs_section',
        description: 'Fetches one heading from a FlightPHP learn or install page. Use this when the full '
            . 'page is long and you need a single section such as "Resource Routing" or "Troubleshooting". '
            . 'Call get_docs_page() when you need the whole page.',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Documentation Section',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: true,
        )
    )]
    public function getDocsSection(
        #[Schema(description: 'Documentation topic slug, for example "routing" or "install". Call list_docs_pages to see all valid values.')]
        #[CompletionProvider(provider: LearnTopicCompletion::class)]
        string $topic,
        #[Schema(description: 'Heading text to extract, for example "Resource Routing" or "Troubleshooting". Matching is case-insensitive.')]
        string $heading
    ): string {
        return MarkdownSections::extract($this->getDocsPage($topic), $heading);
    }

    #[McpTool(
        name: 'list_docs_pages',
        description: 'Returns all available FlightPHP documentation topics with slugs and descriptions, '
            . 'including the install guide. Call this when unsure which topic covers a FlightPHP feature, '
            . 'or at the start of a FlightPHP session to understand what documentation is available.',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false,
        )
    )]
    public function listDocsPages(): string
    {
        return DocsCatalog::listDocs();
    }

    #[McpTool(
        name: 'list_guide_pages',
        description: 'Lists all official FlightPHP step-by-step guides. Call this when a user wants '
            . 'to build a complete app or follow a tutorial.',
        annotations: new ToolAnnotations(
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false,
        )
    )]
    public function listGuidePages(): string
    {
        return DocsCatalog::listGuides();
    }

    #[McpTool(
        name: 'get_guide_page',
        description: 'Fetches an official FlightPHP step-by-step guide. Call this when a user wants '
            . 'to build a complete FlightPHP application or asks about project structure, SOLID principles, '
            . 'or testing patterns. For a new app, also call get_docs_page("install"). '
            . 'Available guides: "blog", "unit-testing".',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Guide',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true,
        )
    )]
    public function getGuidePage(
        #[Schema(description: 'Guide slug. Call list_guide_pages() for the valid values.')]
        #[CompletionProvider(provider: GuideCompletion::class)]
        string $guide
    ): string {
        return $this->fetchDocsUrl(DocsCatalog::guideUrl($guide));
    }

    #[McpTool(
        name: 'list_plugin_pages',
        description: 'Lists all FlightPHP plugins and extensions with their slugs and descriptions. '
            . 'Call this when a user asks about adding functionality to FlightPHP (database, auth, caching, '
            . 'sessions, templating, email, CLI, testing, monitoring, encryption, queues, etc.) to find the right plugin. '
            . 'The official skeleton uses the twig plugin.',
        annotations: new ToolAnnotations(
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false,
        )
    )]
    public function listPluginPages(): string
    {
        return DocsCatalog::listPlugins();
    }

    #[McpTool(
        name: 'get_plugin_docs',
        description: 'Fetches documentation for a FlightPHP plugin or extension. Call this before '
            . 'helping a user integrate any FlightPHP plugin (ORM, auth, caching, sessions, templating, '
            . 'email, CLI, APM, encryption, job queues, etc.). Call list_plugin_pages() to see all available plugins.',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Plugin Documentation',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true,
        )
    )]
    public function getPluginDocs(
        #[Schema(description: 'Plugin slug, for example "twig", "active-record", "session". Call list_plugin_pages() for all valid values.')]
        #[CompletionProvider(provider: PluginCompletion::class)]
        string $plugin
    ): string {
        return $this->fetchDocsUrl(DocsCatalog::pluginUrl($plugin));
    }

    #[McpTool(
        name: 'search_docs',
        description: 'Searches FlightPHP documentation across core docs, guides, and plugins. '
            . 'Uses the docs site search and the local page catalog, and returns canonical '
            . 'https://docs.flightphp.com URLs. Use this when unsure which page covers the question. '
            . 'Follow up with get_docs_page(), get_guide_page(), get_plugin_docs(), get_docs_section(), or fetch_url().',
        annotations: new ToolAnnotations(
            title: 'Search FlightPHP Documentation',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true,
        )
    )]
    public function searchDocs(
        #[Schema(description: 'Search query, for example "authentication", "twig", "simplepdo", "file upload"')]
        string $query
    ): string {
        $query = trim($query);
        if ($query === '') {
            throw new \InvalidArgumentException('A search query is required.');
        }

        $liveFailed = false;
        try {
            $html = $this->docs()->fetch(DocsCatalog::searchUrl($query), 'text/html');
            $siteHits = DocsSearch::parseHtml($html);
        } catch (\Throwable) {
            $liveFailed = true;
            $siteHits = [];
        }

        $results = DocsSearch::merge($siteHits, DocsSearch::catalogHits($query));

        return DocsSearch::format($query, $results, $liveFailed);
    }

    #[McpTool(
        name: 'lookup_api',
        description: 'Checks a FlightPHP symbol or habit against known documentation footguns before you write code. '
            . 'Use this for Flight::get, PdoWrapper, Flight::render, Flight::path, route return values, AGENTS.md, '
            . 'Latte versus Twig, and method filters versus middleware. Then open the page it names. '
            . 'This does not invent method signatures.',
        annotations: new ToolAnnotations(
            title: 'Look Up a FlightPHP API Footgun',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false,
        )
    )]
    public function lookupApi(
        #[Schema(description: 'Symbol or habit to check, for example "Flight::get", "PdoWrapper", "Flight::render", "AGENTS.md"')]
        string $symbol
    ): string {
        return ApiNotes::explain($symbol);
    }

    #[McpTool(
        name: 'fetch_url',
        description: 'Fetches a FlightPHP documentation URL directly. Only use this when '
            . 'get_docs_page() does not cover the exact page needed. '
            . 'URLs must be on the docs.flightphp.com domain. Prefer get_docs_page() for standard topics.',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: true,
        )
    )]
    public function fetchUrl(
        #[Schema(
            description: 'Full URL on docs.flightphp.com. Must start with https://docs.flightphp.com/',
            pattern: '^https://docs\.flightphp\.com/'
        )]
        string $url
    ): string {
        if (!str_starts_with($url, 'https://docs.flightphp.com/')) {
            throw new \InvalidArgumentException(
                'Only URLs on https://docs.flightphp.com/ are permitted. Use get_docs_page() for standard topics.'
            );
        }

        return $this->fetchDocsUrl($url);
    }

    #[McpTool(
        name: 'generate_plugin_page',
        description: 'Generates a FlightPHP docs plugin page in markdown. Plugin pages document '
            . 'Flight-native integration (service registration, route usage), not generic library usage. '
            . 'Empty sections are omitted. Pass the code you already have. Do not leave placeholder commands '
            . 'for the reader to fill in. Returns markdown for the awesome-plugins/ section of the docs site.',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false,
        )
    )]
    public function generatePluginPage(
        #[Schema(description: 'Plugin display name, for example "Flight Session"')]
        string $name,
        #[Schema(description: 'Opening paragraph describing what the plugin does')]
        string $description,
        #[Schema(description: 'Full GitHub repository URL, for example https://github.com/flightphp/session')]
        string $github_url = '',
        #[Schema(description: 'Composer package name, for example flightphp/session')]
        string $composer_package = '',
        #[Schema(description: 'PHP code showing Flight-native setup: Flight::register(), or the skeleton services.php wiring')]
        string $flight_setup_example = '',
        #[Schema(description: 'PHP code showing usage inside a Flight route or service')]
        string $usage_example = '',
        #[Schema(description: 'Prose or a markdown table of configuration options. Section omitted if empty.')]
        string $config_options = '',
        #[Schema(description: 'Newline-separated see-also entries. Use "Label | https://..." for a link. Section omitted if empty.')]
        string $see_also = '',
        #[Schema(description: 'Newline-separated troubleshooting notes. Section omitted if empty.')]
        string $troubleshooting = ''
    ): string {
        return PageTemplates::plugin(
            $name,
            $description,
            $github_url,
            $composer_package,
            $flight_setup_example,
            $usage_example,
            $config_options,
            $see_also,
            $troubleshooting,
        );
    }

    #[McpTool(
        name: 'generate_learn_page',
        description: 'Generates a FlightPHP core documentation page in markdown. Learn pages cover '
            . 'functionality built into the framework and use Overview, Understanding, Basic Usage, '
            . 'Advanced Usage, Key Points, See Also, Troubleshooting, and Changelog. Empty sections are omitted. '
            . 'Pass the prose and code you already have. Returns markdown for the learn/ section of the docs site.',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false,
        )
    )]
    public function generateLearnPage(
        #[Schema(description: 'Page title, for example "Routing" or "Middleware"')]
        string $title,
        #[Schema(description: 'Overview paragraph explaining what this feature does')]
        string $description,
        #[Schema(description: 'Understanding section: why the feature exists and how it fits. Section omitted if empty.')]
        string $understanding = '',
        #[Schema(description: 'PHP code for the Basic Usage section. Section omitted if empty.')]
        string $basic_example = '',
        #[Schema(description: 'PHP code for the Advanced Usage section. Section omitted if empty.')]
        string $advanced_example = '',
        #[Schema(description: 'Newline-separated Key Points. Section omitted if empty.')]
        string $key_points = '',
        #[Schema(description: 'Newline-separated see-also entries. Use "Label | https://..." for a link. Section omitted if empty.')]
        string $see_also = '',
        #[Schema(description: 'Newline-separated troubleshooting notes. Section omitted if empty.')]
        string $troubleshooting = '',
        #[Schema(description: 'Newline-separated changelog entries, for example "v3.18.0 - Added SimplePdo". Section omitted if empty.')]
        string $changelog = ''
    ): string {
        return PageTemplates::learn(
            $title,
            $description,
            $understanding,
            $basic_example,
            $advanced_example,
            $key_points,
            $see_also,
            $troubleshooting,
            $changelog,
        );
    }

    #[McpTool(
        name: 'generate_guide_page',
        description: 'Generates a FlightPHP step-by-step guide page in markdown. Pass step titles, '
            . 'or steps with bodies separated by a line that is only ---. The first line of each step is the title '
            . 'and the rest is the body. Empty step lists produce no Step sections. '
            . 'Returns markdown for the guides/ section of the docs site.',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false,
        )
    )]
    public function generateGuidePage(
        #[Schema(description: 'Guide title, for example "Building a REST API with Flight"')]
        string $title,
        #[Schema(description: 'What the reader will have built by the end of the guide')]
        string $description,
        #[Schema(description: 'Newline-separated prerequisites. Defaults to PHP 8.1+ and Composer if empty.')]
        string $prerequisites = '',
        #[Schema(description: 'Step titles, one per line. For bodies, separate steps with a line that is only ---. The first line of a step is the title.')]
        string $steps = '',
        #[Schema(description: 'Newline-separated see-also entries. Use "Label | https://..." for a link. Section omitted if empty.')]
        string $see_also = ''
    ): string {
        return PageTemplates::guide($title, $description, $prerequisites, $steps, $see_also);
    }

    #[McpResource(
        uri: 'flightphp://docs/index',
        name: 'flightphp-docs-index',
        description: 'Index of all FlightPHP documentation topics, including install. Read at the start of any '
            . 'FlightPHP development session to understand what documentation is available.',
        mimeType: 'text/plain',
    )]
    public function getDocsIndex(): string
    {
        $lines = ["FlightPHP Documentation Index\n"];
        foreach (DocsCatalog::PAGES + DocsCatalog::LEARN as $slug => $desc) {
            $lines[] = DocsCatalog::docUrl($slug) . " — $slug: $desc";
        }

        return implode("\n", $lines);
    }

    #[McpResource(
        uri: 'flightphp://guides/index',
        name: 'flightphp-guides-index',
        description: 'Index of all FlightPHP official step-by-step guides.',
        mimeType: 'text/plain',
    )]
    public function getGuidesIndex(): string
    {
        $lines = ["FlightPHP Guides Index\n"];
        foreach (DocsCatalog::GUIDES as $slug => $desc) {
            $lines[] = DocsCatalog::guideUrl($slug) . " — $slug: $desc";
        }

        return implode("\n", $lines);
    }

    #[McpResourceTemplate(
        uriTemplate: 'flightphp://guides/{guide}',
        name: 'flightphp-guide-page',
        description: 'Content of a FlightPHP guide by slug (for example flightphp://guides/blog). '
            . 'Read flightphp://guides/index for valid slugs.',
        mimeType: 'text/plain',
    )]
    public function getGuideTopic(
        #[CompletionProvider(provider: GuideCompletion::class)]
        string $guide
    ): string {
        return $this->fetchDocsUrl(DocsCatalog::guideUrl($guide));
    }

    #[McpResource(
        uri: 'flightphp://plugins/index',
        name: 'flightphp-plugins-index',
        description: 'Index of all FlightPHP plugins and extensions with slugs and descriptions.',
        mimeType: 'text/plain',
    )]
    public function getPluginsIndex(): string
    {
        $lines = ["FlightPHP Plugins Index\n"];
        foreach (DocsCatalog::PLUGINS as $slug => $desc) {
            $lines[] = DocsCatalog::pluginUrl($slug) . " — $slug: $desc";
        }

        return implode("\n", $lines);
    }

    #[McpResourceTemplate(
        uriTemplate: 'flightphp://plugins/{plugin}',
        name: 'flightphp-plugin-page',
        description: 'Content of a FlightPHP plugin documentation page '
            . '(for example flightphp://plugins/twig). Read flightphp://plugins/index for valid slugs.',
        mimeType: 'text/plain',
    )]
    public function getPluginTopic(
        #[CompletionProvider(provider: PluginCompletion::class)]
        string $plugin
    ): string {
        return $this->fetchDocsUrl(DocsCatalog::pluginUrl($plugin));
    }

    #[McpResourceTemplate(
        uriTemplate: 'flightphp://docs/{topic}',
        name: 'flightphp-docs-page',
        description: 'Content of a FlightPHP documentation page by topic slug '
            . '(for example flightphp://docs/routing or flightphp://docs/install). '
            . 'Read flightphp://docs/index for valid slugs.',
        mimeType: 'text/plain',
    )]
    public function getDocsTopic(
        #[CompletionProvider(provider: LearnTopicCompletion::class)]
        string $topic
    ): string {
        return $this->fetchDocsUrl(DocsCatalog::docUrl($topic));
    }

    private function fetchDocsUrl(string $url): string
    {
        return $this->docs()->fetch($url);
    }

    private function docs(): DocsClient
    {
        return $this->docs ??= new DocsClient();
    }
}
