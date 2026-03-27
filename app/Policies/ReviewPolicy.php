<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;

final readonly class ReviewPolicy
{
    public function create(User $user, Booking $booking): bool
    {
        return $user->id === $booking->guest_id
            && $booking->status === BookingStatus::Completed
            && ! $booking->review()->exists();
    }

    public function respond(User $user, Review $review): bool
    {
        return $user->id === $review->property?->host_id
            && $review->host_response === null;
    }
}
