<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\CancelledBy;
use App\Models\Booking;
use RuntimeException;

final readonly class CancelBooking
{
    public function handle(Booking $booking, CancelledBy $cancelledBy, ?string $reason = null): Booking
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved], true)) {
            throw new RuntimeException('Only pending or approved bookings can be cancelled.');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        return $booking->refresh();
    }
}
