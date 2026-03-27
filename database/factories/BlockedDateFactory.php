<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlockedDate;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDate>
 */
final class BlockedDateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'reason' => fake()->optional(0.5)->sentence(),
        ];
    }
}
