<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateReview;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final readonly class ReviewController
{
    public function store(StoreReviewRequest $request, Property $property, CreateReview $action): RedirectResponse
    {
        /** @var array{booking_id: string, rating: int, comment: string} $validated */
        $validated = $request->validated();
        $booking = Booking::query()->findOrFail($validated['booking_id']);

        Gate::authorize('create', [\App\Models\Review::class, $booking]);

        /** @var \App\Models\User $guest */
        $guest = $request->user();

        $action->handle(
            booking: $booking,
            guest: $guest,
            rating: $validated['rating'],
            comment: $validated['comment'],
        );

        return redirect()->back();
    }
}
