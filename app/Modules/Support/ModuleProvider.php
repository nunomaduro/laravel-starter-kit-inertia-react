<?php

declare(strict_types=1);

namespace App\Modules\Support;

use App\Modules\Contracts\DeclaresModuleRelationships;
use App\Modules\Contracts\ProvidesAIContext;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Base class for module service providers.
 *
 * Handles: migration loading, model registration in the relationship registry,
 * AI context registration, and cross-module relationship setup.
 *
 * Modules extend this and implement manifest(), and optionally ProvidesAIContext
 * and DeclaresModuleRelationships.
 */
abstract class ModuleProvider extends ServiceProvider
{
    /**
     * The module's manifest describing its metadata.
     */
    abstract public function manifest(): ModuleManifest;

    final public function register(): void
    {
        // Register models in the relationship registry
        $manifest = $this->manifest();
        $moduleName = mb_strtolower($manifest->name);

        foreach ($manifest->models as $modelClass) {
            $shortName = mb_strtolower(class_basename($modelClass));
            ModuleRelationshipRegistry::registerModel("{$moduleName}::{$shortName}", $modelClass);
        }
    }

    final public function boot(): void
    {
        $this->loadMigrations();
        $this->registerAIContext();
        $this->registerRelationships();
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
     * The module's migration path.
     */
    protected function migrationPath(): string
    {
        return $this->moduleBasePath().'/database/migrations';
    }

    /**
     * The module's base path (override in concrete providers).
     */
    protected function moduleBasePath(): string
    {
        $reflection = new ReflectionClass(static::class);

        return dirname((string) $reflection->getFileName(), 2);
    }

    private function loadMigrations(): void
    {
        $path = $this->migrationPath();

        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    private function registerAIContext(): void
    {
        if ($this instanceof ProvidesAIContext) {
            $this->app->tag([$this::class], 'module.ai_context');
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
