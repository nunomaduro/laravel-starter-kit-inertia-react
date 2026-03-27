<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Review;
use Illuminate\Support\Carbon;
use RuntimeException;

final readonly class RespondToReview
{
    public function handle(Review $review, string $response): Review
    {
        if ($review->host_response !== null) {
            throw new RuntimeException('This review has already been responded to.');
        }

        $review->update([
            'host_response' => $response,
            'host_responded_at' => Carbon::now(),
        ]);

        return $review->refresh();
    }
}
