<?php

declare(strict_types=1);

use App\Actions\ApproveBooking;
use App\Enums\BookingStatus;
use App\Exceptions\BookingCannotBeApprovedException;
use App\Models\Booking;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;

it('approves a pending booking', function (): void {
    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $roomType = RoomType::factory()->create([
        'property_id' => $property->id,
        'total_rooms' => 2,
    ]);

    $booking = Booking::factory()->create([
        'property_id' => $property->id,
        'room_type_id' => $roomType->id,
        'status' => 'pending',
        'check_in' => '2026-05-01',
        'check_out' => '2026-05-04',
    ]);

    $result = app(ApproveBooking::class)->handle($booking);

    expect($result->status)->toBe(BookingStatus::Approved);
});

it('throws exception when booking is not pending', function (): void {
    $booking = Booking::factory()->approved()->create();

    app(ApproveBooking::class)->handle($booking);
})->throws(BookingCannotBeApprovedException::class);
