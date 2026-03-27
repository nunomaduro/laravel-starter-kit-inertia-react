<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Actions\CancelBooking;
use App\Enums\CancelledBy;
use App\Http\Requests\CancelBookingRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final readonly class CancelBookingController
{
    public function __invoke(CancelBookingRequest $request, Booking $booking, CancelBooking $action): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        /** @var array{cancellation_reason: string|null} $validated */
        $validated = $request->validated();

        $action->handle($booking, CancelledBy::Host, $validated['cancellation_reason'] ?? null);

        return redirect()->back();
    }
}
