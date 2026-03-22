<?php

declare(strict_types=1);

namespace Modules\Billing\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Billing\Models\Invoice;

final class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return TenantContext::id() !== null && $user->belongsToOrganization(TenantContext::id());
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->belongsToOrganization($invoice->organization_id);
    }

    public function download(User $user, Invoice $invoice): bool
    {
        return $user->belongsToOrganization($invoice->organization_id);
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
