<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Enums\Billing\CreditTransactionType;
use App\Models\Billing\Credit;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Credit>
 */
final class CreditFactory extends Factory
{
    protected $model = Credit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->numberBetween(10, 500);
        $type = fake()->randomElement(CreditTransactionType::class);

        return [
            'creditable_type' => Organization::class,
            'creditable_id' => Organization::factory(),
            'amount' => $type === CreditTransactionType::Usage ? -$amount : $amount,
            'running_balance' => 0,
            'type' => $type,
            'description' => fake()->sentence(),
            'metadata' => null,
            'expires_at' => fake()->optional(0.3)->dateTimeBetween('+1 month', '+1 year'),
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CreditTransactionType::Purchase,
            'amount' => abs($attributes['amount'] ?? fake()->numberBetween(50, 500)),
        ]);
    }

    public function usage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CreditTransactionType::Usage,
            'amount' => -fake()->numberBetween(1, 100),
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CreditTransactionType::Adjustment,
            'amount' => fake()->randomElement([1, -1]) * fake()->numberBetween(10, 200),
        ]);
    }
}
