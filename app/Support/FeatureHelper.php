<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
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
     * Check if a feature is active for the given user (by feature key).
     * Returns false if the feature is globally disabled, otherwise delegates to Pennant.
     */
    public static function isActiveForKey(string $featureKey, ?User $user = null): bool
    {
        if (self::isGloballyDisabled($featureKey)) {
            return false;
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

    /**
     * Check if a feature key is globally disabled.
     */
    public static function isGloballyDisabled(string $featureKey): bool
    {
        return in_array($featureKey, config('feature-flags.globally_disabled', []), true);
    }

    private static function keyForClass(string $featureClass): ?string
    {
        $all = array_merge(
            config('feature-flags.inertia_features', []),
            config('feature-flags.route_feature_map', [])
        );
        $key = array_search($featureClass, $all, true);

        return $key !== false ? (string) $key : null;
    }

    private static function classForKey(string $featureKey): ?string
    {
        $map = array_merge(
            config('feature-flags.inertia_features', []),
            config('feature-flags.route_feature_map', [])
        );
        $featureClass = $map[$featureKey] ?? null;

        return $featureClass && class_exists($featureClass) ? $featureClass : null;
    }
}
