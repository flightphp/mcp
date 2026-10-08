<?php
declare(strict_types=1);

namespace flight\mcp;

final class DocsCatalog
{
    public const LEARN_BASE = 'https://docs.flightphp.com/learn/';
    public const GUIDES_BASE = 'https://docs.flightphp.com/guides/';
    public const PLUGINS_BASE = 'https://docs.flightphp.com/awesome-plugins/';
    public const ORIGIN = 'https://docs.flightphp.com';

    /**
     * The public /search path redirects to /en/v3/search/ and drops the query string.
     * The versioned search URL is the one that keeps ?q=.
     */
    public const SEARCH_URL = 'https://docs.flightphp.com/en/v3/search?q=';

    /** @var array<string, string> */
    public const LEARN = [
        'routing'                       => 'Routing - URL patterns, HTTP methods, route groups, parameters, resource routing',
        'middleware'                     => 'Middleware - Request/response filtering, authentication, execution order',
        'requests'                       => 'Requests - HTTP request handling, query params, POST data, headers',
        'responses'                      => 'Responses - Building responses, JSON/JSONP, redirects, status codes, headers',
        'templates'                      => 'Views/Templates - HTML templating with Twig (skeleton default), Latte, and the built-in PHP engine',
        'configuration'                  => 'Configuration - Framework configuration options',
        'autoloading'                    => 'Autoloading - Composer PSR-4 for skeleton apps, Flight::path(), folder case must match namespaces',
        'security'                       => 'Security - Best practices, CSRF, XSS prevention',
        'events'                         => 'Events - Event manager, listeners',
        'extending'                      => 'Extending - Custom methods, classes, extending the framework',
        'filtering'                      => 'Filtering - Method hooks and filtering (Flight::before and Flight::after)',
        'collections'                    => 'Collections - Data collection handling',
        'json'                           => 'JSON - JSON encoding/decoding utilities',
        'simple-pdo'                     => 'SimplePdo - Modern PDO wrapper for database access. Use this instead of PdoWrapper',
        'pdo-wrapper'                    => 'PdoWrapper - Deprecated PDO wrapper as of v3.18.0. Use SimplePdo instead',
        'dependency-injection-container' => 'Dependency Injection - DIC usage, PSR-11, containers. The skeleton uses Dice',
        'unit-testing'                   => 'Unit Testing - Testing Flight applications',
        'uploaded-file'                  => 'File Uploads - Handling user-uploaded files',
        'ai'                             => 'AI Integration - AGENTS.md, Runway ai:init, skeleton layout for coding assistants',
        'migrating-to-v3'                => 'Migration Guide - Upgrading from FlightPHP v2 to v3',
        'why-frameworks'                 => 'Why Frameworks',
        'flight-vs-another-framework'    => 'Comparison - FlightPHP vs Laravel, Slim, others',
    ];

    /** Pages that do not live under /learn/. */
    /** @var array<string, string> */
    public const PAGES = [
        'install' => 'Installation - composer require, skeleton create-project, app/config layout, web server',
    ];

    /** @var array<string, string> */
    public const GUIDES = [
        'blog'         => 'Building a Blog - Full project: routing, Latte templates, forms, data storage, error handling',
        'unit-testing' => 'Unit Testing & SOLID Principles - PHPUnit setup, testable code, mocking, architecture',
    ];

    /** @var array<string, string> */
    public const PLUGINS = [
        'active-record'      => 'Flight ActiveRecord - ORM/Active Record pattern for database models (official)',
        'apm'                => 'Flight APM - Application performance monitoring (official)',
        'async'              => 'Flight Async - Async/concurrent request handling (official)',
        'comment-template'   => 'Comment Template - Template comment utilities',
        'easy-query'         => 'Easy Query - Simplified database query builder',
        'flightmail'         => 'FlightMail - Symfony Mailer wrapper with a Flight-friendly API (unofficial)',
        'ghost-session'      => 'Ghostff Session - Advanced session manager with encryption support',
        'jwt'                => 'Firebase JWT - JSON Web Token authentication',
        'latte'              => 'Latte - Latte templating engine integration',
        'mcp'                => 'Flight MCP - MCP server that exposes these FlightPHP docs to coding assistants',
        'migrations'         => 'BYJG Migrations - Database schema migration management',
        'n0nag0n_wordpress'  => 'WordPress Integration - Run FlightPHP inside WordPress',
        'permissions'        => 'Flight Permissions - Role-based access control (official)',
        'php-cookie'         => 'PHP Cookie - Cookie management library',
        'php-encryption'     => 'Defuse PHP Encryption - Symmetric encryption for sensitive data',
        'php-file-cache'     => 'Flight Cache - File-based caching (official)',
        'runway'             => 'Flight Runway - CLI tool for scaffolding, routes, and ai:* commands (official)',
        'session'            => 'Flight Session - Simple session handler (official)',
        'simple-job-queue'   => 'Simple Job Queue - Background job processing',
        'tracy'              => 'Tracy - Error handler and debugger integration',
        'tracy-extensions'   => 'Tracy Extensions - FlightPHP-specific Tracy panels (official)',
        'twig'               => 'Twig - Template engine. This is the default view engine in the official skeleton',
    ];

