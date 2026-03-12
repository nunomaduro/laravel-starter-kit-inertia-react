<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class OrganizationSettingsService
{
    private const int CACHE_TTL_MINUTES = 60;

    /**
     * Apply all org-specific overrides to config for the given organization.
     *
     * @param  array<string, string>  $overridableKeys  Map of "group.name" => "config.key"
     */
    public function applyForOrganization(Organization $organization, array $overridableKeys): void
    {
        $overrides = $this->getOverridesForOrganization($organization);

        foreach ($overrides as $override) {
            $settingsKey = $override->group.'.'.$override->name;

            if (! isset($overridableKeys[$settingsKey])) {
                continue;
            }

            $value = $this->decodePayload($override->payload, $override->is_encrypted);
            config()->set($overridableKeys[$settingsKey], $value);
        }
    }

    /**
     * Get all overrides for an organization (cached).
     *
     * @return \Illuminate\Support\Collection<int, object{group: string, name: string, payload: string, is_encrypted: bool}>
     */
    public function getOverridesForOrganization(Organization $organization): \Illuminate\Support\Collection
    {
        $cacheKey = $this->cacheKey($organization);

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => DB::table('organization_settings')
                ->where('organization_id', $organization->id)
                ->get(['group', 'name', 'payload', 'is_encrypted']),
        );
    }

    public function setOverride(
        Organization $organization,
        string $group,
        string $name,
        mixed $value,
        bool $encrypt = false,
    ): void {
        $payload = $encrypt
            ? Crypt::encryptString(json_encode($value, JSON_THROW_ON_ERROR))
            : json_encode($value, JSON_THROW_ON_ERROR);

        $exists = DB::table('organization_settings')
            ->where('organization_id', $organization->id)
            ->where('group', $group)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            DB::table('organization_settings')
                ->where('organization_id', $organization->id)
                ->where('group', $group)
                ->where('name', $name)
                ->update([
                    'payload' => $payload,
                    'is_encrypted' => $encrypt,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('organization_settings')->insert([
                'organization_id' => $organization->id,
                'group' => $group,
                'name' => $name,
                'payload' => $payload,
                'is_encrypted' => $encrypt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->clearCache($organization);
    }

    public function removeOverride(Organization $organization, string $group, string $name): void
    {
        DB::table('organization_settings')
            ->where('organization_id', $organization->id)
            ->where('group', $group)
            ->where('name', $name)
            ->delete();

        $this->clearCache($organization);
    }

    public function clearCache(Organization $organization): void
    {
        Cache::forget($this->cacheKey($organization));
    }

    /**
     * Get branding for an organization (group=branding). Used for Inertia shared props.
     *
     * @return array{logoUrl: string|null, logoUrlDark: string|null, themePreset: string|null, themeRadius: string|null, themeFont: string|null, allowUserCustomization: bool}
     */
    public function getBranding(Organization $organization): array
    {
        $overrides = $this->getOverridesForOrganization($organization)
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
            $value = $this->decodePayload($override->payload, $override->is_encrypted);
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
        $overrides = $this->getOverridesForOrganization($organization)
            ->where('group', 'branding')
            ->whereIn('name', ['user_can_change_colors', 'user_can_change_font', 'user_can_change_layout', 'user_can_change_logo'])
            ->keyBy('name');

        return [
            'user_can_change_colors' => (bool) $this->decodeOverrideValue($overrides->get('user_can_change_colors'), true),
            'user_can_change_font' => (bool) $this->decodeOverrideValue($overrides->get('user_can_change_font'), true),
            'user_can_change_layout' => (bool) $this->decodeOverrideValue($overrides->get('user_can_change_layout'), true),
            'user_can_change_logo' => (bool) $this->decodeOverrideValue($overrides->get('user_can_change_logo'), false),
        ];
    }

    private function decodeOverrideValue(mixed $override, mixed $default): mixed
    {
        if ($override === null) {
            return $default;
        }

        return $this->decodePayload($override->payload, $override->is_encrypted);
    }

    private function cacheKey(Organization $organization): string
    {
        return 'org_settings:'.$organization->id;
    }

    private function decodePayload(string $payload, bool|int $isEncrypted): mixed
    {
        $raw = ((bool) $isEncrypted) ? Crypt::decryptString($payload) : $payload;

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }
}
