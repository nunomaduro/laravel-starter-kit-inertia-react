<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SlugRedirect>
 */
final class SlugRedirectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'old_slug' => fake()->unique()->slug(),
            'organization_id' => Organization::factory(),
            'redirects_to_slug' => fake()->unique()->slug(),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
