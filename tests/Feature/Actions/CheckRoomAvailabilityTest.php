<?php

declare(strict_types=1);

use App\Actions\CheckRoomAvailability;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Carbon;

it('returns true when room is available', function (): void {
    $roomType = RoomType::factory()->create(['total_rooms' => 2]);

    $result = app(CheckRoomAvailability::class)->handle(
        $roomType,
        Carbon::parse('2026-05-01'),
        Carbon::parse('2026-05-04'),
    );

    expect($result)->toBeTrue();
});

it('returns false when date is blocked', function (): void {
    $roomType = RoomType::factory()->create(['total_rooms' => 2]);

    BlockedDate::factory()->create([
        'room_type_id' => $roomType->id,
        'date' => '2026-05-02',
    ]);

    $result = app(CheckRoomAvailability::class)->handle(
        $roomType,
        Carbon::parse('2026-05-01'),
        Carbon::parse('2026-05-04'),
    );

    expect($result)->toBeFalse();
});

it('returns false when all rooms are booked', function (): void {
    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $roomType = RoomType::factory()->create([
        'property_id' => $property->id,
        'total_rooms' => 1,
    ]);

    Booking::factory()->approved()->create([
        'property_id' => $property->id,
        'room_type_id' => $roomType->id,
        'check_in' => '2026-05-01',
        'check_out' => '2026-05-05',
    ]);

    $result = app(CheckRoomAvailability::class)->handle(
        $roomType,
        Carbon::parse('2026-05-02'),
        Carbon::parse('2026-05-04'),
    );

    expect($result)->toBeFalse();
});

it('returns true when some rooms are still available', function (): void {
    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $roomType = RoomType::factory()->create([
        'property_id' => $property->id,
        'total_rooms' => 2,
    ]);

    Booking::factory()->approved()->create([
        'property_id' => $property->id,
        'room_type_id' => $roomType->id,
        'check_in' => '2026-05-01',
        'check_out' => '2026-05-05',
    ]);

    $result = app(CheckRoomAvailability::class)->handle(
        $roomType,
        Carbon::parse('2026-05-02'),
        Carbon::parse('2026-05-04'),
    );

    expect($result)->toBeTrue();
});
