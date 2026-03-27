<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use RuntimeException;

final readonly class DeclineBooking
{
    public function handle(Booking $booking, ?string $reason = null): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw new RuntimeException('Only pending bookings can be declined.');
        }

        $attributes = ['status' => BookingStatus::Declined];

        if ($reason !== null) {
            $attributes['notes'] = $reason;
        }

        $booking->update($attributes);

        return $booking->refresh();
    }
}
