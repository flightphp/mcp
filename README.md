# FlightPHP MCP Server

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-8892BF?style=flat-square)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)](LICENSE)
[![Composer](https://img.shields.io/badge/Composer-Required-blue?style=flat-square)](https://getcomposer.org)

A Model Context Protocol (MCP) server for accessing and summarizing [Flight PHP Framework](https://flightphp.com) documentation. Point any MCP-compatible AI assistant at the hosted server and it gains instant access to FlightPHP's docs — no setup required.

## Quick Start

**The server is publicly hosted at:**

```
https://mcp.flightphp.com/mcp
```

No installation, no API keys. Just add the URL to your AI coding extension and start asking questions about FlightPHP. See the [IDE / AI Extension Configuration](#ide--ai-extension-configuration) section below for copy-paste configs.

## What It Does

Once connected, your AI assistant can:

- **Read the current docs** — install guide, learn pages, step-by-step guides, and plugins
- **Pull one section** — grab a single heading from a long page such as routing
- **Search** — site search plus the local page catalog, with canonical docs URLs
- **Check known footguns** — `Flight::get()`, PdoWrapper, `Flight::render()`, route return values, Latte versus Twig
- **Scaffold docs pages** — learn, guide, and plugin markdown in the docs site's section style
- **Follow project prompts** — new app, feature, debug, migration, and plugin prompts that read the docs first

The server also sends instructions on connect. For a new app those instructions say to start from `flightphp/skeleton`, use `$this->app` and `App\` namespaces, put routes in `app/config/routes.php`, and use Twig. Short `Flight::` samples in the docs are for teaching APIs.

## IDE / AI Extension Configuration

The server uses Streamable HTTP transport. Pick your extension below and paste in the config — that's it.

### GitHub Copilot (VS Code)

Add to `.vscode/mcp.json` in your workspace (or your user-level `settings.json`):

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

### Claude Code (CLI)

Add to your project's `.mcp.json` or run:

```bash
claude mcp add --transport http flightphp-docs https://mcp.flightphp.com/mcp
```

Or manually in `.mcp.json`:

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

Add to `~/.continue/config.json` (or `config.yaml`):

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

| Tool | What it does |
|------|----------------|
| `list_docs_pages` | Lists learn topics and the install guide |
| `get_docs_page` | Fetches a topic such as `install`, `routing`, `ai`, or `simple-pdo` |
| `get_docs_section` | Fetches one heading from a learn or install page |
| `list_guide_pages` / `get_guide_page` | Lists and fetches guides such as `blog` and `unit-testing` |
| `list_plugin_pages` / `get_plugin_docs` | Lists and fetches plugin pages such as `twig`, `active-record`, and `session` |
| `search_docs` | Searches the docs site and the local catalog |
| `lookup_api` | Explains a documented footgun and names the page to read |
| `fetch_url` | Fetches any `https://docs.flightphp.com/` URL |
| `generate_learn_page` | Builds a learn page (Overview, Usage, Troubleshooting, Changelog) |
| `generate_guide_page` | Builds a guide page with prerequisites and step bodies |
| `generate_plugin_page` | Builds a plugin page with Flight setup and usage |

## Prompts

| Prompt | What it does |
|--------|----------------|
| `new_flightphp_project` | Reads install, AI, autoloading, and routing, then scaffolds the skeleton |
| `implement_flightphp_feature` | Reads the topic page before writing a feature |
| `debug_flightphp_issue` | Reads the relevant page before diagnosing |
| `flightphp_migration_help` | Reads the migration or comparison pages first |
| `use_flightphp_plugin` | Reads the plugin page before integration code |

Resources `flightphp://docs/index`, `flightphp://guides/index`, and `flightphp://plugins/index` list the same pages, and the matching templates fetch one page by slug.

---

## Self-Hosting

Prefer to run your own instance? You'll need PHP >= 8.1 and Composer.

```bash
composer install
php server.php
```

The server starts on `http://0.0.0.0:8890/mcp` by default.

### Project Structure

```
flightphp-mcp/
├── composer.json          # Project dependencies (PHP >= 8.1)
├── server.php             # HTTP and stdio entry point
├── src/
│   ├── Fetcher.php        # MCP tools and resources
│   ├── Prompts.php        # MCP prompts
│   ├── DocsCatalog.php    # Page slugs and canonical URLs
│   └── DocsClient.php     # Fetch, redirect check, and cache
└── vendor/                # Composer dependencies
```

### Adding New Tools

1. Add methods to `src/Fetcher.php` or create new classes in `src/`
2. Annotate with `#[McpTool]` to register and `#[Schema]` for parameter descriptions
3. The server auto-discovers tools — no manual registration needed

## Resources

- [Flight PHP Framework](https://flightphp.com)
- [Model Context Protocol](https://modelcontextprotocol.io)
- [PHP MCP Server SDK](https://github.com/php-mcp/server)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.