<?php

declare(strict_types=1);

use App\Actions\ResolvePriceForDate;
use App\Models\RoomType;
use App\Models\RoomTypePrice;
use App\Models\SeasonalPrice;
use App\Models\SpecialDatePrice;
use Illuminate\Support\Carbon;

it('returns special date price when available', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 10000]);
    $date = Carbon::parse('2026-05-01');

    SpecialDatePrice::factory()->create([
        'room_type_id' => $roomType->id,
        'date' => $date,
        'price_per_night' => 25000,
    ]);

    $price = app(ResolvePriceForDate::class)->handle($roomType, $date);

    expect($price)->toBe(25000);
});

it('returns seasonal price when no special date price exists', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 10000]);
    $date = Carbon::parse('2026-05-15');

    SeasonalPrice::factory()->create([
        'room_type_id' => $roomType->id,
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-31',
        'price_per_night' => 18000,
    ]);

    $price = app(ResolvePriceForDate::class)->handle($roomType, $date);

    expect($price)->toBe(18000);
});

it('returns day of week price when no seasonal or special price exists', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 10000]);
    $date = Carbon::parse('2026-05-04'); // Monday

    RoomTypePrice::factory()->create([
        'room_type_id' => $roomType->id,
        'day_of_week' => 1, // Monday
        'price_per_night' => 12000,
    ]);

    $price = app(ResolvePriceForDate::class)->handle($roomType, $date);

    expect($price)->toBe(12000);
});

it('returns base price as fallback', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 10000]);
    $date = Carbon::parse('2026-05-01');

    $price = app(ResolvePriceForDate::class)->handle($roomType, $date);

    expect($price)->toBe(10000);
});

it('prioritizes special date over seasonal price', function (): void {
    $roomType = RoomType::factory()->create(['base_price_per_night' => 10000]);
    $date = Carbon::parse('2026-05-15');

    SeasonalPrice::factory()->create([
        'room_type_id' => $roomType->id,
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-31',
        'price_per_night' => 18000,
    ]);

    SpecialDatePrice::factory()->create([
        'room_type_id' => $roomType->id,
        'date' => $date,
        'price_per_night' => 30000,
    ]);

    $price = app(ResolvePriceForDate::class)->handle($roomType, $date);

    expect($price)->toBe(30000);
});
