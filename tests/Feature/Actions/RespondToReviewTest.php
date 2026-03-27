<?php

declare(strict_types=1);

use App\Actions\RespondToReview;
use App\Models\Review;

it('adds host response to a review', function (): void {
    $review = Review::factory()->create();

    $result = app(RespondToReview::class)->handle($review, 'Thank you for your feedback!');

    expect($result->host_response)->toBe('Thank you for your feedback!')
        ->and($result->host_responded_at)->not->toBeNull();
});

it('throws exception when already responded', function (): void {
    $review = Review::factory()->withHostResponse()->create();

    app(RespondToReview::class)->handle($review, 'Another response');
})->throws(RuntimeException::class, 'This review has already been responded to.');
