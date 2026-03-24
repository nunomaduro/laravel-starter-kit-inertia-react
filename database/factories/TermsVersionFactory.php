<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TermsType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TermsVersion>
 */
final class TermsVersionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'body' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(TermsType::cases()),
            'effective_at' => fake()->date(),
            'summary' => fake()->optional()->sentence(),
            'is_required' => fake()->boolean(80),
        ];
    }

    public function required(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => true,
        ]);
    }

    public function optional(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => false,
        ]);
    }
}
