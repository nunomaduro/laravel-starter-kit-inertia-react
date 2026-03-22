<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use App\Services\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

final readonly class BillingDashboardController
{
    public function index(): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 403, 'No organization selected.');

        return Inertia::render('billing/index', [
            'organization' => $organization->only(['id', 'name', 'billing_email']),
            'creditBalance' => $organization->creditBalance(),
            'activePlan' => $organization->activePlan()?->only(['id', 'name']) ?? null,
            'isOnTrial' => $organization->isOnTrial(),
            'invoices' => $organization->billingHistory(5),
        ]);
    }
}
