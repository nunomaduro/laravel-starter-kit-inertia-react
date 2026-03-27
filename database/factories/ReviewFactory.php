<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
final class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'guest_id' => User::factory(),
            'property_id' => Property::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(),
            'host_response' => null,
            'host_responded_at' => null,
        ];
    }

    public function withHostResponse(): self
    {
        return $this->state(fn (array $attributes): array => [
            'host_response' => fake()->paragraph(),
            'host_responded_at' => now(),
        ]);
    }
}
