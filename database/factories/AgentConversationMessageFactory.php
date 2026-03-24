<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AgentConversationMessage>
 */
final class AgentConversationMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'conversation_id' => AgentConversation::factory(),
            'user_id' => User::factory(),
            'agent' => fake()->randomElement(['assistant', 'researcher', 'coder']),
            'role' => fake()->randomElement(['user', 'assistant', 'system']),
            'content' => fake()->paragraph(),
            'attachments' => [],
            'tool_calls' => [],
            'tool_results' => [],
            'usage' => ['prompt_tokens' => fake()->randomNumber(3), 'completion_tokens' => fake()->randomNumber(3)],
            'meta' => [],
        ];
    }

    public function fromUser(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'user',
            'agent' => null,
        ]);
    }

    public function fromAssistant(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => 'assistant',
            'agent' => 'assistant',
        ]);
    }
}
