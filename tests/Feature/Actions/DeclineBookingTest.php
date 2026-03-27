<?php

declare(strict_types=1);

use App\Actions\DeclineBooking;
use App\Enums\BookingStatus;
use App\Models\Booking;

it('declines a pending booking', function (): void {
    $booking = Booking::factory()->create(['status' => 'pending']);

    $result = app(DeclineBooking::class)->handle($booking, 'Fully booked');

    expect($result->status)->toBe(BookingStatus::Declined)
        ->and($result->notes)->toBe('Fully booked');
});

it('throws exception when booking is not pending', function (): void {
    $booking = Booking::factory()->approved()->create();

    app(DeclineBooking::class)->handle($booking);
})->throws(RuntimeException::class);
