<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\User;

final readonly class PropertyPolicy
{
    public function view(?User $user, Property $property): bool
    {
        if ($property->status === PropertyStatus::Approved) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $user->id === $property->host_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isHost() || $user->isAdmin();
    }

    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->host_id || $user->isAdmin();
    }

    public function delete(User $user, Property $property): bool
    {
        if ($user->id !== $property->host_id && ! $user->isAdmin()) {
            return false;
        }

        return ! $property->bookings()
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
            ->exists();
    }

    public function approve(User $user, Property $property): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, Property $property): bool
    {
        return $user->isAdmin();
    }
}
