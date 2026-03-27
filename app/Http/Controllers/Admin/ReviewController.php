<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ReviewController
{
    public function index(): Response
    {
        $reviews = Review::query()
            ->with(['guest', 'property', 'booking'])
            ->latest()
            ->paginate(20);

        return Inertia::render('admin/reviews/index', [
            'reviews' => $reviews,
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->back();
    }
}
