<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Storage;

final readonly class OrganizationBrandingService
{
    public function __construct(private OrganizationSettingsService $settings) {}

    /**
     * Get branding for an organization (group=branding). Used for Inertia shared props.
     *
     * @return array{logoUrl: string|null, logoUrlDark: string|null, themePreset: string|null, themeRadius: string|null, themeFont: string|null, allowUserCustomization: bool}
     */
    public function getBranding(Organization $organization): array
    {
        $overrides = $this->settings->getOverridesForOrganization($organization)
            ->where('group', 'branding');

        if ($overrides->isEmpty()) {
            return [
                'logoUrl' => null,
                'logoUrlDark' => null,
                'themePreset' => null,
                'themeRadius' => null,
                'themeFont' => null,
                'allowUserCustomization' => true,
            ];
        }

        $logoPath = null;
        $logoPathDark = null;
        $themePreset = null;
        $themeRadius = null;
        $themeFont = null;
        $allowUserCustomization = true;

        foreach ($overrides as $override) {
            $value = $this->settings->decodePayload($override->payload, $override->is_encrypted);
            match ($override->name) {
                'logo_path' => $logoPath = is_string($value) ? $value : null,
                'logo_path_dark' => $logoPathDark = is_string($value) ? $value : null,
                'theme_preset' => $themePreset = is_string($value) ? $value : null,
                'theme_radius' => $themeRadius = is_string($value) ? $value : null,
                'theme_font' => $themeFont = is_string($value) ? $value : null,
                'allow_user_ui_customization' => $allowUserCustomization = (bool) $value,
                default => null,
            };
        }

        $logoUrl = $logoPath
            ? (Storage::disk('public')->exists($logoPath) ? Storage::disk('public')->url($logoPath) : null)
            : null;

        $logoUrlDark = $logoPathDark
            ? (Storage::disk('public')->exists($logoPathDark) ? Storage::disk('public')->url($logoPathDark) : null)
            : null;

        return [
            'logoUrl' => $logoUrl,
            'logoUrlDark' => $logoUrlDark,
            'themePreset' => $themePreset,
            'themeRadius' => $themeRadius,
            'themeFont' => $themeFont,
            'allowUserCustomization' => $allowUserCustomization,
        ];
    }

    /**
     * Get the per-user branding control flags for an organization (group=branding).
     *
     * @return array{user_can_change_colors: bool, user_can_change_font: bool, user_can_change_layout: bool, user_can_change_logo: bool}
     */
    public function getBrandingUserControls(Organization $organization): array
    {
        $overrides = $this->settings->getOverridesForOrganization($organization)
            ->where('group', 'branding')
            ->whereIn('name', ['user_can_change_colors', 'user_can_change_font', 'user_can_change_layout', 'user_can_change_logo'])
            ->keyBy('name');

        return [
            'user_can_change_colors' => (bool) $this->settings->decodeOverrideValue($overrides->get('user_can_change_colors'), true),
            'user_can_change_font' => (bool) $this->settings->decodeOverrideValue($overrides->get('user_can_change_font'), true),
            'user_can_change_layout' => (bool) $this->settings->decodeOverrideValue($overrides->get('user_can_change_layout'), true),
            'user_can_change_logo' => (bool) $this->settings->decodeOverrideValue($overrides->get('user_can_change_logo'), false),
        ];
    }
}
