<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

final readonly class EarningsController
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $propertyIds = $user->properties()->pluck('id');

        $completedQuery = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->where('status', BookingStatus::Completed);

        $totalEarnings = (clone $completedQuery)->sum('host_payout');
        $thisMonth = (clone $completedQuery)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->sum('host_payout');
        $pendingEarnings = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->where('status', BookingStatus::Approved)
            ->sum('host_payout');
        $completedCount = (clone $completedQuery)->count();

        $completedBookings = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->where('status', BookingStatus::Completed)
            ->with(['property', 'guest', 'roomType'])
            ->latest()
            ->paginate(15);

        return Inertia::render('host/earnings/index', [
            'summary' => [
                'total_earnings' => $totalEarnings,
                'this_month' => $thisMonth,
                'pending_payouts' => $pendingEarnings,
                'completed_bookings' => $completedCount,
            ],
            'bookings' => $completedBookings,
        ]);
    }
}
