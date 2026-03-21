<?php

declare(strict_types=1);

namespace App\Services;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Response;

use function Laravel\Ai\agent;

/**
 * Thin wrapper around laravel/ai SDK to absorb API changes in one place.
 *
 * All AI functionality (text generation, embeddings, agent conversations)
 * should go through this service. If the SDK changes, only this file updates.
 *
 * For MCP/Relay tool integration, use PrismService instead.
 */
final readonly class AIService
{
    /**
     * Send a message to an agent and get a response.
     *
     * @param  array<string, mixed>  $context  Optional context for the agent
     */
    public function chat(Agent $agent, string $message): Response
    {
        return agent($agent)->respond($message);
    }

    /**
     * Continue an existing conversation.
     *
     * @param  class-string<Agent>  $agentClass
     */
    public function continueConversation(string $agentClass, string $conversationId, string $message): Response
    {
        return agent(new $agentClass)->continue($conversationId)->respond($message);
    }

    /**
     * Generate embeddings for the given texts.
     *
     * @param  array<int, string>  $texts
     * @return array<int, array<int, float>>
     */
    public function embeddings(array $texts): array
    {
        if ($texts === []) {
            return [];
        }

        // Truncate texts exceeding embedding model token limit (~8192 tokens ≈ ~32k chars)
        $maxChars = 32_000;
        $truncated = array_map(
            fn (string $text): string => mb_strlen($text) > $maxChars
                ? mb_substr($text, 0, $maxChars)
                : $text,
            $texts,
        );

        $response = Embeddings::for($truncated)->generate();

        return $response->all();
    }

    /**
     * Generate a single embedding for the given text.
     *
     * @return array<int, float>
     */
    public function embedding(string $text): array
    {
        $results = $this->embeddings([$text]);

        return $results[0] ?? [];
    }

    /**
     * Check if the default AI provider is configured and available.
     */
    public function isAvailable(): bool
    {
        $provider = config('ai.default', '');

        if (blank($provider)) {
            return false;
        }

        $key = config("ai.providers.{$provider}.key", '');

        return ! blank($key);
    }
}
