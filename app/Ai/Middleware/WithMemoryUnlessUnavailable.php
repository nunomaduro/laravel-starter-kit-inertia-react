<?php

declare(strict_types=1);

namespace App\Ai\Middleware;

use Closure;
use Eznix86\AI\Memory\Services\MemoryManager;
use Laravel\Ai\Prompts\AgentPrompt;
use Throwable;

/**
 * Runs memory recall when possible; proceeds without memories if embeddings/recall fail
 * (e.g. when only OpenRouter is configured and default_for_embeddings uses OpenAI).
 */
final class WithMemoryUnlessUnavailable
{
    public function __construct(
        private array $context = [],
        private ?int $limit = null,
    ) {}

    public function handle(AgentPrompt $prompt, Closure $next): mixed
    {
        if ($this->context === []) {
            return $next($prompt);
        }

        $limit = $this->limit ?? (int) config('memory.middleware_recall_limit', 5);

        try {
            $memoryManager = app(MemoryManager::class);
            $memories = $memoryManager->recall($prompt->prompt, $this->context, $limit);
        } catch (Throwable) {
            return $next($prompt);
        }

        if ($memories->isNotEmpty()) {
            $memoryContext = $memories->map(fn ($memory): string => "- {$memory->content}")->implode("\n");
            $prompt = $prompt->prepend("Relevant memories from previous conversations:\n{$memoryContext}");
        }

        return $next($prompt);
    }
}
