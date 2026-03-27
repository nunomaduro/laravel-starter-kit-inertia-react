<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Actions\ApproveBooking;
use App\Actions\DeclineBooking;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class BookingController
{
    public function index(Request $request): Response
    {
        $propertyIds = $request->user()->properties()->pluck('id');

        $query = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->with(['property', 'guest', 'roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('host/bookings/index', [
            'bookings' => $bookings,
            'filters' => $request->only(['status']),
        ]);
    }

    public function update(UpdateBookingStatusRequest $request, Booking $booking, ApproveBooking $approveBooking, DeclineBooking $declineBooking): RedirectResponse
    {
        /** @var array{status: string, decline_reason: string|null} $validated */
        $validated = $request->validated();

        if ($validated['status'] === 'approved') {
            Gate::authorize('approve', $booking);
            $approveBooking->handle($booking);
        } else {
            Gate::authorize('decline', $booking);
            $declineBooking->handle($booking, $validated['decline_reason'] ?? null);
        }

        return redirect()->back();
    }
}
