<?php

declare(strict_types=1);

namespace Modules\Billing\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\Subscription;

/**
 * @extends Factory<Subscription>
 */
final class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'subscriber_type' => Organization::class,
            'subscriber_id' => Organization::factory(),
            'plan_id' => Plan::factory(),
            'slug' => \Illuminate\Support\Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => ['en' => $name],
            'description' => ['en' => fake()->sentence()],
            'gateway_subscription_id' => null,
            'quantity' => 1,
            'trial_ends_at' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'canceled_at' => null,
        ];
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'canceled_at' => now(),
        ]);
    }

    public function onTrial(int $days = 14): static
    {
        return $this->state(fn (array $attributes): array => [
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => $quantity,
        ]);
    }
}
