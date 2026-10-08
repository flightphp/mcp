<?php
declare(strict_types=1);

namespace flight\mcp;

final class PageTemplates
{
    public static function plugin(
        string $name,
        string $description,
        string $githubUrl,
        string $composerPackage,
        string $flightSetupExample,
        string $usageExample,
        string $configOptions,
        string $seeAlso,
        string $troubleshooting,
    ): string {
        $lines = ["# {$name}", '', $description];

        if ($githubUrl !== '') {
            $lines[] = '';
            $lines[] = "Visit the [Github repository]({$githubUrl}) for the full source code and details.";
        }

        $lines[] = '';
        $lines[] = '## Installation';
        $lines[] = '';
        if ($composerPackage !== '') {
            $lines[] = 'Install the plugin via Composer:';
            $lines[] = '';
            $lines[] = '```bash';
            $lines[] = "composer require {$composerPackage}";
            $lines[] = '```';
        } else {
            $lines[] = 'Composer package name was not provided. Add a `composer require` command once the package name is known.';
        }

        if ($flightSetupExample !== '') {
            $lines[] = '';
            $lines[] = '## Setup in Flight';
            $lines[] = '';
            $lines[] = '```php';
            $lines[] = $flightSetupExample;
            $lines[] = '```';
        }

        if ($usageExample !== '') {
            $lines[] = '';
            $lines[] = '## Usage';
            $lines[] = '';
            $lines[] = '```php';
            $lines[] = $usageExample;
            $lines[] = '```';
        }

        if ($configOptions !== '') {
            $lines[] = '';
            $lines[] = '## Configuration';
            $lines[] = '';
            $lines[] = $configOptions;
        }

        self::appendSeeAlso($lines, $seeAlso);
        self::appendBullets($lines, 'Troubleshooting', $troubleshooting);

        return implode("\n", $lines);
    }

    public static function learn(
        string $title,
        string $description,
        string $understanding,
        string $basicExample,
        string $advancedExample,
        string $keyPoints,
        string $seeAlso,
        string $troubleshooting,
        string $changelog,
    ): string {
        $lines = [
            "# {$title}",
            '',
            '## Overview',
            '',
            $description,
        ];

        if ($understanding !== '') {
            $lines[] = '';
            $lines[] = '## Understanding';
            $lines[] = '';
            $lines[] = $understanding;
        }

        self::appendCode($lines, 'Basic Usage', $basicExample);
        self::appendCode($lines, 'Advanced Usage', $advancedExample);
        self::appendBullets($lines, 'Key Points', $keyPoints);
        self::appendSeeAlso($lines, $seeAlso);
        self::appendBullets($lines, 'Troubleshooting', $troubleshooting);
        self::appendBullets($lines, 'Changelog', $changelog);

        return implode("\n", $lines);
    }

    public static function guide(
        string $title,
        string $description,
        string $prerequisites,
        string $steps,
        string $seeAlso,
    ): string {
        $lines = ["# {$title}", '', $description, '', '## Prerequisites', ''];
        $prereqLines = self::bulletLines($prerequisites);
        if ($prereqLines === []) {
            $lines[] = '- PHP 8.1+';
            $lines[] = '- Composer';
        } else {
            array_push($lines, ...$prereqLines);
        }

        foreach (self::steps($steps) as $index => $step) {
            $lines[] = '';
            $lines[] = '## Step ' . ($index + 1) . ': ' . $step['title'];
            if ($step['body'] !== '') {
                $lines[] = '';
                $lines[] = $step['body'];
            }
        }

        self::appendSeeAlso($lines, $seeAlso);

        return implode("\n", $lines);
    }

    /** @param list<string> $lines */
    private static function appendCode(array &$lines, string $heading, string $code): void
    {
        if ($code === '') {
            return;
        }
        $lines[] = '';
        $lines[] = "## {$heading}";
        $lines[] = '';
        $lines[] = '```php';
        $lines[] = $code;
        $lines[] = '```';
    }

    /** @param list<string> $lines */
    private static function appendBullets(array &$lines, string $heading, string $body): void
    {
        $bullets = self::bulletLines($body);
        if ($bullets === []) {
            return;
        }
        $lines[] = '';
        $lines[] = "## {$heading}";
        $lines[] = '';
        array_push($lines, ...$bullets);
    }

    /** @param list<string> $lines */
    private static function appendSeeAlso(array &$lines, string $seeAlso): void
    {
        $items = [];
        foreach (self::rawLines($seeAlso) as $line) {
            if (str_contains($line, ' | ')) {
                [$label, $url] = array_map('trim', explode(' | ', $line, 2));
                $items[] = "- [{$label}]({$url})";
                continue;
            }
            $items[] = str_starts_with($line, '-') ? $line : "- {$line}";
        }
        if ($items === []) {
            return;
        }
        $lines[] = '';
        $lines[] = '## See Also';
        $lines[] = '';
        array_push($lines, ...$items);
    }

    /** @return list<string> */
    private static function bulletLines(string $body): array
    {
        $lines = [];
        foreach (self::rawLines($body) as $line) {
            $lines[] = str_starts_with($line, '-') ? $line : "- {$line}";
        }

        return $lines;
    }

    /** @return list<string> */
    private static function rawLines(string $body): array
    {
        if (trim($body) === '') {
            return [];
        }
        $lines = [];
        foreach (preg_split("/\r\n|\n|\r/", $body) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * A line that is only --- separates steps that have bodies.
     * Without that separator, each non-empty line is a step title.
     *
     * @return list<array{title: string, body: string}>
     */
    private static function steps(string $steps): array
    {
        $steps = trim($steps);
        if ($steps === '') {
            return [];
        }

        if (preg_match("/\n\s*---\s*\n/", $steps) === 1) {
            $parsed = [];
            foreach (preg_split("/\n\s*---\s*\n/", $steps) ?: [] as $chunk) {
                $chunk = trim($chunk);
                if ($chunk === '') {
                    continue;
                }
                $chunkLines = preg_split("/\r\n|\n|\r/", $chunk) ?: [];
                $title = trim((string) array_shift($chunkLines));
                $body = trim(implode("\n", $chunkLines));
                if ($title !== '') {
                    $parsed[] = ['title' => $title, 'body' => $body];
                }
            }

            return $parsed;
        }

        $parsed = [];
        foreach (self::rawLines($steps) as $title) {
            $parsed[] = ['title' => $title, 'body' => ''];
        }

        return $parsed;
    }
}
