<?php
declare(strict_types=1);

namespace flight\mcp;

final class MarkdownSections
{
    public static function extract(string $markdown, string $heading): string
    {
        $want = strtolower(trim($heading));
        if ($want === '') {
            throw new \InvalidArgumentException('A heading is required.');
        }

        $lines = preg_split("/\r\n|\n|\r/", $markdown) ?: [];
        $headings = [];
        $start = null;
        $level = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/^(#{1,6})\s+(.+?)\s*$/', $line, $match) !== 1) {
                continue;
            }
            $title = trim($match[2]);
            $headings[] = $title;
            $thisLevel = strlen($match[1]);
            if ($start === null && str_contains(strtolower($title), $want)) {
                $start = $index;
                $level = $thisLevel;
                continue;
            }
            if ($start !== null && $level !== null && $thisLevel <= $level) {
                return trim(implode("\n", array_slice($lines, $start, $index - $start)));
            }
        }

        if ($start !== null) {
            return trim(implode("\n", array_slice($lines, $start)));
        }

        $list = $headings === []
            ? '  (no headings)'
            : implode("\n", array_map(static fn (string $title): string => "  - $title", $headings));

        return "No section matching '$heading'. Headings on this page:\n$list";
    }
}
