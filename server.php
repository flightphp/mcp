#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;  // For public!
use Psr\Log\AbstractLogger;

$logger = new class extends AbstractLogger {
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        fwrite(STDERR, sprintf("[%s][%s] %s %s\n", date('Y-m-d H:i:s'), strtoupper((string) $level), $message, empty($context) ? '' : json_encode($context)));
    }
};

try {
    $server = Server::make()
        ->withServerInfo('Flight PHP Framework Docs MCP', '1.0.0')
        ->withLogger($logger)
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