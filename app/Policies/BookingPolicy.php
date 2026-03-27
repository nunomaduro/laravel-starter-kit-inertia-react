<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

final readonly class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->guest_id
            || $user->id === $booking->property?->host_id
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $user->id === $booking->property?->host_id
            && $booking->status === BookingStatus::Pending;
    }

    public function decline(User $user, Booking $booking): bool
    {
        return $user->id === $booking->property?->host_id
            && $booking->status === BookingStatus::Pending;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Approved], true)) {
            return false;
        }

        return $user->id === $booking->guest_id
            || $user->id === $booking->property?->host_id;
    }
}
