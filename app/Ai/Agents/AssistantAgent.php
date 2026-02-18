<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Eznix86\AI\Memory\Middleware\WithMemory;
use Eznix86\AI\Memory\Tools\RecallMemory;
use Eznix86\AI\Memory\Tools\StoreMemory;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Example agent with semantic memory (store/recall) and WithMemory middleware.
 *
 * Pass context (e.g. ['user_id' => $user->id]) so memories are scoped per user.
 * Uses eznix86/laravel-ai-memory for embeddings + pgvector storage and recall.
 */
final class AssistantAgent implements Agent, HasMiddleware, HasTools
{
    use Promptable;

    public function __construct(
        protected array $context = [],
        protected int $recallLimit = 10,
    ) {}

    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant with memory. You can store and recall information from previous conversations. '
            .'Use the Store Memory tool to save important facts the user shares (e.g. preferences, decisions). '
            .'Use the Recall Memory tool when you need to look up what you already know about the user or the topic.';
    }

    public function tools(): iterable
    {
        return [
            (new RecallMemory)->context($this->context)->limit($this->recallLimit),
            (new StoreMemory)->context($this->context),
        ];
    }

    public function middleware(): array
    {
        return [
            new WithMemory($this->context, limit: (int) config('memory.middleware_recall_limit', 5)),
        ];
    }
}
