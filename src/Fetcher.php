<?php
declare(strict_types=1);

namespace flight\mcp;

use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\CompletionProvider;
use PhpMcp\Server\Attributes\McpResource;
use PhpMcp\Server\Attributes\McpResourceTemplate;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class Fetcher
{
    private const DOCS_BASE_URL = 'https://docs.flightphp.com/learn/';
    private const DOCS_PAGES = [
        'routing'                       => 'Routing - URL patterns, HTTP methods, route groups, parameters, resource routing',
        'middleware'                     => 'Middleware - Request/response filtering, authentication, execution order',
        'requests'                       => 'Requests - HTTP request handling, query params, POST data, headers',
        'responses'                      => 'Responses - Building responses, JSON/JSONP, redirects, status codes, headers',
        'templates'                      => 'Views/Templates - HTML templating with Latte, Blade, Smarty, built-in PHP engine',
        'configuration'                  => 'Configuration - Framework configuration options',
        'autoloading'                    => 'Autoloading - Class autoloading setup',
        'security'                       => 'Security - Best practices, CSRF, XSS prevention',
        'events'                         => 'Events - Event manager, listeners',
        'extending'                      => 'Extending - Custom methods, classes, extending the framework',
        'filtering'                      => 'Filtering - Method hooks and filtering',
        'collections'                    => 'Collections - Data collection handling',
        'json'                           => 'JSON - JSON encoding/decoding utilities',
        'simple-pdo'                     => 'SimplePdo - Modern PDO wrapper for database access',
        'dependency-injection-container' => 'Dependency Injection - DIC usage, PSR-11, containers',
        'unit-testing'                   => 'Unit Testing - Testing Flight applications',
        'uploaded-file'                  => 'File Uploads - Handling user-uploaded files',
        'ai'                             => 'AI Integration - AI tool integration',
        'migrating-to-v3'                => 'Migration Guide - Upgrading from FlightPHP v2 to v3',
        'why-frameworks'                 => 'Why Frameworks',
        'flight-vs-another-framework'    => 'Comparison - FlightPHP vs Laravel, Slim, others',
    ];

    private const GUIDES_BASE_URL = 'https://docs.flightphp.com/en/v3/guides/';
    private const GUIDE_PAGES = [
        'blog'         => 'Building a Blog - Full project: routing, Latte templates, forms, data storage, error handling',
        'unit-testing' => 'Unit Testing & SOLID Principles - PHPUnit setup, testable code, mocking, architecture',
    ];

    private const PLUGINS_BASE_URL = 'https://docs.flightphp.com/en/v3/awesome-plugins/';
    private const PLUGIN_PAGES = [
        'active-record'      => 'Flight ActiveRecord - ORM/Active Record pattern for database models (official)',
        'apm'                => 'Flight APM - Application performance monitoring (official)',
        'async'              => 'Flight Async - Async/concurrent request handling (official)',
        'comment-template'   => 'Comment Template - Template comment utilities',
        'easy-query'         => 'Easy Query - Simplified database query builder',
        'ghost-session'      => 'Ghostff Session - Advanced session manager with encryption support',
        'jwt'                => 'Firebase JWT - JSON Web Token authentication',
        'latte'              => 'Latte - Latte templating engine integration',
        'migrations'         => 'BYJG Migrations - Database schema migration management',
        'n0nag0n_wordpress'  => 'WordPress Integration - Run FlightPHP inside WordPress',
        'permissions'        => 'Flight Permissions - Role-based access control (official)',
        'php-cookie'         => 'PHP Cookie - Cookie management library',
        'php-encryption'     => 'Defuse PHP Encryption - Symmetric encryption for sensitive data',
        'php-file-cache'     => 'Flight Cache - File-based caching (official)',
        'runway'             => 'Flight Runway - CLI tool for scaffolding and management (official)',
        'session'            => 'Flight Session - Simple session handler (official)',
        'simple-job-queue'   => 'Simple Job Queue - Background job processing',
        'tracy'              => 'Tracy - Error handler and debugger integration',
        'tracy-extensions'   => 'Tracy Extensions - FlightPHP-specific Tracy panels (official)',
    ];

    #[McpTool(
        name: 'get_docs_page',
        description: 'ALWAYS call this before writing any FlightPHP code. Fetches the official '
            . 'FlightPHP documentation for a specific topic (routing, middleware, requests, responses, '
            . 'templates, security, database, DI container, testing, etc.). Use this whenever a user '
            . 'asks how to do something in FlightPHP, before suggesting any implementation. '
            . 'Call list_docs_pages() first if unsure which topic slug to use.',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Documentation Page',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: true,
        )
    )]
    public function getDocsPage(
        #[Schema(description: 'Documentation topic slug e.g. "routing", "middleware", "security". Call list_docs_pages to see all valid values.')]
        #[CompletionProvider(values: ['routing', 'middleware', 'requests', 'responses', 'templates',
            'configuration', 'autoloading', 'security', 'events', 'extending', 'filtering',
            'collections', 'json', 'simple-pdo', 'dependency-injection-container', 'unit-testing',
            'uploaded-file', 'ai', 'migrating-to-v3', 'why-frameworks', 'flight-vs-another-framework'])]
        string $topic
    ): string {
        if (!array_key_exists($topic, self::DOCS_PAGES)) {
            throw new \InvalidArgumentException(
                "Unknown topic '$topic'. Call list_docs_pages() to see valid slugs and descriptions."
            );
        }
        return $this->fetchDocsUrl(self::DOCS_BASE_URL . $topic);
    }

    #[McpTool(
        name: 'list_docs_pages',
        description: 'Returns all available FlightPHP documentation topics with slugs and descriptions. '
            . 'Call this when unsure which topic covers a FlightPHP feature, or at the start of a '
            . 'FlightPHP session to understand what documentation is available.',
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false,
        )
    )]
    public function listDocsPages(): string {
        $lines = ["Available FlightPHP documentation topics:\n"];
        foreach (self::DOCS_PAGES as $slug => $desc) {
            $lines[] = "  $slug: $desc";
        }
        $lines[] = "\nUse get_docs_page(topic) to fetch content for any slug above.";
        $lines[] = "\nFor step-by-step guides call list_guide_pages() or get_guide_page(guide).";
        $lines[] = "For plugins and extensions call list_plugin_pages() or get_plugin_docs(plugin).";
        return implode("\n", $lines);
    }

    #[McpTool(
        name: 'list_guide_pages',
        description: 'Lists all official FlightPHP step-by-step guides. Call this when a user wants '
            . 'to build a complete app or follow a tutorial.',
        annotations: new ToolAnnotations(
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false,
        )
    )]
    public function listGuidePages(): string {
        $lines = ["Available FlightPHP guides:\n"];
        foreach (self::GUIDE_PAGES as $slug => $desc) {
            $lines[] = "  $slug: $desc";
        }
        $lines[] = "\nUse get_guide_page(guide) to fetch content for any guide above.";
        return implode("\n", $lines);
    }

    #[McpTool(
        name: 'get_guide_page',
        description: 'Fetches an official FlightPHP step-by-step guide. Call this when a user wants '
            . 'to build a complete FlightPHP application (blog, REST API, etc.) or asks about best-practice '
            . 'project structure, SOLID principles, or testing patterns. Available guides: "blog", "unit-testing".',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Guide',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true,
        )
    )]
    public function getGuidePage(
        #[Schema(description: 'Guide slug: "blog" or "unit-testing"')]
        #[CompletionProvider(values: ['blog', 'unit-testing'])]
        string $guide
    ): string {
        if (!array_key_exists($guide, self::GUIDE_PAGES)) {
            throw new \InvalidArgumentException("Unknown guide '$guide'. Valid slugs: " . implode(', ', array_keys(self::GUIDE_PAGES)));
        }
        return $this->fetchDocsUrl(self::GUIDES_BASE_URL . $guide);
    }

    #[McpTool(
        name: 'list_plugin_pages',
        description: 'Lists all FlightPHP plugins and extensions with their slugs and descriptions. '
            . 'Call this when a user asks about adding functionality to FlightPHP (database, auth, caching, '
            . 'sessions, templating, CLI, testing, monitoring, encryption, queues, etc.) to find the right plugin.',
        annotations: new ToolAnnotations(
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: false,
        )
    )]
    public function listPluginPages(): string {
        $lines = ["Available FlightPHP plugins and extensions:\n"];
        foreach (self::PLUGIN_PAGES as $slug => $desc) {
            $lines[] = "  $slug: $desc";
        }
        $lines[] = "\nUse get_plugin_docs(plugin) to fetch full documentation for any plugin above.";
        return implode("\n", $lines);
    }

    #[McpTool(
        name: 'get_plugin_docs',
        description: 'Fetches documentation for a FlightPHP plugin or extension. Call this before '
            . 'helping a user integrate any FlightPHP plugin (ORM, auth, caching, sessions, templating, '
            . 'CLI, APM, encryption, job queues, etc.). Call list_plugin_pages() to see all available plugins.',
        annotations: new ToolAnnotations(
            title: 'Get FlightPHP Plugin Documentation',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true,
        )
    )]
    public function getPluginDocs(
        #[Schema(description: 'Plugin slug, e.g. "active-record", "session", "jwt". Call list_plugin_pages() for all valid values.')]
        #[CompletionProvider(values: ['active-record', 'apm', 'async', 'comment-template', 'easy-query',
            'ghost-session', 'jwt', 'latte', 'migrations', 'n0nag0n_wordpress', 'permissions',
            'php-cookie', 'php-encryption', 'php-file-cache', 'runway', 'session',
            'simple-job-queue', 'tracy', 'tracy-extensions'])]
        string $plugin
    ): string {
        if (!array_key_exists($plugin, self::PLUGIN_PAGES)) {
            throw new \InvalidArgumentException("Unknown plugin '$plugin'. Call list_plugin_pages() to see valid slugs.");
        }
        return $this->fetchDocsUrl(self::PLUGINS_BASE_URL . $plugin);
    }

    #[McpTool(
        name: 'search_docs',
        description: 'Searches the entire FlightPHP documentation site (core docs, guides, AND plugins) '
            . 'for a keyword or topic. Use this when unsure which specific page covers what the user needs, '
            . 'or to discover relevant pages across all sections at once. Returns a list of matching pages '
            . 'with titles, URLs, and excerpts. Follow up with get_docs_page(), get_guide_page(), '
            . 'get_plugin_docs(), or fetch_url() to read the full content of relevant results.',
        annotations: new ToolAnnotations(
            title: 'Search FlightPHP Documentation',
            readOnlyHint: true, destructiveHint: false, idempotentHint: true, openWorldHint: true,
        )
    )]
    public function searchDocs(
        #[Schema(description: 'Search query, e.g. "authentication", "database", "caching", "file upload"')]
        string $query
    ): string {
        $url = 'https://docs.flightphp.com/en/v3/search?q=' . urlencode($query);
        $context = stream_context_create(['http' => ['header' => "Accept: text/html\r\n", 'timeout' => 10]]);
        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            throw new \RuntimeException("Failed to search FlightPHP docs for '$query'.");
        }

        // Extract result links: <a href="/en/v3/..."><strong>Title</strong></a>
        preg_match_all(
            '/<a\s+href="(\/en\/v3\/[^"]+)"[^>]*>\s*<strong>([^<]+)<\/strong>\s*<\/a>/i',
            $html, $linkMatches, PREG_SET_ORDER
        );

        if (empty($linkMatches)) {
            return "No results found for '$query' in the FlightPHP documentation.";
        }

        // Extract list-item blocks to get excerpts
        preg_match_all('/<li[^>]*class="[^"]*list-group-item[^"]*"[^>]*>(.*?)<\/li>/is', $html, $itemMatches);

        $lines = ["FlightPHP docs search results for '$query':\n"];
        foreach ($linkMatches as $i => $match) {
            $path = $match[1];
            $title = html_entity_decode(trim($match[2]), ENT_QUOTES | ENT_HTML5);
            $fullUrl = 'https://docs.flightphp.com' . $path;

            // Extract plain-text excerpt from corresponding list item
            $excerpt = '';
            if (isset($itemMatches[1][$i])) {
                $text = preg_replace('/<[^>]+>/', ' ', $itemMatches[1][$i]);
                $text = html_entity_decode(preg_replace('/\s+/', ' ', $text), ENT_QUOTES | ENT_HTML5);
                $excerpt = ' — ' . trim(substr(trim($text), 0, 120));
            }

            $lines[] = sprintf("  [%d] %s\n      URL: %s%s", $i + 1, $title, $fullUrl, $excerpt);
        }

        $lines[] = sprintf("\n%d result(s). Fetch a page with get_docs_page(), get_guide_page(), get_plugin_docs(), or fetch_url().", count($linkMatches));
        return implode("\n", $lines);
    }

    #[McpTool(
        name: 'fetch_url',
        description: 'Fetches a FlightPHP documentation URL directly. Only use this when '
            . 'get_docs_page() does not cover the exact page needed (e.g., changelog, API ref). '
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
                "Only URLs on https://docs.flightphp.com/ are permitted. Use get_docs_page() for standard topics."
            );
        }
        return $this->fetchDocsUrl($url);
    }

    #[McpResource(
        uri: 'flightphp://docs/index',
        name: 'flightphp-docs-index',
        description: 'Index of all FlightPHP documentation topics. Read at the start of any '
            . 'FlightPHP development session to understand what documentation is available.',
        mimeType: 'text/plain',
    )]
    public function getDocsIndex(): string {
        $lines = ["FlightPHP Documentation Index\nBase URL: " . self::DOCS_BASE_URL . "\n"];
        foreach (self::DOCS_PAGES as $slug => $desc) {
            $lines[] = "$slug: $desc";
        }
        return implode("\n", $lines);
    }

    #[McpResource(
        uri: 'flightphp://guides/index',
        name: 'flightphp-guides-index',
        description: 'Index of all FlightPHP official step-by-step guides.',
        mimeType: 'text/plain',
    )]
    public function getGuidesIndex(): string {
        $lines = ["FlightPHP Guides Index\nBase URL: " . self::GUIDES_BASE_URL . "\n"];
        foreach (self::GUIDE_PAGES as $slug => $desc) {
            $lines[] = "$slug: $desc";
        }
        return implode("\n", $lines);
    }

    #[McpResourceTemplate(
        uriTemplate: 'flightphp://guides/{guide}',
        name: 'flightphp-guide-page',
        description: 'Content of a FlightPHP guide by slug (e.g. flightphp://guides/blog). '
            . 'Read flightphp://guides/index for valid slugs.',
        mimeType: 'text/plain',
    )]
    public function getGuideTopic(
        #[CompletionProvider(values: ['blog', 'unit-testing'])]
        string $guide
    ): string {
        if (!array_key_exists($guide, self::GUIDE_PAGES)) {
            throw new \InvalidArgumentException("Unknown guide '$guide'. See flightphp://guides/index.");
        }
        return $this->fetchDocsUrl(self::GUIDES_BASE_URL . $guide);
    }

    #[McpResource(
        uri: 'flightphp://plugins/index',
        name: 'flightphp-plugins-index',
        description: 'Index of all FlightPHP plugins and extensions with slugs and descriptions.',
        mimeType: 'text/plain',
    )]
    public function getPluginsIndex(): string {
        $lines = ["FlightPHP Plugins Index\nBase URL: " . self::PLUGINS_BASE_URL . "\n"];
        foreach (self::PLUGIN_PAGES as $slug => $desc) {
            $lines[] = "$slug: $desc";
        }
        return implode("\n", $lines);
    }

    #[McpResourceTemplate(
        uriTemplate: 'flightphp://plugins/{plugin}',
        name: 'flightphp-plugin-page',
        description: 'Content of a FlightPHP plugin documentation page '
            . '(e.g. flightphp://plugins/active-record). Read flightphp://plugins/index for valid slugs.',
        mimeType: 'text/plain',
    )]
    public function getPluginTopic(
        #[CompletionProvider(values: ['active-record', 'apm', 'async', 'comment-template', 'easy-query',
            'ghost-session', 'jwt', 'latte', 'migrations', 'n0nag0n_wordpress', 'permissions',
            'php-cookie', 'php-encryption', 'php-file-cache', 'runway', 'session',
            'simple-job-queue', 'tracy', 'tracy-extensions'])]
        string $plugin
    ): string {
        if (!array_key_exists($plugin, self::PLUGIN_PAGES)) {
            throw new \InvalidArgumentException("Unknown plugin '$plugin'. See flightphp://plugins/index.");
        }
        return $this->fetchDocsUrl(self::PLUGINS_BASE_URL . $plugin);
    }

    #[McpResourceTemplate(
        uriTemplate: 'flightphp://docs/{topic}',
        name: 'flightphp-docs-page',
        description: 'Content of a FlightPHP documentation page by topic slug '
            . '(e.g. flightphp://docs/routing). Read flightphp://docs/index for valid slugs.',
        mimeType: 'text/plain',
    )]
    public function getDocsTopic(
        #[CompletionProvider(values: ['routing', 'middleware', 'requests', 'responses', 'templates',
            'configuration', 'autoloading', 'security', 'events', 'extending', 'filtering',
            'collections', 'json', 'simple-pdo', 'dependency-injection-container', 'unit-testing',
            'uploaded-file', 'ai', 'migrating-to-v3', 'why-frameworks', 'flight-vs-another-framework'])]
        string $topic
    ): string {
        if (!array_key_exists($topic, self::DOCS_PAGES)) {
            throw new \InvalidArgumentException("Unknown topic '$topic'. See flightphp://docs/index.");
        }
        return $this->fetchDocsUrl(self::DOCS_BASE_URL . $topic);
    }

    private function fetchDocsUrl(string $url): string {
        $context = stream_context_create(['http' => ['header' => "Accept: text/plain\r\n", 'timeout' => 10]]);
        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            throw new \RuntimeException("Failed to fetch '$url'. The page may not exist or the docs site may be unreachable.");
        }
        return $content;
    }
}
