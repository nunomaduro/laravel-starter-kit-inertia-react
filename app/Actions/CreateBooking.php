<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Exceptions\InvalidBookingDatesException;
use App\Exceptions\RoomNotAvailableException;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class CreateBooking
{
    public function __construct(
        private CheckRoomAvailability $checkRoomAvailability,
        private CalculateBookingPrice $calculateBookingPrice,
    ) {}

    public function handle(User $guest, RoomType $roomType, CarbonInterface $checkIn, CarbonInterface $checkOut, int $guestsCount, ?string $notes = null): Booking
    {
        if ($guestsCount > $roomType->max_guests) {
            throw new InvalidBookingDatesException('The number of guests exceeds the room capacity.');
        }

        $nights = (int) $checkIn->diffInDays($checkOut);

        if ($nights < $roomType->min_nights) {
            throw new InvalidBookingDatesException("The minimum stay is {$roomType->min_nights} nights.");
        }

        if ($roomType->max_nights !== null && $nights > $roomType->max_nights) {
            throw new InvalidBookingDatesException("The maximum stay is {$roomType->max_nights} nights.");
        }

        if ($checkIn->lt(Carbon::today())) {
            throw new InvalidBookingDatesException('The check-in date must be today or in the future.');
        }

        $commissionRate = (float) ($roomType->property->host->commission_rate ?? 0.10);

        return DB::transaction(function () use ($guest, $roomType, $checkIn, $checkOut, $guestsCount, $notes, $commissionRate): Booking {
            Booking::query()
                ->where('room_type_id', $roomType->id)
                ->where('status', BookingStatus::Approved)
                ->whereDate('check_in', '<', $checkOut)
                ->whereDate('check_out', '>', $checkIn)
                ->lockForUpdate()
                ->get();

            if (! $this->checkRoomAvailability->handle($roomType, $checkIn, $checkOut)) {
                throw new RoomNotAvailableException;
            }

            $priceBreakdown = $this->calculateBookingPrice->handle($roomType, $checkIn, $checkOut, $commissionRate);

            return Booking::query()->create([
                'guest_id' => $guest->id,
                'property_id' => $roomType->property_id,
                'room_type_id' => $roomType->id,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'guests_count' => $guestsCount,
                'status' => BookingStatus::Pending,
                'total_price' => $priceBreakdown->totalPrice,
                'commission_amount' => $priceBreakdown->commissionAmount,
                'host_payout' => $priceBreakdown->hostPayout,
                'price_breakdown' => $priceBreakdown->nightlyBreakdown,
                'notes' => $notes,
            ]);
        });
    }
}
