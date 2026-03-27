<?php

declare(strict_types=1);

use App\Actions\CompleteBooking;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Carbon;

it('completes an approved booking after checkout', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-10'));

    $booking = Booking::factory()->approved()->create([
        'check_in' => '2026-05-01',
        'check_out' => '2026-05-05',
    ]);

    $result = app(CompleteBooking::class)->handle($booking);

    expect($result->status)->toBe(BookingStatus::Completed);

    Carbon::setTestNow();
});

it('throws exception when booking is not approved', function (): void {
    $booking = Booking::factory()->create(['status' => 'pending']);

    app(CompleteBooking::class)->handle($booking);
})->throws(RuntimeException::class);

it('throws exception when checkout is in the future', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-05-01'));

    $booking = Booking::factory()->approved()->create([
        'check_in' => '2026-05-01',
        'check_out' => '2026-05-05',
    ]);

    app(CompleteBooking::class)->handle($booking);

    Carbon::setTestNow();
})->throws(RuntimeException::class);
