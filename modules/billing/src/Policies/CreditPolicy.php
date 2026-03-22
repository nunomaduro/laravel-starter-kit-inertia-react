<?php

declare(strict_types=1);

namespace Modules\Billing\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Billing\Models\Credit;

final class CreditPolicy
{
    public function viewAny(User $user): bool
    {
        return TenantContext::id() !== null && $user->belongsToOrganization(TenantContext::id());
    }

    public function view(User $user, Credit $credit): bool
    {
        return $user->belongsToOrganization($credit->organization_id);
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
