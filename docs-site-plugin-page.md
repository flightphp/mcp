# FlightPHP MCP Server

The FlightPHP MCP Server gives any MCP-compatible AI coding assistant instant, structured access to the entire FlightPHP documentation — routing, middleware, plugins, guides, and more. Instead of your AI hallucinating API details or guessing at method signatures, it fetches the real docs on demand. No API keys, no installation required for the hosted version.

Visit the [Github repository](https://github.com/flightphp/mcp) for the full source code and details.

## Quick Start

The server is publicly hosted and ready to use:

```
https://mcp.flightphp.com/mcp
```

Just add that URL to your AI coding extension. No signup, no credentials. See the [IDE Configuration](#ide--ai-extension-configuration) section below for copy-paste configs for the most popular tools.

## What It Does

Once connected, your AI assistant can:

- **Browse all available docs** — list every core topic, the install guide, step-by-step guides, and plugin pages
- **Fetch any documentation page** — retrieve full content for install, routing, middleware, requests, security, and more
- **Read one section** — pull a single heading from a long page instead of the whole document
- **Look up plugin docs** — get full documentation for Twig, ActiveRecord, Session, Tracy, Runway, and the other listed plugins
- **Follow step-by-step guides** — access complete walkthroughs for building blogs and tested applications
- **Search across everything** — find relevant pages across core docs, guides, and plugins at once
- **Check known footguns** — `Flight::get()` is not a route, PdoWrapper is deprecated, and the skeleton uses Twig and `$this->app`

For a new application the server instructions tell the assistant to start from `composer create-project flightphp/skeleton`, keep routes in `app/config/routes.php`, and treat `AGENTS.md` as the source of truth.

### Key Points
- **Zero setup** — the hosted server at `https://mcp.flightphp.com/mcp` requires no installation or API keys.
- **Always current** — the server fetches docs live from [docs.flightphp.com](https://docs.flightphp.com), so it's always up to date.
- **Works everywhere** — any tool that supports the MCP Streamable HTTP transport can connect.
- **Self-hostable** — run your own instance with PHP >= 8.1 and Composer if you prefer.

## IDE / AI Extension Configuration

The server uses Streamable HTTP transport. Pick your extension below and paste in the config.

### Claude Code (CLI)

Run the following command to add it to your project:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Or add it manually to your project's `.mcp.json`:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### GitHub Copilot (VS Code)

Add to `.vscode/mcp.json` in your workspace:

```json
{
  "servers": {
    "flightphp-docs": {
      "type": "http",
      "url": "https://mcp.flightphp.com/mcp"
    }
  }
}
```

### Kilo Code (VS Code)

Add to your VS Code `settings.json`:

```json
{
  "kilocode.mcpServers": {
    "flightphp-docs": {
      "url": "https://mcp.flightphp.com/mcp",
      "transport": "streamable-http"
    }
  }
}
```

### Continue.dev (VS Code / JetBrains)

Add to `~/.continue/config.json`:

```json
{
  "mcpServers": [
    {
      "name": "flightphp-docs",
      "transport": {
        "type": "http",
        "url": "https://mcp.flightphp.com/mcp"
      }
    }
  ]
}
```

## Available Tools

The MCP server exposes the following tools to your AI assistant:

| Tool | Description |
|------|-------------|
| `list_docs_pages` | Lists learn topics and the install guide, with slugs and descriptions |
| `get_docs_page` | Fetches a docs page by slug (e.g. `install`, `routing`, `middleware`, `ai`) |
| `get_docs_section` | Fetches one heading from a learn or install page |
| `list_guide_pages` | Lists all available step-by-step guides |
| `get_guide_page` | Fetches a full guide by slug (e.g. `blog`, `unit-testing`) |
| `list_plugin_pages` | Lists all available plugin and extension pages |
| `get_plugin_docs` | Fetches full plugin documentation by slug (e.g. `twig`, `active-record`, `session`) |
| `search_docs` | Searches across docs, guides, and plugins for a keyword or topic |
| `lookup_api` | Explains a documented footgun (`Flight::get()`, PdoWrapper, views, route return values) and names the page to read |
| `fetch_url` | Fetches any page directly by its full `docs.flightphp.com` URL |
| `generate_learn_page` | Builds a learn-page markdown draft in the docs section style |
| `generate_guide_page` | Builds a guide-page markdown draft with step bodies |
| `generate_plugin_page` | Builds a plugin-page markdown draft focused on Flight integration |

Prompts (`new_flightphp_project`, `implement_flightphp_feature`, `debug_flightphp_issue`, `flightphp_migration_help`, `use_flightphp_plugin`) tell the assistant which pages to read before it writes code.

## Self-Hosting

Prefer to run your own instance? You'll need PHP >= 8.1 and Composer.

```bash
git clone https://github.com/flightphp/mcp.git
cd mcp
composer install
php server.php
```

The server starts on `http://0.0.0.0:8890/mcp` by default. Update your IDE config to point at your local address:

```json
{
  "mcpServers": {
    "flightphp-docs": {
      "type": "http",
      "url": "http://localhost:8890/mcp"
    }
  }
}
```