    /** @return list<string> */
    public static function docSlugs(): array
    {
        return array_keys(self::PAGES + self::LEARN);
    }

    /** @return list<string> */
    public static function guideSlugs(): array
    {
        return array_keys(self::GUIDES);
    }

    /** @return list<string> */
    public static function pluginSlugs(): array
    {
        return array_keys(self::PLUGINS);
    }

    /** @param list<string> $slugs */
    public static function filterSlugs(array $slugs, string $currentValue): array
    {
        if ($currentValue === '') {
            return $slugs;
        }

        return array_values(array_filter(
            $slugs,
            static fn (string $slug): bool => str_starts_with($slug, $currentValue)
        ));
    }

    public static function docUrl(string $topic): string
    {
        if (isset(self::PAGES[$topic])) {
            return self::ORIGIN . '/' . $topic;
        }
        if (isset(self::LEARN[$topic])) {
            return self::LEARN_BASE . $topic;
        }

        throw new \InvalidArgumentException(
            "Unknown topic '$topic'. Call list_docs_pages() to see valid slugs and descriptions."
        );
    }

    public static function guideUrl(string $guide): string
    {
        if (!isset(self::GUIDES[$guide])) {
            throw new \InvalidArgumentException(
                "Unknown guide '$guide'. Valid slugs: " . implode(', ', self::guideSlugs())
            );
        }

        return self::GUIDES_BASE . $guide;
    }

    public static function pluginUrl(string $plugin): string
    {
        if (!isset(self::PLUGINS[$plugin])) {
            throw new \InvalidArgumentException(
                "Unknown plugin '$plugin'. Call list_plugin_pages() to see valid slugs."
            );
        }

        return self::PLUGINS_BASE . $plugin;
    }

    public static function searchUrl(string $query): string
    {
        return self::SEARCH_URL . rawurlencode($query);
    }

    public static function listDocs(): string
    {
        $lines = ["Available FlightPHP documentation topics:\n"];
        foreach (self::PAGES + self::LEARN as $slug => $desc) {
            $lines[] = "  $slug: $desc";
        }
        $lines[] = "\nUse get_docs_page(topic) to fetch content for any slug above.";
        $lines[] = "Use get_docs_section(topic, heading) to read one heading from a long page.";
        $lines[] = "\nFor step-by-step guides call list_guide_pages() or get_guide_page(guide).";
        $lines[] = "For plugins and extensions call list_plugin_pages() or get_plugin_docs(plugin).";

        return implode("\n", $lines);
    }

    public static function listGuides(): string
    {
        $lines = ["Available FlightPHP guides:\n"];
        foreach (self::GUIDES as $slug => $desc) {
            $lines[] = "  $slug: $desc";
        }
        $lines[] = "\nUse get_guide_page(guide) to fetch content for any guide above.";

        return implode("\n", $lines);
    }

    public static function listPlugins(): string
    {
        $lines = ["Available FlightPHP plugins and extensions:\n"];
        foreach (self::PLUGINS as $slug => $desc) {
            $lines[] = "  $slug: $desc";
        }
        $lines[] = "\nUse get_plugin_docs(plugin) to fetch full documentation for any plugin above.";

        return implode("\n", $lines);
    }

    /**
     * @return list<array{slug: string, description: string, url: string, kind: string}>
     */
    public static function entries(): array
    {
        $entries = [];
        foreach (self::PAGES + self::LEARN as $slug => $desc) {
            $entries[] = [
                'slug' => $slug,
                'description' => $desc,
                'url' => self::docUrl($slug),
                'kind' => 'docs',
            ];
        }
        foreach (self::GUIDES as $slug => $desc) {
            $entries[] = [
                'slug' => $slug,
                'description' => $desc,
                'url' => self::guideUrl($slug),
                'kind' => 'guide',
            ];
        }
        foreach (self::PLUGINS as $slug => $desc) {
            $entries[] = [
                'slug' => $slug,
                'description' => $desc,
                'url' => self::pluginUrl($slug),
                'kind' => 'plugin',
            ];
        }

        return $entries;
    }

    /**
     * Turn a docs-site href into a public https://docs.flightphp.com URL.
     * Version and language prefixes are removed. Off-site links return null.
     */
    public static function canonicalize(string $href): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5));
        if ($href === '' || str_starts_with($href, '#')) {
            return null;
        }

        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            $parts = parse_url($href);
            if (strtolower((string) ($parts['host'] ?? '')) !== 'docs.flightphp.com') {
                return null;
            }
            $path = (string) ($parts['path'] ?? '/');
        } elseif (str_starts_with($href, '/')) {
            $path = $href;
        } else {
            return null;
        }

        $path = preg_replace('#^/[a-z]{2}/v\d+#', '', $path) ?? $path;
        $queryPos = strpos($path, '?');
        if ($queryPos !== false) {
            $path = substr($path, 0, $queryPos);
        }
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }
        if (preg_match('#^/(install|guides|awesome-plugins|learn)/\1$#', $path, $match) === 1) {
            $path = '/' . $match[1];
        }
        if ($path === '/search' || str_starts_with($path, '/search/')) {
            return null;
        }

        return self::ORIGIN . $path;
    }
}
