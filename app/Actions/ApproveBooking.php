<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Exceptions\BookingCannotBeApprovedException;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

final readonly class ApproveBooking
{
    public function __construct(private CheckRoomAvailability $checkRoomAvailability) {}

    public function handle(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw new BookingCannotBeApprovedException('Only pending bookings can be approved.');
        }

        return DB::transaction(function () use ($booking): Booking {
            Booking::query()
                ->where('room_type_id', $booking->room_type_id)
                ->where('status', BookingStatus::Approved)
                ->whereDate('check_in', '<', $booking->check_out)
                ->whereDate('check_out', '>', $booking->check_in)
                ->lockForUpdate()
                ->get();

            if (! $this->checkRoomAvailability->handle($booking->roomType, $booking->check_in, $booking->check_out)) {
                throw new BookingCannotBeApprovedException('The room is no longer available for the requested dates.');
            }

            $booking->update(['status' => BookingStatus::Approved]);

            return $booking->refresh();
        });
    }
}
