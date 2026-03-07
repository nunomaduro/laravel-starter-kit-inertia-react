<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use App\Settings\ThemeSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OrgThemeController extends Controller
{
    public function __construct(
        private readonly OrganizationSettingsService $organizationSettings,
    ) {}

    public function save(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();
        if (! $organization instanceof Organization) {
            return back()->with('flash', ['error' => 'No organization selected.']);
        }

        $this->authorizeCustomize($request, $organization);

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

        foreach ($map as $key => $settingName) {
            $this->organizationSettings->setOverride($organization, ThemeSettings::group(), $settingName, $validated[$key]);
        }

        return back()->with('flash', ['success' => 'Theme saved for your organization.']);
    }

    public function reset(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();
        if (! $organization instanceof Organization) {
            return back()->with('flash', ['error' => 'No organization selected.']);
        }

        $this->authorizeCustomize($request, $organization);

        foreach (['dark_color_scheme', 'primary_color', 'light_color_scheme', 'card_skin', 'border_radius', 'sidebar_layout', 'font', 'menu_color', 'menu_accent'] as $name) {
            $this->organizationSettings->removeOverride($organization, ThemeSettings::group(), $name);
        }

        return back()->with('flash', ['success' => 'Theme reset to organization defaults.']);
    }

    private function authorizeCustomize(Request $request, Organization $organization): void
    {
        $settings = app(ThemeSettings::class);
        $user = $request->user();

        $canCustomize = $user !== null && (
            $user->isOrganizationAdmin()
            || (bool) $settings->allow_user_theme_customization
        );

        abort_unless($canCustomize, 403);
    }
}
