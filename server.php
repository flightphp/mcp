#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;
use Psr\Log\AbstractLogger;

$isDebug = in_array('--debug', $argv ?? [], true);

$logger = new class($isDebug) extends AbstractLogger {
    public function __construct(private bool $debug) {}
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if ($level === 'debug' && !$this->debug) {
            return;
        }
        if ($level === 'debug') {
            fwrite(STDERR, sprintf("[%s][DEBUG] %s\n", date('Y-m-d H:i:s'), $message));
        } else {
            fwrite(STDERR, sprintf("[%s][%s] %s %s\n", date('Y-m-d H:i:s'), strtoupper((string) $level), $message, empty($context) ? '' : json_encode($context)));
        }
    }
};

try {
    $server = Server::make()
        ->withServerInfo('Flight PHP Framework Docs MCP', '1.1.0')
        ->withLogger($logger)
        ->build();

    // Auto-discover tools from src/
    $server->discover(basePath: __DIR__, scanDirs: ['src']);

    $isStdio = in_array('--stdio', $argv ?? [], true);
    if ($isStdio) {
        $transport = new StdioServerTransport();
        fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Flight PHP Framework Docs MCP server starting (stdio)\n");
    } else {
        $transport = new StreamableHttpServerTransport(
            host: '0.0.0.0',
            port: 8890,
            mcpPath: '/mcp'
        );
        fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Flight PHP Framework Docs MCP server starting on http://0.0.0.0:8890/mcp\n");
    }
    $server->listen($transport);

} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}