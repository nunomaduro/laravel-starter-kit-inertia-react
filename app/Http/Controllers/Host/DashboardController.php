<?php

declare(strict_types=1);

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

final readonly class DashboardController
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $propertyIds = $user->properties()->pluck('id');

        $totalProperties = $propertyIds->count();
        $totalBookings = Booking::query()->whereIn('property_id', $propertyIds)->count();
        $pendingBookings = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->where('status', BookingStatus::Pending)
            ->count();
        $monthlyEarnings = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->where('status', BookingStatus::Completed)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->sum('host_payout');

        $recentBookings = Booking::query()
            ->whereIn('property_id', $propertyIds)
            ->with(['property', 'guest', 'roomType'])
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('host/dashboard', [
            'stats' => [
                'total_properties' => $totalProperties,
                'total_bookings' => $totalBookings,
                'pending_bookings' => $pendingBookings,
                'monthly_earnings' => $monthlyEarnings,
            ],
            'recentBookings' => $recentBookings,
        ]);
    }
}
