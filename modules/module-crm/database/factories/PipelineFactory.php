<?php

declare(strict_types=1);

namespace Cogneiss\ModuleCrm\Database\Factories;

use Cogneiss\ModuleCrm\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pipeline>
 */
final class PipelineFactory extends Factory
{
    protected $model = Pipeline::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Sales', 'Support', 'Onboarding', 'Renewals', 'Enterprise']),
            'stages' => ['Lead', 'Qualified', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'],
            'is_default' => false,
        ];
    }

    public function default(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }

    /**
     * @param  array<int, string>  $stages
     */
    public function withStages(array $stages): self
    {
        return $this->state(fn (array $attributes): array => [
            'stages' => $stages,
        ]);
    }
}
