<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
final class BookingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('+1 week', '+3 months');
        $nights = fake()->numberBetween(1, 7);
        $checkOut = (clone $checkIn)->modify("+{$nights} days");
        $totalPrice = fake()->numberBetween(10000, 200000);
        $commissionAmount = (int) round($totalPrice * 0.1);
        $hostPayout = $totalPrice - $commissionAmount;

        return [
            'guest_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_type_id' => RoomType::factory(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests_count' => fake()->numberBetween(1, 4),
            'status' => 'pending',
            'cancelled_by' => null,
            'cancellation_reason' => null,
            'total_price' => $totalPrice,
            'commission_amount' => $commissionAmount,
            'host_payout' => $hostPayout,
            'price_breakdown' => [
                ['night' => 1, 'price' => $totalPrice / $nights],
            ],
            'notes' => null,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'approved',
        ]);
    }

    public function declined(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'declined',
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'completed',
            'check_in' => fake()->dateTimeBetween('-2 months', '-1 week'),
            'check_out' => fake()->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'cancelled',
            'cancelled_by' => fake()->randomElement(['guest', 'host']),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
