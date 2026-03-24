<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserTermsAcceptance;

final class UserTermsAcceptancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('access admin panel');
    }

    public function view(User $user, UserTermsAcceptance $userTermsAcceptance): bool
    {
        if ($user->id === $userTermsAcceptance->user_id) {
            return true;
        }

        return $user->can('access admin panel');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserTermsAcceptance $userTermsAcceptance): bool
    {
        return $user->id === $userTermsAcceptance->user_id;
    }

    public function delete(User $user, UserTermsAcceptance $userTermsAcceptance): bool
    {
        return $user->id === $userTermsAcceptance->user_id;
    }

    public function restore(User $user, UserTermsAcceptance $userTermsAcceptance): bool
    {
        return $user->id === $userTermsAcceptance->user_id;
    }

    public function forceDelete(User $user, UserTermsAcceptance $userTermsAcceptance): bool
    {
        return $user->id === $userTermsAcceptance->user_id;
    }
}
