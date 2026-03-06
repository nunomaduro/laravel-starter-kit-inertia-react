<?php

declare(strict_types=1);

namespace App\Testing;

use App\Services\SeedScenarioManager;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;

final class SeedHelper
{
    private static ?SeedScenarioManager $scenarioManager = null;

    /**
     * Auto-seed a model and its relationships.
     *
     * @param  class-string<Model>  $modelClass
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function seedFor(string $modelClass, int $count = 1)
    {
        throw_unless(class_exists($modelClass), InvalidArgumentException::class, sprintf('Model class %s does not exist', $modelClass));

        throw_unless(is_subclass_of($modelClass, Model::class), InvalidArgumentException::class, $modelClass.' is not an Eloquent model');

        $model = new $modelClass;
        $table = $model->getTable();

        throw_unless(Schema::hasTable($table), RuntimeException::class, sprintf('Table %s does not exist. Run migrations first.', $table));

        // Check if model has factory
        $factoryClass = 'Database\\Factories\\'.class_basename($modelClass).'Factory';

        throw_unless(class_exists($factoryClass), RuntimeException::class, sprintf('Factory %s does not exist. Create it first.', $factoryClass));

        // Seed parent relationships first
        self::seedRelationships($modelClass);

        // Create the models
        return $modelClass::factory()->count($count)->create();
    }

    /**
     * Seed multiple models at once.
     *
     * @param  array<class-string<Model>|array{class: class-string<Model>, count: int}>  $models
     * @return array<string, \Illuminate\Database\Eloquent\Collection>
     */
    public static function seedMany(array $models): array
    {
        $results = [];

        foreach ($models as $key => $model) {
            if (is_string($model)) {
                $modelClass = $model;
                $count = 1;
            } else {
                $modelClass = $model['class'];
                $count = $model['count'] ?? 1;
            }

            $modelName = is_string($key) ? $key : class_basename($modelClass);
            $results[$modelName] = self::seedFor($modelClass, $count);
        }

        return $results;
    }

    /**
     * Seed using a named scenario.
     *
     * @return array<string, mixed>
     */
    public static function seedScenario(string $scenarioName): array
    {
        if (! self::$scenarioManager instanceof SeedScenarioManager) {
            self::$scenarioManager = new SeedScenarioManager;
        }

        return self::$scenarioManager->executeScenario($scenarioName);
    }

    /**
     * Get scenario manager instance.
     */
    public static function getScenarioManager(): SeedScenarioManager
    {
        if (! self::$scenarioManager instanceof SeedScenarioManager) {
            self::$scenarioManager = new SeedScenarioManager;
        }

        return self::$scenarioManager;
    }

    /**
     * Seed relationships for a model.
     *
     * @param  class-string<Model>  $modelClass
     */
    private static function seedRelationships(string $modelClass): void
    {
        $model = new $modelClass;
        $reflection = new ReflectionClass($modelClass);

        // Get all belongsTo relationships
        foreach ($reflection->getMethods() as $method) {
            if ($method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $returnType = $method->getReturnType();

            if ($returnType === null) {
                continue;
            }

            $returnTypeName = $returnType->getName();

            // Check if it's a relationship method
            if (! method_exists($returnTypeName, 'getRelated')) {
                continue;
            }

            // For belongsTo relationships, seed the parent
            try {
                $relationship = $model->{$method->getName()}();

                if (method_exists($relationship, 'getForeignKeyName')) {
                    $relatedModel = $relationship->getRelated();

                    // Check if related model exists and has records
                    if ($relatedModel::query()->count() === 0) {
                        self::seedFor($relatedModel::class, 1);
                    }
                }
            } catch (Exception) {
                // Skip if relationship can't be resolved
                continue;
            }
        }
    }
}
