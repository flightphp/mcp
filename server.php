#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;  // For public!

try {
    $server = Server::make()
        ->withServerInfo('Flight PHP Framework Docs MCP', '1.0.0')
        ->build();

    // Auto-discover tools from src/
    $server->discover(basePath: __DIR__, scanDirs: ['src']);

    // **Public HTTP mode** (listen on port 8080)
    $transport = new StreamableHttpServerTransport(
        host: '0.0.0.0',  // Bind to all interfaces
        port: 8890,
        mcpPath: '/mcp'  // Endpoint: yourdomain.com/mcp
    );
    $server->listen($transport);

} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}