<?php

declare(strict_types=1);

namespace Modules\Billing\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Billing\Models\RefundRequest;

final class RefundRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return TenantContext::id() !== null && $user->belongsToOrganization(TenantContext::id());
    }

    public function view(User $user, RefundRequest $refundRequest): bool
    {
        return $user->belongsToOrganization($refundRequest->organization_id);
    }

    public function create(User $user): bool
    {
        return TenantContext::id() !== null && $user->belongsToOrganization(TenantContext::id());
    }

    public function update(User $user, RefundRequest $refundRequest): bool
    {
        return $user->belongsToOrganization($refundRequest->organization_id);
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
