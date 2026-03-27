<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CancelBooking;
use App\Enums\CancelledBy;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final readonly class CancelBookingController
{
    public function __invoke(Booking $booking, CancelBooking $action): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $action->handle($booking, CancelledBy::Guest);

        return redirect()->back();
    }
}
