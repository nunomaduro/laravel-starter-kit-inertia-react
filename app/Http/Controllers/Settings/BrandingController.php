<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBrandingRequest;
use App\Models\Organization;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

final class BrandingController extends Controller
{
    public function __construct(
        private readonly OrganizationSettingsService $organizationSettings
    ) {}

    public function edit(Request $request): Response|RedirectResponse
    {
        $organization = TenantContext::get();
        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }
        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $branding = $this->organizationSettings->getBranding($organization);

        return Inertia::render('settings/branding', [
            'branding' => $branding,
            'presetOptions' => collect(config('theme.presets', []))->mapWithKeys(fn (array $v, string $k): array => [$k => $v['label'] ?? $k])->all(),
            'radiusOptions' => config('theme.radii', []),
            'fontOptions' => config('theme.fonts', []),
        ]);
    }

    public function update(UpdateBrandingRequest $request): RedirectResponse
    {
        $organization = TenantContext::get();
        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }
        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $oldRow = DB::table('organization_settings')
                ->where('organization_id', $organization->id)
                ->where('group', 'branding')
                ->where('name', 'logo_path')
                ->first();
            if ($oldRow && Storage::disk('public')->exists(json_decode((string) $oldRow->payload, true, 512, JSON_THROW_ON_ERROR))) {
                Storage::disk('public')->delete(json_decode((string) $oldRow->payload, true, 512, JSON_THROW_ON_ERROR));
            }
            $path = $request->file('logo')->store('branding/logos', 'public');
            $this->organizationSettings->setOverride($organization, 'branding', 'logo_path', $path);
        }

        if (array_key_exists('theme_preset', $data)) {
            $this->organizationSettings->setOverride($organization, 'branding', 'theme_preset', $data['theme_preset'] ?? null);
        }
        if (array_key_exists('theme_radius', $data)) {
            $this->organizationSettings->setOverride($organization, 'branding', 'theme_radius', $data['theme_radius'] ?? null);
        }
        if (array_key_exists('theme_font', $data)) {
            $this->organizationSettings->setOverride($organization, 'branding', 'theme_font', $data['theme_font'] ?? null);
        }
        if (array_key_exists('allow_user_ui_customization', $data)) {
            $this->organizationSettings->setOverride($organization, 'branding', 'allow_user_ui_customization', (bool) ($data['allow_user_ui_customization'] ?? false));
        }

        return to_route('settings.branding.edit')->with('flash', ['status' => 'success', 'message' => 'Branding updated.']);
    }
}
