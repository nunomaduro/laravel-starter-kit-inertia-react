<?php

declare(strict_types=1);

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravelcm\Subscriptions\Interval;
use Modules\Billing\Models\Plan;

/**
 * @extends Factory<Plan>
 */
final class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ['en' => $name],
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => ['en' => fake()->sentence()],
            'is_active' => true,
            'price' => fake()->randomFloat(2, 5, 200),
            'is_per_seat' => false,
            'price_per_seat' => 0,
            'signup_fee' => 0,
            'currency' => 'usd',
            'trial_period' => 0,
            'trial_interval' => Interval::DAY->value,
            'invoice_period' => 1,
            'invoice_interval' => Interval::MONTH->value,
            'grace_period' => 0,
            'grace_interval' => Interval::DAY->value,
            'prorate_day' => null,
            'prorate_period' => null,
            'prorate_extend_due' => null,
            'active_subscribers_limit' => null,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes): array => [
            'invoice_period' => 1,
            'invoice_interval' => Interval::YEAR->value,
        ]);
    }

    public function withTrial(int $days = 14): static
    {
        return $this->state(fn (array $attributes): array => [
            'trial_period' => $days,
            'trial_interval' => Interval::DAY->value,
        ]);
    }

    public function perSeat(float $pricePerSeat = 10.00): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_per_seat' => true,
            'price_per_seat' => $pricePerSeat,
        ]);
    }
}
