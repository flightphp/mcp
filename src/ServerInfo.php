<?php
declare(strict_types=1);

namespace flight\mcp;

final class ServerInfo
{
    public const NAME = 'Flight PHP Framework Docs MCP';
    public const VERSION = '1.2.0';

    public const INSTRUCTIONS = <<<'TXT'
You are helping with the FlightPHP framework. Read the official docs with these tools before writing Flight code. Do not invent methods, config keys, or folder layouts.

New applications:
- Start from the official skeleton: composer create-project flightphp/skeleton
- Call get_docs_page("install") and get_docs_page("ai") before scaffolding
- In application code prefer $app and $this->app, constructor injection, and App\ namespaces
- Folder case must match namespaces (app/Controller, app/Middleware, app/Model)
- Routes live in app/config/routes.php. Services live in app/config/services.php
- The skeleton view engine is Twig, not Latte and not Flight::render()
- AGENTS.md at the project root is the source of truth. Do not add separate Copilot, Cursor, or Windsurf rule files unless the project already has them
- Short Flight:: examples in the docs teach APIs. Do not copy that style into skeleton controllers

API rules:
- Flight::get() reads a variable. It does not define a GET route. Use Flight::route('GET /path', ...) or $router->get() inside a group
- Flight::before() and Flight::after() are method filters, not HTTP middleware
- PdoWrapper is deprecated as of v3.18.0. Use SimplePdo
- Returning a value from a route can pass execution to the next route and surface as a 404. Echo the body or write to the response object
- If a topic slug is unknown, call list_docs_pages(), list_plugin_pages(), list_guide_pages(), or search_docs() before guessing
- Use lookup_api() for a known footgun, then open the page it names
- Use get_docs_section() when a learn page is long and you only need one heading
- fetch_url() only allows https://docs.flightphp.com/
TXT;
}
