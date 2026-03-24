<?php

declare(strict_types=1);

namespace Modules\Billing\Policies;

use App\Models\User;

final class PlanPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function update(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function delete(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function restore(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function forceDelete(User $user): bool
    {
        return $user->can('manage billing');
    }
}
