<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\RoomTypePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomTypePrice>
 */
final class RoomTypePriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'price_per_night' => fake()->numberBetween(5000, 60000),
        ];
    }
}
