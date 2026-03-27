<?php

declare(strict_types=1);

use App\Actions\CreateReview;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\RoomType;
use App\Models\User;

it('creates a review for a completed booking', function (): void {
    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $roomType = RoomType::factory()->create(['property_id' => $property->id]);
    $guest = User::factory()->create();

    $booking = Booking::factory()->completed()->create([
        'guest_id' => $guest->id,
        'property_id' => $property->id,
        'room_type_id' => $roomType->id,
    ]);

    $review = app(CreateReview::class)->handle($booking, $guest, 5, 'Amazing stay!');

    expect($review)->toBeInstanceOf(Review::class)
        ->and($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Amazing stay!')
        ->and($review->guest_id)->toBe($guest->id)
        ->and($review->property_id)->toBe($property->id);
});

it('throws exception for non-completed booking', function (): void {
    $booking = Booking::factory()->create(['status' => 'pending']);
    $guest = User::factory()->create();

    app(CreateReview::class)->handle($booking, $guest, 5, 'Great');
})->throws(RuntimeException::class, 'Reviews can only be left for completed bookings.');

it('throws exception when guest does not match', function (): void {
    $booking = Booking::factory()->completed()->create();
    $otherGuest = User::factory()->create();

    app(CreateReview::class)->handle($booking, $otherGuest, 5, 'Great');
})->throws(RuntimeException::class, 'Only the booking guest can leave a review.');

it('throws exception when review already exists', function (): void {
    $guest = User::factory()->create();
    $booking = Booking::factory()->completed()->create(['guest_id' => $guest->id]);

    Review::factory()->create([
        'booking_id' => $booking->id,
        'guest_id' => $guest->id,
        'property_id' => $booking->property_id,
    ]);

    app(CreateReview::class)->handle($booking, $guest, 4, 'Another review');
})->throws(RuntimeException::class, 'A review already exists for this booking.');
