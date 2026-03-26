<?php

declare(strict_types=1);

namespace Modules\BotStudio\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentEmbedToken;

/**
 * @extends Factory<AgentEmbedToken>
 */
final class AgentEmbedTokenFactory extends Factory
{
    protected $model = AgentEmbedToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_definition_id' => AgentDefinition::factory(),
            'organization_id' => Organization::factory(),
            'token' => hash('sha256', fake()->uuid()),
            'name' => fake()->words(2, true),
            'allowed_domains' => [],
            'is_active' => true,
            'rate_limit_per_minute' => 30,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * @param  array<int, string>  $domains
     */
    public function withDomains(array $domains): self
    {
        return $this->state(fn (array $attributes): array => [
            'allowed_domains' => $domains,
        ]);
    }
}
