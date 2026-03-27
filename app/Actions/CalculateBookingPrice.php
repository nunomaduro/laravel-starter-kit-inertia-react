<?php

declare(strict_types=1);

namespace App\Actions;

use App\DataTransferObjects\BookingPriceBreakdown;
use App\Models\RoomType;
use Carbon\CarbonInterface;

final readonly class CalculateBookingPrice
{
    public function __construct(private ResolvePriceForDate $resolvePriceForDate) {}

    public function handle(RoomType $roomType, CarbonInterface $checkIn, CarbonInterface $checkOut, float $commissionRate): BookingPriceBreakdown
    {
        $nightlyBreakdown = [];
        $current = $checkIn->copy();

        while ($current->lt($checkOut)) {
            $nightlyBreakdown[$current->toDateString()] = $this->resolvePriceForDate->handle($roomType, $current);
            $current = $current->addDay();
        }

        $totalPrice = array_sum($nightlyBreakdown);
        $commissionAmount = (int) round($totalPrice * $commissionRate);
        $hostPayout = $totalPrice - $commissionAmount;

        return new BookingPriceBreakdown(
            totalPrice: $totalPrice,
            commissionAmount: $commissionAmount,
            hostPayout: $hostPayout,
            nightlyBreakdown: $nightlyBreakdown,
        );
    }
}
