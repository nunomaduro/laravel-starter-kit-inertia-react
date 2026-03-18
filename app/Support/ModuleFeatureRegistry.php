<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Runtime registry where modules push their feature definitions during register().
 *
 * This avoids merging into config (which breaks config:cache) and provides a
 * single place for FeatureHelper and HandleInertiaRequests to query module features.
 */
final class ModuleFeatureRegistry
{
    /**
     * Inertia-exposed features: key => feature class.
     *
     * @var array<string, class-string>
     */
    private static array $inertiaFeatures = [];

    /**
     * Route feature map: key => feature class.
     *
     * @var array<string, class-string>
     */
    private static array $routeFeatures = [];

    /**
     * Feature metadata: key => ['delegate_to_orgs' => bool, 'plan_required' => ?string].
     *
     * @var array<string, array{delegate_to_orgs: bool, plan_required: string|null}>
     */
    private static array $featureMetadata = [];

    /**
     * Register an Inertia-exposed feature for a module.
     *
     * @param  class-string  $featureClass
     */
    public static function registerInertiaFeature(string $key, string $featureClass): void
    {
        self::$inertiaFeatures[$key] = $featureClass;
    }

    /**
     * Register a route-gated feature for a module.
     *
     * @param  class-string  $featureClass
     */
    public static function registerRouteFeature(string $key, string $featureClass): void
    {
        self::$routeFeatures[$key] = $featureClass;
    }

    /**
     * Register feature metadata (delegation, plan requirements).
     *
     * @param  array{delegate_to_orgs: bool, plan_required: string|null}  $metadata
     */
    public static function registerFeatureMetadata(string $key, array $metadata): void
    {
        self::$featureMetadata[$key] = $metadata;
    }

    /**
     * All module Inertia features merged with static config.
     *
     * @return array<string, class-string>
     */
    public static function allInertiaFeatures(): array
    {
        /** @var array<string, class-string> $static */
        $static = config('feature-flags.inertia_features', []);

        return array_merge($static, self::$inertiaFeatures);
    }

    /**
     * All module route features merged with static config.
     *
     * @return array<string, class-string>
     */
    public static function allRouteFeatures(): array
    {
        /** @var array<string, class-string> $static */
        $static = config('feature-flags.route_feature_map', []);

        return array_merge($static, self::$routeFeatures);
    }

    /**
     * All feature metadata merged with static config.
     *
     * @return array<string, array{delegate_to_orgs: bool, plan_required: string|null}>
     */
    public static function allFeatureMetadata(): array
    {
        /** @var array<string, array{delegate_to_orgs: bool, plan_required: string|null}> $static */
        $static = config('feature-flags.feature_metadata', []);

        return array_merge($static, self::$featureMetadata);
    }

    /**
     * Only module-registered Inertia features (excluding static config).
     *
     * @return array<string, class-string>
     */
    public static function moduleInertiaFeatures(): array
    {
        return self::$inertiaFeatures;
    }

    /**
     * Only module-registered route features (excluding static config).
     *
     * @return array<string, class-string>
     */
    public static function moduleRouteFeatures(): array
    {
        return self::$routeFeatures;
    }

    /**
     * Reset all registrations (useful for testing).
     */
    public static function flush(): void
    {
        self::$inertiaFeatures = [];
        self::$routeFeatures = [];
        self::$featureMetadata = [];
    }
}
