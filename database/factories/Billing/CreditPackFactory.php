<?php

declare(strict_types=1);

namespace Database\Factories\Billing;

use App\Models\Billing\CreditPack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditPack>
 */
final class CreditPackFactory extends Factory
{
    protected $model = CreditPack::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'credits' => fake()->numberBetween(10, 500),
            'bonus_credits' => 0,
            'price' => fake()->numberBetween(500, 50000),
            'currency' => 'usd',
            'validity_days' => 365,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
