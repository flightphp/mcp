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

- **Fetch documentation pages** — retrieve content from any FlightPHP docs URL
- **Summarize docs** — generate focused summaries based on your specific question

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

#### `fetch_url`
Fetches and returns content from a documentation URL.

**Parameters:**
- `url` (string): Full URL to fetch (e.g., a FlightPHP docs page)

#### `summarize_docs`
Summarizes fetched documentation content based on a query.

**Parameters:**
- `content` (string): The documentation content to summarize
- `query` (string): The specific query or focus for summarization

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
├── composer.json          # Project dependencies
├── server.php             # Main server entry point
├── src/
│   └── Fetcher.php        # MCP tools implementation
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