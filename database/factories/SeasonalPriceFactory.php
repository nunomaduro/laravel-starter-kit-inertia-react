<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\SeasonalPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeasonalPrice>
 */
final class SeasonalPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 month', '+6 months');
        $endDate = fake()->dateTimeBetween($startDate->format('Y-m-d').' +1 week', $startDate->format('Y-m-d').' +3 months');

        return [
            'room_type_id' => RoomType::factory(),
            'name' => fake()->randomElement(['Summer Peak', 'Winter Season', 'Holiday Season']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'price_per_night' => fake()->numberBetween(8000, 80000),
        ];
    }
}
