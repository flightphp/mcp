<?php
declare(strict_types=1);

namespace flight\mcp;

final class ApiNotes
{
    /**
     * Documented footguns. Values are the note and the tool call that has the real page.
     *
     * @var array<string, array{note: string, read: string}>
     */
    private const NOTES = [
        'flight::get' => [
            'note' => 'Flight::get() reads a framework variable. It does not register a GET route. Use Flight::route(\'GET /path\', ...) or $router->get() inside a group callback.',
            'read' => 'get_docs_page("routing")',
        ],
        'flight::before' => [
            'note' => 'Flight::before() and Flight::after() filter a framework method. They are not HTTP middleware. Attach HTTP middleware with ->addMiddleware() on a route or group.',
            'read' => 'get_docs_page("filtering") and get_docs_page("middleware")',
        ],
        'flight::after' => [
            'note' => 'Flight::after() filters a framework method after it runs. It is not HTTP response middleware.',
            'read' => 'get_docs_page("filtering") and get_docs_page("middleware")',
        ],
        'pdowrapper' => [
            'note' => 'PdoWrapper is deprecated as of v3.18.0. Use SimplePdo for new code.',
            'read' => 'get_docs_page("simple-pdo") and get_docs_page("pdo-wrapper")',
        ],
        'flight::render' => [
            'note' => 'Flight::render() uses the built-in PHP view engine. The official skeleton uses Twig, registered as a service, not Flight::render().',
            'read' => 'get_docs_page("templates") and get_plugin_docs("twig")',
        ],
        'flight::path' => [
            'note' => 'Flight::path() is the legacy class loader. Skeleton apps autoload App\\ through Composer PSR-4. Folder case must match the namespace (app/Controller).',
            'read' => 'get_docs_page("autoloading") and get_docs_page("install")',
        ],
        'returntrue' => [
            'note' => 'Returning a value from a route callback passes execution to the next matching route. That often shows up as a 404. Echo the body, or write it on the response object. Returning true to skip a route is deprecated in favor of middleware.',
            'read' => 'get_docs_page("routing")',
        ],
        'agentsmd' => [
            'note' => 'AGENTS.md at the project root is the source of truth for a skeleton app. Runway writes it with php runway ai:generate-instructions. Do not add a second house-style file for each coding tool.',
            'read' => 'get_docs_page("ai") and get_docs_page("install")',
        ],
        'latte' => [
            'note' => 'Latte is a supported template engine. The official skeleton uses Twig. Pick the engine the project already has, and read that plugin page before writing templates.',
            'read' => 'get_docs_page("templates"), get_plugin_docs("twig"), and get_plugin_docs("latte")',
        ],
    ];

    public static function explain(string $symbol): string
    {
        $symbol = trim($symbol);
        if ($symbol === '') {
            throw new \InvalidArgumentException('A symbol or question is required.');
        }

        $compact = strtolower((string) preg_replace('/[^a-z0-9:]+/i', '', $symbol));
        $notes = [];
        foreach (self::NOTES as $key => $note) {
            if ($compact === $key || str_contains($compact, $key)) {
                $notes[] = $note;
            }
        }

        $lines = [];
        if ($notes !== []) {
            $lines[] = "Notes for '$symbol':\n";
            foreach ($notes as $note) {
                $lines[] = '- ' . $note['note'];
                $lines[] = '  Read: ' . $note['read'];
            }

            return implode("\n", $lines);
        }

        $lines[] = "No curated note for '$symbol'.";
        $lines[] = 'Do not invent a Flight method. Read a matching page before writing code.';

        $hits = array_slice(DocsSearch::catalogHits($symbol), 0, 5);
        if ($hits !== []) {
            $lines[] = "\nClosest catalog pages:";
            foreach ($hits as $hit) {
                $lines[] = "  - {$hit['title']}: {$hit['url']}";
            }
        } else {
            $lines[] = "\nCall search_docs() if none of the page lists name this topic.";
        }

        return implode("\n", $lines);
    }
}
