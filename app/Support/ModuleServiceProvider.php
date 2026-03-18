<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\ServiceProvider;

/**
 * Abstract base class for module service providers.
 *
 * Each module extends this and implements the abstract methods. The base class
 * handles feature registration via ModuleFeatureRegistry, route loading, and
 * migration loading so modules follow a consistent pattern.
 *
 * Subclasses may override bootModule() and registerModule() for custom logic.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The module directory name (e.g. "blog", "contact").
     */
    abstract public function moduleName(): string;

    /**
     * The feature key used in Pennant and Inertia shared props (e.g. "blog", "contact").
     */
    abstract public function featureKey(): string;

    /**
     * The fully-qualified feature class name (e.g. BlogFeature::class).
     *
     * @return class-string
     */
    abstract public function featureClass(): string;

    /**
     * Whether this module is enabled in config/modules.php.
     */
    final public function isEnabled(): bool
    {
        return (bool) config("modules.{$this->moduleName()}", false);
    }

    final public function register(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->registerFeature();
        $this->registerModule();
    }

    final public function boot(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
        $this->bootModule();
    }

    /**
     * Hook for subclasses to add custom registration logic.
     */
    protected function registerModule(): void
    {
        //
    }

    /**
     * Hook for subclasses to add custom boot logic (e.g. event listeners).
     */
    protected function bootModule(): void
    {
        //
    }

    /**
     * Feature metadata for delegation and plan gating.
     * Override in subclass to customize.
     *
     * @return array{delegate_to_orgs: bool, plan_required: string|null}
     */
    protected function featureMetadata(): array
    {
        return ['delegate_to_orgs' => true, 'plan_required' => null];
    }

    /**
     * Register the module's feature in the ModuleFeatureRegistry.
     */
    protected function registerFeature(): void
    {
        ModuleFeatureRegistry::registerInertiaFeature($this->featureKey(), $this->featureClass());
        ModuleFeatureRegistry::registerRouteFeature($this->featureKey(), $this->featureClass());
        ModuleFeatureRegistry::registerFeatureMetadata($this->featureKey(), $this->featureMetadata());
    }

    /**
     * Load routes from the module's routes directory.
     */
    protected function loadModuleRoutes(): void
    {
        $routesPath = $this->modulePath('routes/web.php');
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }

    /**
     * Load migrations from the module's database/migrations directory.
     */
    protected function loadModuleMigrations(): void
    {
        $migrationsPath = $this->modulePath('database/migrations');
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    /**
     * Get the absolute path to a file within the module directory.
     */
    protected function modulePath(string $path = ''): string
    {
        $base = base_path("modules/{$this->moduleName()}");

        return $path !== '' ? $base.'/'.$path : $base;
    }
}
