<?php

declare(strict_types=1);

use App\Actions\CancelBooking;
use App\Enums\BookingStatus;
use App\Enums\CancelledBy;
use App\Models\Booking;

it('cancels a pending booking', function (): void {
    $booking = Booking::factory()->create(['status' => 'pending']);

    $result = app(CancelBooking::class)->handle($booking, CancelledBy::Guest, 'Change of plans');

    expect($result->status)->toBe(BookingStatus::Cancelled)
        ->and($result->cancelled_by)->toBe(CancelledBy::Guest)
        ->and($result->cancellation_reason)->toBe('Change of plans');
});

it('cancels an approved booking', function (): void {
    $booking = Booking::factory()->approved()->create();

    $result = app(CancelBooking::class)->handle($booking, CancelledBy::Host);

    expect($result->status)->toBe(BookingStatus::Cancelled)
        ->and($result->cancelled_by)->toBe(CancelledBy::Host);
});

it('throws exception when booking cannot be cancelled', function (): void {
    $booking = Booking::factory()->completed()->create();

    app(CancelBooking::class)->handle($booking, CancelledBy::Guest);
})->throws(RuntimeException::class);
