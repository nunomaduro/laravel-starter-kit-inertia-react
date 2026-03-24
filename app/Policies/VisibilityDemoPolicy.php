<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VisibilityDemo;

final class VisibilityDemoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VisibilityDemo $demo): bool
    {
        return $demo->canBeViewedBy($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, VisibilityDemo $demo): bool
    {
        return $demo->canBeEditedBy($user);
    }

    public function delete(User $user, VisibilityDemo $demo): bool
    {
        return $demo->canBeEditedBy($user);
    }

    public function restore(User $user, VisibilityDemo $demo): bool
    {
        return $demo->canBeEditedBy($user);
    }

    public function forceDelete(User $user, VisibilityDemo $demo): bool
    {
        return $demo->canBeEditedBy($user);
    }

    public function shareItem(User $user, VisibilityDemo $demo): bool
    {
        return $demo->canBeEditedBy($user);
    }
}
