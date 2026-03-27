<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class CalendarController
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $properties = $user->properties()->approved()->get(['id', 'name', 'slug', 'city', 'country', 'type', 'status']);

        $selectedPropertyId = $request->input('property', $properties->first()?->id);

        $roomTypes = [];

        if ($selectedPropertyId) {
            /** @var Property|null $property */
            $property = $properties->firstWhere('id', $selectedPropertyId);

            if ($property) {
                $property->load(['roomTypes.bookings' => function (\Illuminate\Database\Eloquent\Builder $q): void {
                    $q->where('status', BookingStatus::Approved);
                }, 'roomTypes.blockedDates']);

                $roomTypes = $property->roomTypes->map(function ($roomType) {
                    $availability = [];

                    // Build availability for current month ± 1 month
                    $start = now()->startOfMonth()->subMonth();
                    $end = now()->endOfMonth()->addMonth();

                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        $dateStr = $date->format('Y-m-d');
                        $bookedCount = $roomType->bookings
                            ->filter(fn ($b) => $b->check_in <= $date && $b->check_out > $date)
                            ->count();
                        $isBlocked = $roomType->blockedDates->contains('date', $date->format('Y-m-d'));

                        $availability[$dateStr] = [
                            'available' => max(0, $roomType->total_rooms - $bookedCount),
                            'total' => $roomType->total_rooms,
                            'blocked' => $isBlocked,
                        ];
                    }

                    return [
                        ...$roomType->toArray(),
                        'availability' => $availability,
                    ];
                })->values();
            }
        }

        return Inertia::render('host/calendar/index', [
            'properties' => $properties,
            'selectedProperty' => $selectedPropertyId,
            'roomTypes' => $roomTypes,
        ]);
    }
}
