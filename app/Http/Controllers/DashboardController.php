<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Contact\Models\ContactSubmission;
use Spatie\Activitylog\Models\Activity;

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

        $props['recentActivity'] = Inertia::defer(fn (): array => Activity::query()
            ->with('causer')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn (Activity $activity): array => [
                'id' => (string) $activity->id,
                'action' => $activity->description ?? $activity->event ?? 'unknown',
                'target' => $activity->subject_type ? class_basename($activity->subject_type) : null,
                'description' => $activity->properties->get('attributes')
                    ? collect($activity->properties->get('attributes'))->keys()->take(3)->join(', ')
                    : null,
                'timestamp' => $activity->created_at?->toISOString() ?? '',
                'type' => $activity->log_name ?? 'default',
                'actor' => $activity->causer ? [
                    'name' => $activity->causer->name ?? 'System',
                    'avatar' => $activity->causer->avatar ?? null,
                ] : null,
            ])
            ->all());

        return Inertia::render('dashboard', $props);
    }
}
