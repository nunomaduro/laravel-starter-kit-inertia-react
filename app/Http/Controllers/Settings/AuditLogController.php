<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AuditLogController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        return Inertia::render('settings/audit-log', [
            'logs' => Inertia::defer(fn () => AuditLog::query()
                ->where('organization_id', $organization->id)
                ->with('actor:id,name,email')
                ->latest('created_at')
                ->paginate(50)
                ->through(fn (AuditLog $log): array => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'actor' => $log->actor ? ['name' => $log->actor->name, 'email' => $log->actor->email] : null,
                    'actor_type' => $log->actor_type,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toIso8601String(),
                ])),
        ]);
    }
}
