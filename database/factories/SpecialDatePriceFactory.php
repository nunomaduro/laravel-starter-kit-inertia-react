<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\SpecialDatePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpecialDatePrice>
 */
final class SpecialDatePriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'price_per_night' => fake()->numberBetween(10000, 100000),
            'label' => fake()->optional(0.5)->randomElement(['New Year', 'Christmas Eve', 'Valentine\'s Day', 'National Holiday']),
        ];
    }
}
