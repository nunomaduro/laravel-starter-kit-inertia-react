<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use ReflectionClass;

final class ModelRegistry
{
    /**
     * Get all models in the application.
     *
     * @return array<string>
     */
    public function getAllModels(): array
    {
        $modelsPath = app_path('Models');

        if (! File::isDirectory($modelsPath)) {
            return [];
        }

        $models = [];

        foreach (File::allFiles($modelsPath) as $file) {
            $className = $this->getClassNameFromFile($file->getPathname());
            if ($className === null || ! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);
            if ($reflection->isAbstract() || $reflection->isInterface() || ! $reflection->isSubclassOf(Model::class)) {
                continue;
            }

            $models[] = $className;
        }

        return $models;
    }

    /**
     * Check if a model has a factory.
     */
    public function hasFactory(string $modelClass): bool
    {
        $modelName = class_basename($modelClass);
        $factoryPath = database_path("factories/{$modelName}Factory.php");

        return File::exists($factoryPath);
    }

    /**
     * Check if a model has a seeder.
     *
     * @return array{exists: bool, category: string|null}
     */
    public function hasSeeder(string $modelClass): array
    {
        $modelName = class_basename($modelClass);
        $seederName = "{$modelName}Seeder";

        $categories = ['essential', 'development', 'production'];

        foreach ($categories as $category) {
            $seederPath = database_path("seeders/{$category}/{$seederName}.php");

            if (File::exists($seederPath)) {
                return ['exists' => true, 'category' => $category];
            }
        }

        return ['exists' => false, 'category' => null];
    }

    /**
     * Get audit report for all models.
     *
     * @return array<string, array{factory: bool, seeder: array{exists: bool, category: string|null}}>
     */
    public function getAuditReport(): array
    {
        $models = $this->getAllModels();
        $report = [];

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);

            $report[$modelName] = [
                'factory' => $this->hasFactory($modelClass),
                'seeder' => $this->hasSeeder($modelClass),
            ];
        }

        return $report;
    }

    /**
     * Get class name from file path.
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }

        $namespace = $namespaceMatch[1];
        $className = basename($filePath, '.php');

        return "{$namespace}\\{$className}";
    }
}
