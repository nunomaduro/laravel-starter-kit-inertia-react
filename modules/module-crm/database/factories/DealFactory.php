<?php

declare(strict_types=1);

namespace Cogneiss\ModuleCrm\Database\Factories;

use Cogneiss\ModuleCrm\Models\Contact;
use Cogneiss\ModuleCrm\Models\Deal;
use Cogneiss\ModuleCrm\Models\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
final class DealFactory extends Factory
{
    protected $model = Deal::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'pipeline_id' => Pipeline::factory(),
            'title' => fake()->catchPhrase(),
            'value' => fake()->randomFloat(2, 1000, 100000),
            'currency' => 'USD',
            'stage' => fake()->randomElement(['Lead', 'Qualified', 'Proposal', 'Negotiation']),
            'probability' => fake()->numberBetween(10, 90),
            'expected_close_date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'closed_at' => null,
            'status' => fake()->randomElement(['open', 'won', 'lost']),
        ];
    }

    public function won(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'won',
            'stage' => 'Closed Won',
            'probability' => 100,
            'closed_at' => now(),
        ]);
    }

    public function lost(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'lost',
            'stage' => 'Closed Lost',
            'probability' => 0,
            'closed_at' => now(),
        ]);
    }

    public function highValue(): self
    {
        return $this->state(fn (array $attributes): array => [
            'value' => fake()->randomFloat(2, 50000, 500000),
        ]);
    }
}
