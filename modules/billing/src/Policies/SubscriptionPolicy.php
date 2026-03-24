<?php

declare(strict_types=1);

namespace Modules\Billing\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Billing\Models\Subscription;

final class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return TenantContext::id() !== null && $user->belongsToOrganization(TenantContext::id());
    }

    public function view(User $user, Subscription $subscription): bool
    {
        $subscriberId = $subscription->subscriber_id;

        return $user->belongsToOrganization($subscriberId);
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
