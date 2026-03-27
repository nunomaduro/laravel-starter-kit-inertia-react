<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use RuntimeException;

final readonly class CreateReview
{
    public function handle(Booking $booking, User $guest, int $rating, string $comment): Review
    {
        if ($booking->status !== BookingStatus::Completed) {
            throw new RuntimeException('Reviews can only be left for completed bookings.');
        }

        if ($booking->guest_id !== $guest->id) {
            throw new RuntimeException('Only the booking guest can leave a review.');
        }

        if ($booking->review()->exists()) {
            throw new RuntimeException('A review already exists for this booking.');
        }

        return Review::query()->create([
            'booking_id' => $booking->id,
            'guest_id' => $guest->id,
            'property_id' => $booking->property_id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}
