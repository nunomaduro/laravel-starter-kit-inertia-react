<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Middleware\WithMemoryUnlessUnavailable;
use App\Ai\Tools\UsersIndexAiTool;
use App\Ai\Tools\UsersShowAiTool;
use Eznix86\AI\Memory\Tools\RecallMemory;
use Eznix86\AI\Memory\Tools\StoreMemory;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

/**
 * Example agent with semantic memory (store/recall), WithMemory middleware, and conversation persistence.
 *
 * Use forUser($user) for a new conversation or continue($conversationId, $user) to continue.
 * Pass context (e.g. ['user_id' => $user->id]) so memories are scoped per user.
 */
final class AssistantAgent implements Agent, Conversational, HasMiddleware, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(
        private array $context = [],
        private int $recallLimit = 10,
    ) {}

    public function instructions(): string
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
            new UsersIndexAiTool,
            new UsersShowAiTool,
        ];
    }

    public function middleware(): array
    {
        return [
            new WithMemoryUnlessUnavailable($this->context, limit: (int) config('memory.middleware_recall_limit', 5)),
        ];
    }
}
