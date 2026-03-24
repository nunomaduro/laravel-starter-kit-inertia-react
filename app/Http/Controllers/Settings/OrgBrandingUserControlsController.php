<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\RecordAuditLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateOrgBrandingUserControlsRequest;
use App\Models\Organization;
use App\Services\OrganizationBrandingService;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;

final class OrgBrandingUserControlsController extends Controller
{
    public function __construct(
        private readonly OrganizationSettingsService $organizationSettings,
        private readonly OrganizationBrandingService $brandingService,
        private readonly RecordAuditLog $auditLog,
    ) {}

    public function __invoke(UpdateOrgBrandingUserControlsRequest $request): RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        $validated = $request->validated();

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
