<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\RecordAuditLog;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\OrganizationBrandingService;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OrgBrandingUserControlsController extends Controller
{
    public function __construct(
        private readonly OrganizationSettingsService $organizationSettings,
        private readonly OrganizationBrandingService $brandingService,
        private readonly RecordAuditLog $auditLog,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $validated = $request->validate([
            'user_can_change_colors' => ['required', 'boolean'],
            'user_can_change_font' => ['required', 'boolean'],
            'user_can_change_layout' => ['required', 'boolean'],
            'user_can_change_logo' => ['required', 'boolean'],
        ]);

        $existing = $this->brandingService->getBrandingUserControls($organization);

        foreach ($validated as $name => $value) {
            $this->organizationSettings->setOverride($organization, 'branding', $name, (bool) $value);
        }

        $this->auditLog->handle(
            action: 'branding.user_controls.changed',
            subjectType: 'branding',
            subjectId: 'user_controls',
            oldValue: $existing,
            newValue: array_map(fn ($v): bool => (bool) $v, $validated),
            organizationId: $organization->id,
        );

        return back()->with('flash', ['status' => 'success', 'message' => 'User customization controls updated.']);
    }
}
