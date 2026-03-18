<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

/**
 * Centralizes feature flag checks with support for globally disabled modules.
 * When a feature key is in globally_disabled, it is always off for everyone (including super-admins).
 */
final class FeatureHelper
{
    /**
     * Check if a feature is active for the given user (by feature class).
     * Returns false if the feature is globally disabled, otherwise delegates to Pennant.
     */
    public static function isActiveForClass(string $featureClass, ?User $user = null): bool
    {
        $key = self::keyForClass($featureClass);
        if ($key !== null && self::isGloballyDisabled($key)) {
            return false;
        }

        $u = $user ?? auth()->user();
        if (! $u) {
            return (new $featureClass)->defaultValue ?? false;
        }

        return Feature::for($u)->active($featureClass);
    }

    /**
     * Get the org-level feature override ('inherit' | 'enabled' | 'disabled').
     */
    public static function getOrgFeatureOverride(string $featureKey, Organization $organization): string
    {
        $payload = DB::table('organization_settings')
            ->where('organization_id', $organization->id)
            ->where('group', 'features')
            ->where('name', $featureKey)
            ->value('payload');

        if ($payload === null) {
            return 'inherit';
        }

        $value = json_decode((string) $payload, true);

        return in_array($value, ['enabled', 'disabled'], true) ? $value : 'inherit';
    }

    /**
     * Return feature metadata for delegatable features.
     *
     * @return array<string, array{delegate_to_orgs: bool, plan_required: string|null}>
     */
    public static function getDelegatableFeatures(): array
    {
        return array_filter(
            ModuleFeatureRegistry::allFeatureMetadata(),
            fn (array $meta): bool => $meta['delegate_to_orgs'] === true,
        );
    }

    /**
     * Check if a feature key is globally disabled.
     */
    public static function isGloballyDisabled(string $featureKey): bool
    {
        return in_array($featureKey, config('feature-flags.globally_disabled', []), true);
    }

    /**
     * Resolve a feature key from its class, checking both static config and module registry.
     */
    public static function keyForClass(string $featureClass): ?string
    {
        $all = array_merge(
            ModuleFeatureRegistry::allInertiaFeatures(),
            ModuleFeatureRegistry::allRouteFeatures(),
        );
        $key = array_search($featureClass, $all, true);

        return $key !== false ? (string) $key : null;
    }

    /**
     * Resolve a feature class from its key, checking both static config and module registry.
     */
    public static function classForKey(string $featureKey): ?string
    {
        $map = array_merge(
            ModuleFeatureRegistry::allInertiaFeatures(),
            ModuleFeatureRegistry::allRouteFeatures(),
        );
        $featureClass = $map[$featureKey] ?? null;

        return $featureClass && class_exists($featureClass) ? $featureClass : null;
    }

    /**
     * Check if a feature key is active, considering module enabled status.
     * If the feature belongs to a disabled module, it is always off.
     */
    public static function isActiveForKey(string $featureKey, ?User $user = null): bool
    {
        if (self::isGloballyDisabled($featureKey)) {
            return false;
        }

        // If the feature is registered by a module, check module enabled status.
        $moduleFeatures = ModuleFeatureRegistry::moduleInertiaFeatures();
        $moduleRouteFeatures = ModuleFeatureRegistry::moduleRouteFeatures();
        if (isset($moduleFeatures[$featureKey]) || isset($moduleRouteFeatures[$featureKey])) {
            // Module is registered, which means it's enabled — proceed to Pennant check.
        } else {
            // Check if this key exists in static config; if not, it's unknown.
            $staticClass = config("feature-flags.inertia_features.{$featureKey}")
                ?? config("feature-flags.route_feature_map.{$featureKey}");
            if ($staticClass === null) {
                return false;
            }
        }

        // Org-level override (inherit | enabled | disabled)
        $organization = TenantContext::get();
        if ($organization instanceof Organization) {
            $override = self::getOrgFeatureOverride($featureKey, $organization);
            if ($override === 'disabled') {
                return false;
            }

            if ($override === 'enabled') {
                return true;
            }

            // 'inherit' → fall through to Pennant
        }

        $featureClass = self::classForKey($featureKey);
        if ($featureClass === null) {
            return false;
        }

        $u = $user ?? auth()->user();
        if (! $u) {
            return (new $featureClass)->defaultValue ?? false;
        }

        return Feature::for($u)->active($featureClass);
    }
}
