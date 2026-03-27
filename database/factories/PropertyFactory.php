<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
final class PropertyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company().' '.fake()->randomElement(['Resort', 'Villa', 'Hotel']);
        /** @var non-falsy-string $name */

        return [
            'host_id' => User::factory()->host(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(['resort', 'hotel', 'villa']),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'amenities' => fake()->randomElements(
                ['pool', 'wifi', 'parking', 'restaurant', 'gym', 'spa', 'beach_access', 'air_conditioning'],
                fake()->numberBetween(2, 6),
            ),
            'status' => 'approved',
            'is_featured' => false,
            'cancellation_policy' => fake()->paragraph(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    public function villa(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'villa',
        ]);
    }

    public function resort(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'resort',
        ]);
    }

    public function hotel(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'hotel',
        ]);
    }
}
