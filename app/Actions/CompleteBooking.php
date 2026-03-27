<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use RuntimeException;

final readonly class CompleteBooking
{
    public function handle(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Approved) {
            throw new RuntimeException('Only approved bookings can be completed.');
        }

        if ($booking->check_out->gt(Carbon::today())) {
            throw new RuntimeException('The booking cannot be completed before the check-out date.');
        }

        $booking->update(['status' => BookingStatus::Completed]);

        return $booking->refresh();
    }
}
