<?php

declare(strict_types=1);

namespace App\Modules\Support;

use App\Modules\Contracts\DeclaresModuleRelationships;
use App\Modules\Contracts\ProvidesAIContext;
use App\Modules\Contracts\ProvidesAITools;
use App\Support\ModuleFeatureRegistry;
use App\Support\ModuleNavigationRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Base class for module service providers.
 *
 * Handles: enabled/disabled toggling, migration loading, model registration,
 * route loading, feature registration, navigation registration, AI context
 * registration, and cross-module relationship setup.
 *
 * Modules extend this and implement manifest(). Optionally implement
 * ProvidesAIContext and DeclaresModuleRelationships.
 */
abstract class ModuleProvider extends ServiceProvider
{
    /**
     * The module's manifest describing its metadata.
     */
    abstract public function manifest(): ModuleManifest;

    /**
     * Derive the config key from the manifest name (lowercased).
     */
    final public function moduleKey(): string
    {
        return mb_strtolower($this->manifest()->name);
    }

    /**
     * Whether this module is enabled via config/modules.php.
     */
    final public function isEnabled(): bool
    {
        return (bool) config("modules.{$this->moduleKey()}", false);
    }

    final public function register(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->registerModels();
        $this->registerFeature();
        $this->registerModule();
    }

    final public function boot(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->loadMigrations();
        $this->loadRoutes();
        $this->registerAIContext();
        $this->registerAITools();
        $this->registerRelationships();
        $this->registerNavigation();
        $this->bootModule();
    }

    /**
     * Hook for subclasses to add custom boot logic (e.g. policy registration).
     */
    protected function bootModule(): void
    {
        //
    }

    /**
     * Hook for subclasses to add custom register logic.
     */
    protected function registerModule(): void
    {
        //
    }

    /**
     * The Pennant feature class for this module, if any.
     * Return a fully-qualified class name to enable feature registration.
     */
    protected function featureClass(): ?string
    {
        return null;
    }

    /**
     * Feature metadata for this module.
     *
     * @return array{delegate_to_orgs: bool, plan_required: string|null}
     */
    protected function featureMetadata(): array
    {
        return ['delegate_to_orgs' => true, 'plan_required' => null];
    }

    /**
     * The module's migration path.
     */
    protected function migrationPath(): string
    {
        return $this->moduleBasePath().'/database/migrations';
    }

    /**
     * The module's base path (root directory of the module package).
     *
     * Assumes providers live at: {module-root}/src/Providers/{ProviderClass}.php
     * Override if your module uses a different directory structure.
     */
    protected function moduleBasePath(): string
    {
        $reflection = new ReflectionClass(static::class);

        return dirname((string) $reflection->getFileName(), 3);
    }

    /**
     * Resolve a path relative to the module's src directory.
     */
    protected function moduleSourcePath(string $path = ''): string
    {
        $base = $this->moduleBasePath().'/src';

        return $path !== '' ? $base.'/'.$path : $base;
    }

    /**
     * Resolve a path relative to the module's root directory.
     */
    protected function modulePath(string $path = ''): string
    {
        $base = $this->moduleBasePath();

        return $path !== '' ? $base.'/'.$path : $base;
    }

    private function registerModels(): void
    {
        $manifest = $this->manifest();
        $moduleName = $this->moduleKey();

        foreach ($manifest->models as $modelClass) {
            $shortName = mb_strtolower(class_basename($modelClass));
            ModuleRelationshipRegistry::registerModel("{$moduleName}::{$shortName}", $modelClass);
        }
    }

    private function registerFeature(): void
    {
        $key = $this->moduleKey();
        $featureClass = $this->featureClass();

        if ($featureClass !== null) {
            ModuleFeatureRegistry::registerInertiaFeature($key, $featureClass);
            ModuleFeatureRegistry::registerRouteFeature($key, $featureClass);
            ModuleFeatureRegistry::registerFeatureMetadata($key, $this->featureMetadata());
        }
    }

    private function loadMigrations(): void
    {
        $path = $this->migrationPath();

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    private function loadRoutes(): void
    {
        $routesPath = $this->moduleBasePath().'/routes/web.php';

        if (file_exists($routesPath)) {
            Route::middleware('web')->group($routesPath);
        }
    }

    private function registerNavigation(): void
    {
        $manifest = $this->manifest();

        if (! empty($manifest->navigation)) {
            ModuleNavigationRegistry::registerGroup($this->moduleKey(), $manifest->navigation);
        }
    }

    private function registerAIContext(): void
    {
        if ($this instanceof ProvidesAIContext) {
            $this->app->tag([$this::class], 'module.ai_context');
        }
    }

    private function registerAITools(): void
    {
        if ($this instanceof ProvidesAITools) {
            $this->app->tag([$this::class], 'module.ai_tools');
        }
    }

    private function registerRelationships(): void
    {
        if ($this instanceof DeclaresModuleRelationships) {
            foreach ($this->relationships() as $relationship) {
                ModuleRelationshipRegistry::register($relationship);
            }
        }
    }
}
