<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

final class EnhancedRelationshipAnalyzer
{
    /**
     * Analyze model to detect all relationships using reflection.
     *
     * @return array<string, array{type: string, model: string|null, foreignKey: string|null, localKey: string|null, pivotTable: string|null}>
     */
    public function analyzeModel(string $modelClass): array
    {
        if (! class_exists($modelClass)) {
            return [];
        }

        $reflection = new ReflectionClass($modelClass);
        $relationships = [];

        foreach ($reflection->getMethods() as $method) {
            if (! $this->isRelationshipMethod($method)) {
                continue;
            }

            $relationship = $this->extractRelationship($method, $modelClass);

            if ($relationship !== null) {
                $relationships[$method->getName()] = $relationship;
            }
        }

        return $relationships;
    }

    /**
     * Generate intelligent seeder code for relationships.
     *
     * @param  array<string, array{type: string, model: string|null, foreignKey: string|null, localKey: string|null, pivotTable: string|null}>  $relationships
     */
    public function generateRelationshipSeederCode(array $relationships): string
    {
        if ($relationships === []) {
            return '';
        }

        $code = "\n    /**\n     * Seed relationships (idempotent).\n     */\n    private function seedRelationships(): void\n    {\n";

        // Group by relationship type
        $belongsTo = [];
        $hasMany = [];
        $belongsToMany = [];

        foreach ($relationships as $relName => $rel) {
            if ($rel['type'] === 'belongsTo' && $rel['model'] !== null) {
                $belongsTo[] = $rel;
            } elseif (in_array($rel['type'], ['hasMany', 'hasOne'], true) && $rel['model'] !== null) {
                $hasMany[] = $rel;
            } elseif ($rel['type'] === 'belongsToMany' && $rel['model'] !== null) {
                $belongsToMany[] = $rel;
            }
        }

        // Seed belongsTo relationships first (dependencies) - idempotent
        foreach ($belongsTo as $relName => $rel) {
            $relatedModel = $rel['model'];
            $code .= "        // Ensure {$relatedModel} exists for {$relName} (idempotent)\n";
            $code .= "        if (\\App\\Models\\{$relatedModel}::query()->count() === 0) {\n";
            $code .= "            \\App\\Models\\{$relatedModel}::factory()->count(5)->create();\n";
            $code .= "        }\n\n";
        }

        // Note about hasMany relationships (seeded after main model)
        if ($hasMany !== []) {
            $code .= "        // Note: hasMany relationships are seeded after main model creation\n";
        }

        // Note about belongsToMany relationships
        if ($belongsToMany !== []) {
            $code .= "        // Note: belongsToMany relationships require pivot table seeding\n";
        }

        return $code."    }\n";
    }

    /**
     * Check if method is a relationship method.
     */
    private function isRelationshipMethod(ReflectionMethod $method): bool
    {
        if (! $method->isPublic()) {
            return false;
        }

        if ($method->getNumberOfParameters() > 0) {
            return false;
        }

        if (str_starts_with($method->getName(), '__')) {
            return false;
        }

        $returnType = $method->getReturnType();

        if ($returnType === null) {
            return false;
        }

        $returnTypeName = $returnType->getName();

        return is_subclass_of($returnTypeName, \Illuminate\Database\Eloquent\Relations\Relation::class);
    }

    /**
     * Extract relationship details from method.
     *
     * @return array{type: string, model: string|null, foreignKey: string|null, localKey: string|null, pivotTable: string|null}|null
     */
    private function extractRelationship(ReflectionMethod $method, string $modelClass): ?array
    {
        $returnType = $method->getReturnType();
        $methodName = $method->getName();

        if ($returnType === null) {
            return null;
        }

        $returnTypeName = $returnType->getName();
        $type = $this->inferRelationshipType($returnTypeName);

        if ($type === null) {
            return null;
        }

        // Try to get relationship details by instantiating the model
        try {
            $model = new $modelClass;
            $relationship = $method->invoke($model);

            if (! $relationship instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                return null;
            }

            $relatedModel = $relationship->getRelated()::class;
            $relatedModelName = class_basename($relatedModel);

            $details = [
                'type' => $type,
                'model' => $relatedModelName,
                'foreignKey' => null,
                'localKey' => null,
                'pivotTable' => null,
            ];

            // Extract relationship-specific details
            if ($relationship instanceof BelongsTo) {
                $details['foreignKey'] = $relationship->getForeignKeyName();
                $details['ownerKey'] = $relationship->getOwnerKeyName();
            } elseif ($relationship instanceof HasOne || $relationship instanceof HasMany) {
                $details['foreignKey'] = $relationship->getForeignKeyName();
                $details['localKey'] = $relationship->getLocalKeyName();
            } elseif ($relationship instanceof BelongsToMany) {
                $details['pivotTable'] = $relationship->getTable();
                $details['foreignKey'] = $relationship->getForeignPivotKeyName();
                $details['relatedKey'] = $relationship->getRelatedPivotKeyName();
            } elseif ($relationship instanceof HasOneThrough || $relationship instanceof HasManyThrough) {
                $details['foreignKey'] = $relationship->getForeignKeyName();
                $details['localKey'] = $relationship->getLocalKeyName();
            }

            return $details;
        } catch (Exception) {
            // Fallback: infer from method name and type
            $relatedModel = $this->inferRelatedModel($methodName);

            return [
                'type' => $type,
                'model' => $relatedModel,
                'foreignKey' => null,
                'localKey' => null,
                'pivotTable' => null,
            ];
        }
    }

    /**
     * Infer relationship type from return type name.
     *
     * More specific types (e.g. BelongsToMany) must be checked before their
     * substring matches (e.g. BelongsTo) to avoid incorrect classification.
     */
    private function inferRelationshipType(string $returnType): ?string
    {
        return match (true) {
            Str::contains($returnType, 'MorphToMany') => 'morphToMany',
            Str::contains($returnType, 'MorphTo') => 'morphTo',
            Str::contains($returnType, 'MorphMany') => 'morphMany',
            Str::contains($returnType, 'MorphOne') => 'morphOne',
            Str::contains($returnType, 'BelongsToMany') => 'belongsToMany',
            Str::contains($returnType, 'BelongsTo') => 'belongsTo',
            Str::contains($returnType, 'HasManyThrough') => 'hasManyThrough',
            Str::contains($returnType, 'HasOneThrough') => 'hasOneThrough',
            Str::contains($returnType, 'HasMany') => 'hasMany',
            Str::contains($returnType, 'HasOne') => 'hasOne',
            default => null,
        };
    }

    /**
     * Infer related model name from method name.
     */
    private function inferRelatedModel(string $methodName): ?string
    {
        // Remove common prefixes
        $name = $methodName;

        foreach (['belongsTo', 'hasMany', 'hasOne'] as $prefix) {
            if (Str::startsWith($name, $prefix)) {
                $name = Str::after($name, $prefix);

                break;
            }
        }

        // Convert to model name (e.g., "posts" -> "Post", "userProfile" -> "UserProfile")
        return Str::studly(Str::singular($name));
    }
}
