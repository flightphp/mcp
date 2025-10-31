<?php
declare(strict_types=1);

namespace flight\mcp;

use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class Fetcher
{
    /**
     * Fetches and returns content from a URL (e.g., docs page).
     * Scans for key info like APIs, examples.
     */
    #[McpTool(name: 'fetch_url')]
    public function fetchUrl(
        #[Schema(description: 'Full URL to fetch (e.g., docs page)')]
        string $url
    ): string {
        $content = @file_get_contents($url);
        if ($content === false) {
            throw new \InvalidArgumentException("Failed to fetch $url");
        }
        return $content;  // Or parse/summarize with DOMDocument, etc.
    }

    /**
     * Bonus: Summarize fetched content.
     */
    #[McpTool(name: 'summarize_docs')]
    public function summarizeDocs(string $content, string $query): string
    {
        // Simple regex/strpos example; enhance with NLP libs if needed
        return "Summary for '$query': " . substr($content, 0, 500) . '...';
    }
}