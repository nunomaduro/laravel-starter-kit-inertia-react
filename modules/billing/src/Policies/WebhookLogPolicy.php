<?php

declare(strict_types=1);

namespace Modules\Billing\Policies;

use App\Models\User;

final class WebhookLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function view(User $user): bool
    {
        return $user->can('manage billing');
    }

    public function create(): bool
    {
        return false;
    }

    public function update(): bool
    {
        return false;
    }

    public function delete(): bool
    {
        return false;
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
