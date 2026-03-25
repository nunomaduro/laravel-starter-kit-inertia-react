<?php

declare(strict_types=1);

namespace Modules\BotStudio\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\BotStudio\Models\AgentDefinition;

/**
 * @extends Factory<AgentDefinition>
 */
final class AgentDefinitionFactory extends Factory
{
    protected $model = AgentDefinition::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'system_prompt' => fake()->paragraph(),
            'model' => 'gpt-4o-mini',
            'temperature' => 0.7,
            'max_tokens' => 4096,
            'enabled_tools' => [],
            'knowledge_config' => [],
            'conversation_starters' => [],
            'is_published' => false,
            'is_featured' => false,
            'is_template' => false,
        ];
    }

    public function template(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_template' => true,
            'is_published' => true,
        ]);
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => true,
        ]);
    }

    public function forOrganization(Organization $organization): self
    {
        return $this->state(fn (array $attributes): array => [
            'organization_id' => $organization->id,
        ]);
    }
}
