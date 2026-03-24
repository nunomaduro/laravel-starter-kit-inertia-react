<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotificationPreference;
use App\Models\User;

final class NotificationPreferencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NotificationPreference $notificationPreference): bool
    {
        return $user->id === $notificationPreference->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, NotificationPreference $notificationPreference): bool
    {
        return $user->id === $notificationPreference->user_id;
    }

    public function delete(User $user, NotificationPreference $notificationPreference): bool
    {
        return $user->id === $notificationPreference->user_id;
    }

    public function restore(User $user, NotificationPreference $notificationPreference): bool
    {
        return $user->id === $notificationPreference->user_id;
    }

    public function forceDelete(User $user, NotificationPreference $notificationPreference): bool
    {
        return $user->id === $notificationPreference->user_id;
    }
}
