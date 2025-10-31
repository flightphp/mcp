# FlightPHP MCP Server

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-8892BF?style=flat-square)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)](LICENSE)
[![Composer](https://img.shields.io/badge/Composer-Required-blue?style=flat-square)](https://getcomposer.org)

A Model Context Protocol (MCP) server for accessing and summarizing Flight PHP Framework documentation. This server provides AI assistants with tools to fetch documentation content and generate summaries, enabling intelligent interactions with FlightPHP docs.

## Overview

This MCP server integrates with the Flight PHP micro-framework documentation, allowing AI assistants to:

- Fetch content from documentation URLs
- Extract key information like APIs and examples
- Generate contextual summaries of documentation

Built on top of the [PHP MCP Server SDK](https://github.com/php-mcp/server), it follows the Model Context Protocol specification to provide standardized access to FlightPHP's documentation resources.

## Features

- **Documentation Fetching**: Retrieve content from FlightPHP documentation pages
- **Content Summarization**: Generate focused summaries based on specific queries
- **HTTP Transport**: Streamable HTTP server for production deployments
- **Auto-Discovery**: Automatic tool registration from source code
- **Error Handling**: Robust error handling for network requests

## Installation

### Prerequisites

- PHP >= 8.1
- Composer

### Install Dependencies

```bash
composer install
```

## Usage

### Running the Server

Start the MCP server using the provided script:

```bash
php server.php
```

The server will start on `http://0.0.0.0:8890/mcp` and listen for MCP protocol messages.

### Available Tools

#### `fetch_url`
Fetches and returns content from a documentation URL.

**Parameters:**
- `url` (string): Full URL to fetch (e.g., a FlightPHP docs page)

**Example:**
```json
{
  "method": "tools/call",
  "params": {
    "name": "fetch_url",
    "arguments": {
      "url": "https://flightphp.com/learn"
    }
  }
}
```

#### `summarize_docs`
Summarizes fetched documentation content based on a query.

**Parameters:**
- `content` (string): The documentation content to summarize
- `query` (string): The specific query or focus for summarization

**Example:**
```json
{
  "method": "tools/call",
  "params": {
    "name": "summarize_docs",
    "arguments": {
      "content": "Flight is a fast, simple, extensible framework...",
      "query": "routing basics"
    }
  }
}
```

## Configuration

The server is configured in `server.php`:

- **Host**: `0.0.0.0` (binds to all interfaces)
- **Port**: `8890`
- **Endpoint**: `/mcp`
- **Server Info**: "Flight PHP Framework Docs MCP" v1.0.0

## Development

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

1. Create new methods in `src/Fetcher.php` or new classes in `src/`
2. Use the `#[McpTool]` attribute to register tools
3. Use `#[Schema]` attributes for parameter descriptions
4. The server auto-discovers tools from the `src/` directory

## Resources

- [Flight PHP Framework](https://flightphp.com)
- [Model Context Protocol](https://modelcontextprotocol.io)
- [PHP MCP Server SDK](https://github.com/php-mcp/server)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.