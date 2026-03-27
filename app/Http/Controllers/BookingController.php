<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateBooking;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class BookingController
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->bookings()
            ->with(['property.media', 'roomType']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('bookings/index', [
            'bookings' => $bookings,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Booking $booking): Response
    {
        Gate::authorize('view', $booking);

        $booking->load(['property.media', 'roomType', 'guest', 'review']);

        return Inertia::render('bookings/show', [
            'booking' => $booking,
        ]);
    }

    public function store(StoreBookingRequest $request, CreateBooking $action): RedirectResponse
    {
        /** @var array{room_type_id: string, check_in: string, check_out: string, guests_count: int, notes: string|null} $validated */
        $validated = $request->validated();
        $roomType = RoomType::query()->with('property.host')->findOrFail($validated['room_type_id']);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->handle(
            guest: $user,
            roomType: $roomType,
            checkIn: Carbon::parse($validated['check_in']),
            checkOut: Carbon::parse($validated['check_out']),
            guestsCount: $validated['guests_count'],
            notes: $validated['notes'] ?? null,
        );

        return redirect()->route('bookings.index');
    }
}
