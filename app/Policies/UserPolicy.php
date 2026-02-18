<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use STS\FilamentImpersonate\Facades\Impersonation;

final class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if (Impersonation::isImpersonating()) {
            return true;
        }

        return $user->can('view users');
    }

    public function view(User $actor): bool
    {
        return $actor->can('view users');
    }

    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    public function update(User $actor): bool
    {
        return $actor->can('edit users');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isLastSuperAdmin()) {
            return false;
        }

        return $user->can('delete users');
    }

    public function restore(User $user): bool
    {
        return $user->can('edit users');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($model->isLastSuperAdmin()) {
            return false;
        }

        return $user->can('delete users');
    }
}
