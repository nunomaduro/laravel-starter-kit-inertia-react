<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\RecordAuditLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateOrgFeaturesRequest;
use App\Models\Organization;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use App\Support\FeatureHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class OrgFeaturesController extends Controller
{
    public function __construct(
        private readonly OrganizationSettingsService $organizationSettings,
        private readonly RecordAuditLog $auditLog,
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $delegatable = FeatureHelper::getDelegatableFeatures();
        $features = [];

        foreach ($delegatable as $key => $meta) {
            $override = FeatureHelper::getOrgFeatureOverride($key, $organization);
            $features[] = [
                'key' => $key,
                'plan_required' => $meta['plan_required'],
                'override' => $override, // 'inherit' | 'enabled' | 'disabled'
            ];
        }

        return Inertia::render('settings/features', [
            'features' => $features,
        ]);
    }

    public function update(UpdateOrgFeaturesRequest $request): RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        $validated = $request->validated();

        $key = $validated['key'];
        $override = $validated['override'];

        $delegatable = FeatureHelper::getDelegatableFeatures();
        abort_unless(isset($delegatable[$key]), 422, 'Feature not delegatable.');

        $old = FeatureHelper::getOrgFeatureOverride($key, $organization);

        if ($override === 'inherit') {
            $this->organizationSettings->removeOverride($organization, 'features', $key);
        } else {
            $this->organizationSettings->setOverride($organization, 'features', $key, $override);
        }

        $this->auditLog->handle(
            action: 'feature.toggled',
            subjectType: 'feature',
            subjectId: $key,
            oldValue: $old,
            newValue: $override,
            organizationId: $organization->id,
        );

        return back()->with('flash', ['status' => 'success', 'message' => 'Feature setting updated.']);
    }
}
