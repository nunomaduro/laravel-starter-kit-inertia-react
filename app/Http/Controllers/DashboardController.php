<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Contact\Models\ContactSubmission;

final class DashboardController
{
    public function __construct(private readonly DashboardMetricsService $metrics) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $props = $user->isSuperAdmin()
            ? [...$this->metrics->superAdminProps(), 'contactSubmissionsCount' => ContactSubmission::query()->count()]
            : [];

        $props['weeklyStats'] = Inertia::defer(fn (): array => $this->metrics->weeklySignupStats());

        return Inertia::render('dashboard', $props);
    }
}
