<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class SlugRedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('access admin panel');
    }

    public function view(User $user): bool
    {
        return $user->can('access admin panel');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return false;
    }

    public function delete(User $user): bool
    {
        return false;
    }

    public function restore(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user): bool
    {
        return false;
    }
}
