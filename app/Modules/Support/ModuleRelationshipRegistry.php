<?php

declare(strict_types=1);

namespace App\Modules\Support;

use Illuminate\Support\Facades\Log;

/**
 * Central registry for cross-module relationships.
 *
 * Modules register relationships at boot. Relationships whose target module
 * is not installed are gracefully skipped with a log warning.
 */
final class ModuleRelationshipRegistry
{
    /**
     * @var array<string, array<int, ModuleRelationship>>
     */
    private static array $relationships = [];

    /**
     * @var array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private static array $modelMap = [];

    /**
     * Register a model class for a module identifier (e.g., "hr::employee" => Employee::class).
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     */
    public static function registerModel(string $moduleIdentifier, string $modelClass): void
    {
        self::$modelMap[$moduleIdentifier] = $modelClass;
    }

    /**
     * Register a cross-module relationship.
     *
     * If the target module is not installed (model not registered), the relationship
     * is skipped with a warning log. No crashes.
     */
    public static function register(ModuleRelationship $relationship): void
    {
        if (! isset(self::$modelMap[$relationship->targetModel])) {
            Log::warning("Module relationship skipped: target '{$relationship->targetModel}' not installed", [
                'source' => $relationship->sourceModel,
                'target' => $relationship->targetModel,
                'type' => $relationship->type,
            ]);

            return;
        }

        self::$relationships[$relationship->sourceModel][] = $relationship;
    }

    /**
     * Get all relationships for a source model.
     *
     * @return array<int, ModuleRelationship>
     */
    public static function getRelationships(string $sourceModel): array
    {
        return self::$relationships[$sourceModel] ?? [];
    }

    /**
     * Resolve a module identifier to its Eloquent model class.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>|null
     */
    public static function resolveModel(string $moduleIdentifier): ?string
    {
        return self::$modelMap[$moduleIdentifier] ?? null;
    }

    /**
     * Get all registered relationships for AI context.
     *
     * @return array<string, array<int, ModuleRelationship>>
     */
    public static function all(): array
    {
        return self::$relationships;
    }

    /**
     * Get all registered model identifiers.
     *
     * @return array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public static function models(): array
    {
        return self::$modelMap;
    }

    /**
     * Reset the registry (for testing).
     */
    public static function flush(): void
    {
        self::$relationships = [];
        self::$modelMap = [];
    }
}
