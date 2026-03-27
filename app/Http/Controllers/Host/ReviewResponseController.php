<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Actions\RespondToReview;
use App\Http\Requests\RespondToReviewRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final readonly class ReviewResponseController
{
    public function __invoke(RespondToReviewRequest $request, Review $review, RespondToReview $action): RedirectResponse
    {
        Gate::authorize('respond', $review);

        /** @var array{host_response: string} $validated */
        $validated = $request->validated();

        $action->handle($review, $validated['host_response']);

        return redirect()->back();
    }
}
