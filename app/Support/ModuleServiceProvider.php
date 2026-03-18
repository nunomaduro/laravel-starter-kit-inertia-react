<?php

declare(strict_types=1);

namespace App\Support;

use Filament\Facades\Filament;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
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
        $this->discoverFilamentResources();
        $this->discoverFilamentWidgets();
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
     * Load routes from the module's routes directory within the web middleware group.
     */
    protected function loadModuleRoutes(): void
    {
        $routesPath = $this->modulePath('routes/web.php');
        if (file_exists($routesPath)) {
            Route::middleware(SubstituteBindings::class)->group($routesPath);
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
     * Discover and register Filament resources from the module's Filament/Resources directory.
     * Uses Filament::serving() so discovery only runs when Filament is serving a request.
     */
    protected function discoverFilamentResources(): void
    {
        $resourcesPath = $this->moduleSourcePath('Filament/Resources');

        if (! is_dir($resourcesPath)) {
            return;
        }

        $namespace = $this->moduleNamespace().'\\Filament\\Resources';

        Filament::serving(function () use ($resourcesPath, $namespace): void {
            /** @var \Filament\Panel $panel */
            foreach (filament()->getPanels() as $panel) {
                $panel->discoverResources(in: $resourcesPath, for: $namespace);
            }
        });
    }

    /**
     * Discover and register Filament widgets from the module's Filament/Widgets directory.
     * Uses Filament::serving() so discovery only runs when Filament is serving a request.
     */
    protected function discoverFilamentWidgets(): void
    {
        $widgetsPath = $this->moduleSourcePath('Filament/Widgets');

        if (! is_dir($widgetsPath)) {
            return;
        }

        $namespace = $this->moduleNamespace().'\\Filament\\Widgets';

        Filament::serving(function () use ($widgetsPath, $namespace): void {
            /** @var \Filament\Panel $panel */
            foreach (filament()->getPanels() as $panel) {
                $panel->discoverWidgets(in: $widgetsPath, for: $namespace);
            }
        });
    }

    /**
     * Get the absolute path to a file within the module's src directory.
     */
    protected function moduleSourcePath(string $path = ''): string
    {
        $base = base_path("modules/{$this->moduleName()}/src");

        return $path !== '' ? $base.'/'.$path : $base;
    }

    /**
     * Get the module's root namespace (e.g. "Modules\Contact").
     */
    protected function moduleNamespace(): string
    {
        return 'Modules\\'.str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $this->moduleName())));
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
