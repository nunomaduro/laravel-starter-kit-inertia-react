<?php

declare(strict_types=1);

use App\Actions\CalculateBookingPrice;
use App\DataTransferObjects\BookingPriceBreakdown;
use App\Models\RoomType;
use Illuminate\Support\Carbon;

it('calculates booking price for multiple nights', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 10000]);

    $checkIn = Carbon::parse('2026-05-01');
    $checkOut = Carbon::parse('2026-05-04');

    $result = app(CalculateBookingPrice::class)->handle($roomType, $checkIn, $checkOut, 0.10);

    expect($result)->toBeInstanceOf(BookingPriceBreakdown::class)
        ->and($result->totalPrice)->toBe(30000)
        ->and($result->commissionAmount)->toBe(3000)
        ->and($result->hostPayout)->toBe(27000)
        ->and($result->nightlyBreakdown)->toHaveCount(3)
        ->and($result->nightlyBreakdown)->toHaveKeys(['2026-05-01', '2026-05-02', '2026-05-03']);
});

it('excludes checkout date from pricing', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 15000]);

    $checkIn = Carbon::parse('2026-06-01');
    $checkOut = Carbon::parse('2026-06-02');

    $result = app(CalculateBookingPrice::class)->handle($roomType, $checkIn, $checkOut, 0.10);

    expect($result->totalPrice)->toBe(15000)
        ->and($result->nightlyBreakdown)->toHaveCount(1);
});
