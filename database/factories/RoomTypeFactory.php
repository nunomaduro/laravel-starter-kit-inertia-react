<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
final class RoomTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => fake()->randomElement(['Deluxe Room', 'Standard Suite', 'Ocean View', 'Family Room', 'Presidential Suite']),
            'description' => fake()->paragraph(),
            'max_guests' => fake()->numberBetween(2, 6),
            'base_price_per_night' => fake()->numberBetween(5000, 50000),
            'min_nights' => 1,
            'max_nights' => null,
            'total_rooms' => fake()->numberBetween(1, 10),
        ];
    }
}
