<?php

declare(strict_types=1);

use App\Actions\CreateBooking;
use App\Enums\BookingStatus;
use App\Exceptions\InvalidBookingDatesException;
use App\Exceptions\RoomNotAvailableException;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Support\Carbon;

it('creates a booking successfully', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-04-01'));

    $host = User::factory()->host()->create(['commission_rate' => '0.1000']);
    $property = Property::factory()->create(['host_id' => $host->id]);
    $roomType = RoomType::factory()->create([
        'property_id' => $property->id,
        'base_price_per_night' => 10000,
        'max_guests' => 4,
        'min_nights' => 1,
        'total_rooms' => 2,
    ]);
    $guest = User::factory()->create();

    $booking = app(CreateBooking::class)->handle(
        $guest,
        $roomType,
        Carbon::parse('2026-04-10'),
        Carbon::parse('2026-04-13'),
        2,
        'Late check-in',
    );

    expect($booking)->toBeInstanceOf(Booking::class)
        ->and($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->guest_id)->toBe($guest->id)
        ->and($booking->total_price)->toBe(30000)
        ->and($booking->commission_amount)->toBe(3000)
        ->and($booking->host_payout)->toBe(27000)
        ->and($booking->notes)->toBe('Late check-in');

    Carbon::setTestNow();
});

it('throws exception when guests exceed capacity', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-04-01'));

    $roomType = RoomType::factory()->create(['max_guests' => 2]);
    $guest = User::factory()->create();

    app(CreateBooking::class)->handle(
        $guest,
        $roomType,
        Carbon::parse('2026-04-10'),
        Carbon::parse('2026-04-12'),
        5,
    );

    Carbon::setTestNow();
})->throws(InvalidBookingDatesException::class);

it('throws exception when stay is too short', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-04-01'));

    $roomType = RoomType::factory()->create([
        'min_nights' => 3,
        'max_guests' => 4,
    ]);
    $guest = User::factory()->create();

    app(CreateBooking::class)->handle(
        $guest,
        $roomType,
        Carbon::parse('2026-04-10'),
        Carbon::parse('2026-04-11'),
        2,
    );

    Carbon::setTestNow();
})->throws(InvalidBookingDatesException::class);

it('throws exception when check-in is in the past', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-04-15'));

    $roomType = RoomType::factory()->create(['max_guests' => 4]);
    $guest = User::factory()->create();

    app(CreateBooking::class)->handle(
        $guest,
        $roomType,
        Carbon::parse('2026-04-01'),
        Carbon::parse('2026-04-03'),
        2,
    );

    Carbon::setTestNow();
})->throws(InvalidBookingDatesException::class);

it('throws exception when room is not available', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-04-01'));

    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $roomType = RoomType::factory()->create([
        'property_id' => $property->id,
        'total_rooms' => 1,
        'max_guests' => 4,
        'min_nights' => 1,
    ]);

    BlockedDate::factory()->create([
        'room_type_id' => $roomType->id,
        'date' => '2026-04-11',
    ]);

    $guest = User::factory()->create();

    app(CreateBooking::class)->handle(
        $guest,
        $roomType,
        Carbon::parse('2026-04-10'),
        Carbon::parse('2026-04-13'),
        2,
    );

    Carbon::setTestNow();
})->throws(RoomNotAvailableException::class);
