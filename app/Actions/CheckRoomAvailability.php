<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\RoomType;
use Carbon\CarbonInterface;

final readonly class CheckRoomAvailability
{
    public function handle(RoomType $roomType, CarbonInterface $checkIn, CarbonInterface $checkOut): bool
    {
        $hasBlockedDates = BlockedDate::query()
            ->where('room_type_id', $roomType->id)
            ->whereDate('date', '>=', $checkIn)
            ->whereDate('date', '<', $checkOut)
            ->exists();

        if ($hasBlockedDates) {
            return false;
        }

        $current = $checkIn->copy();

        while ($current->lt($checkOut)) {
            $bookingsOnDate = Booking::query()
                ->where('room_type_id', $roomType->id)
                ->where('status', BookingStatus::Approved)
                ->whereDate('check_in', '<=', $current)
                ->whereDate('check_out', '>', $current)
                ->count();

            if ($bookingsOnDate >= $roomType->total_rooms) {
                return false;
            }

            $current = $current->addDay();
        }

        return true;
    }
}
