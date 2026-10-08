<?php
declare(strict_types=1);

namespace flight\mcp\Completion;

use flight\mcp\DocsCatalog;
use PhpMcp\Server\Contracts\CompletionProviderInterface;
use PhpMcp\Server\Contracts\SessionInterface;

final class GuideCompletion implements CompletionProviderInterface
{
    public function getCompletions(string $currentValue, SessionInterface $session): array
    {
        return DocsCatalog::filterSlugs(DocsCatalog::guideSlugs(), $currentValue);
    }
}
