<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordAuditLog;
use App\Actions\SuggestThemeFromLogo;
use App\Models\Organization;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use App\Settings\ThemeSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class OrgThemeController extends Controller
{
    public function __construct(
        private readonly OrganizationSettingsService $organizationSettings,
        private readonly RecordAuditLog $auditLog,
    ) {}

    public function save(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();

        $validated = $request->validate([
            'dark' => ['required', 'string', 'in:navy,mirage,mint,black,cinder'],
            'primary' => ['required', 'string', 'in:indigo,blue,green,amber,purple,rose'],
            'light' => ['required', 'string', 'in:slate,gray,neutral'],
            'skin' => ['required', 'string', 'in:shadow,bordered,flat,elevated'],
            'radius' => ['required', 'string', 'in:none,sm,default,md,lg,full'],
            'layout' => ['sometimes', 'string', 'in:main,sideblock'],
            'font' => ['sometimes', 'string', 'in:inter,geist-sans,poppins,outfit,plus-jakarta-sans,instrument-sans'],
            'menuColor' => ['sometimes', 'string', 'in:default,primary,muted'],
            'menuAccent' => ['sometimes', 'string', 'in:subtle,strong,bordered'],
        ]);

        if ($organization instanceof Organization) {
            $this->authorizeCustomize($request);

            $map = [
                'dark' => 'dark_color_scheme',
                'primary' => 'primary_color',
                'light' => 'light_color_scheme',
                'skin' => 'card_skin',
                'radius' => 'border_radius',
                'layout' => 'sidebar_layout',
                'font' => 'font',
                'menuColor' => 'menu_color',
                'menuAccent' => 'menu_accent',
            ];

            $locked = resolve(ThemeSettings::class)->locked_settings;

            $user = $request->user();
            $isAdmin = $user !== null && (
                $user->isOrganizationAdmin()
                || $user->canInOrganization('org.settings.manage', $organization)
            );

            // Apply org-level per-field restrictions for non-admins
            if (! $isAdmin) {
                $orgBranding = $this->organizationSettings->getBrandingUserControls($organization);

                if (! $orgBranding['user_can_change_colors']) {
                    $validated = array_diff_key($validated, array_flip(['dark', 'primary', 'light', 'skin']));
                }

                if (! $orgBranding['user_can_change_font']) {
                    unset($validated['font']);
                }

                if (! $orgBranding['user_can_change_layout']) {
                    unset($validated['layout']);
                }
            }

            $changed = [];
            foreach ($map as $key => $settingName) {
                if (in_array($settingName, $locked, true)) {
                    continue; // silently skip locked settings
                }

                if (! isset($validated[$key])) {
                    continue;
                }

                $this->organizationSettings->setOverride($organization, ThemeSettings::group(), $settingName, $validated[$key]);
                $changed[$settingName] = $validated[$key];
            }

            if ($changed !== []) {
                $this->auditLog->handle(
                    action: 'theme.saved',
                    subjectType: 'theme_setting',
                    subjectId: 'org',
                    newValue: $changed,
                    organizationId: $organization->id,
                );
            }

            return back()->with('flash', ['success' => 'Theme saved for your organization.']);
        }

        // No organization context (single-tenant or global admin): persist to global ThemeSettings.
        $this->authorizeGlobal($request);

        $settings = resolve(ThemeSettings::class);
        $settings->dark_color_scheme = $validated['dark'];
        $settings->primary_color = $validated['primary'];
        $settings->light_color_scheme = $validated['light'];
        $settings->card_skin = $validated['skin'];
        $settings->border_radius = $validated['radius'];

        if (isset($validated['layout'])) {
            $settings->sidebar_layout = $validated['layout'];
        }

        if (isset($validated['font'])) {
            $settings->font = $validated['font'];
        }

        if (isset($validated['menuColor'])) {
            $settings->menu_color = $validated['menuColor'];
        }

        if (isset($validated['menuAccent'])) {
            $settings->menu_accent = $validated['menuAccent'];
        }

        $settings->save();

        return back()->with('flash', ['success' => 'Theme saved.']);
    }

    public function reset(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();

        if ($organization instanceof Organization) {
            $this->authorizeCustomize($request);

            foreach (['dark_color_scheme', 'primary_color', 'light_color_scheme', 'card_skin', 'border_radius', 'sidebar_layout', 'font', 'menu_color', 'menu_accent'] as $name) {
                $this->organizationSettings->removeOverride($organization, ThemeSettings::group(), $name);
            }

            $this->auditLog->handle(
                action: 'theme.reset',
                subjectType: 'theme_setting',
                subjectId: 'org',
                organizationId: $organization->id,
            );

            return back()->with('flash', ['success' => 'Theme reset to organization defaults.']);
        }

        // No organization: reset global ThemeSettings to built-in defaults.
        $this->authorizeGlobal($request);

        $settings = resolve(ThemeSettings::class);
        $settings->dark_color_scheme = 'navy';
        $settings->primary_color = 'indigo';
        $settings->light_color_scheme = 'slate';
        $settings->card_skin = 'shadow';
        $settings->border_radius = 'default';
        $settings->sidebar_layout = 'main';
        $settings->font = 'inter';
        $settings->menu_color = 'default';
        $settings->menu_accent = 'subtle';
        $settings->save();

        return back()->with('flash', ['success' => 'Theme reset to defaults.']);
    }

    public function analyzeLogo(Request $request, SuggestThemeFromLogo $action): JsonResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return response()->json(['message' => 'No organization context.'], 422);
        }

        $user = $request->user();
        $settings = resolve(ThemeSettings::class);

        $canUpload = $user !== null && (
            $user->isOrganizationAdmin()
            || ($user->canInOrganization('org.settings.manage', $organization))
            || (
                $settings->allow_user_logo_upload
                && $settings->allow_user_theme_customization
            )
        );

        abort_unless($canUpload, 403);

        $request->validate([
            'logo' => ['required', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $oldRow = DB::table('organization_settings')
            ->where('organization_id', $organization->id)
            ->where('group', 'branding')
            ->where('name', 'logo_path')
            ->first();

        if ($oldRow) {
            $oldPath = json_decode((string) $oldRow->payload, true);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('logo')->store('branding/logos', 'public');
        $this->organizationSettings->setOverride($organization, 'branding', 'logo_path', $path);

        $this->auditLog->handle(
            action: 'logo.uploaded',
            subjectType: 'theme_setting',
            subjectId: 'logo_path',
            newValue: ['path' => $path],
            organizationId: $organization->id,
        );

        $colorHint = $request->header('X-Color-Hint');
        $suggestion = $action->handle($request->file('logo'), $colorHint ?: null);

        return response()->json([
            'suggestion' => $suggestion,
            'logoUrl' => Storage::disk('public')->url($path),
        ]);
    }

    private function authorizeCustomize(Request $request): void
    {
        $settings = resolve(ThemeSettings::class);
        $user = $request->user();

        $canCustomize = $user !== null && (
            $user->isOrganizationAdmin()
            || (bool) $settings->allow_user_theme_customization
        );

        abort_unless($canCustomize, 403);
    }

    private function authorizeGlobal(Request $request): void
    {
        $settings = resolve(ThemeSettings::class);
        $user = $request->user();

        $canCustomize = $user !== null && (
            $user->isSuperAdmin()
            || (bool) $settings->allow_user_theme_customization
        );

        abort_unless($canCustomize, 403);
    }
}
