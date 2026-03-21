<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use ReflectionClass;
use Stringable;
use Throwable;

/**
 * AI tool that lists all Eloquent models in the project.
 *
 * Read-only — does not modify any files or execute any code.
 */
final class ListModelsAiTool implements Tool
{
    public function name(): string
    {
        return 'list_models';
    }

    public function description(): string
    {
        return 'List all Eloquent models in the application with their table names and relationships';
    }

    /**
     * @return array<string, mixed>
     */
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'module' => [
                    'type' => 'string',
                    'description' => 'Filter by module (e.g., "hr", "crm"). Leave empty for all models.',
                ],
            ],
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $models = [];
        $module = $request->arguments['module'] ?? null;

        // Core app models
        if (! $module) {
            $models = [...$models, ...$this->scanDirectory(app_path('Models'), 'App\\Models')];
        }

        // Module models
        $modulePaths = File::directories(base_path('modules'));

        foreach ($modulePaths as $modulePath) {
            $moduleName = Str::after(basename($modulePath), 'module-');

            if ($module && $moduleName !== $module) {
                continue;
            }

            $srcModels = $modulePath.'/src/Models';

            if (File::isDirectory($srcModels)) {
                $namespace = 'Cogneiss\\Module'.Str::studly($moduleName).'\\Models';
                $models = [...$models, ...$this->scanDirectory($srcModels, $namespace)];
            }
        }

        if ($models === []) {
            return 'No models found.';
        }

        $output = "# Models\n\n";

        foreach ($models as $model) {
            $output .= "- **{$model['class']}** (table: `{$model['table']}`)\n";
        }

        return $output;
    }

    /**
     * @return array<int, array{class: string, table: string}>
     */
    private function scanDirectory(string $directory, string $namespace): array
    {
        $models = [];

        if (! File::isDirectory($directory)) {
            return $models;
        }

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $namespace.'\\'.Str::before($file->getFilename(), '.php');

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);

                if ($reflection->isAbstract() || ! $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) {
                    continue;
                }

                $instance = $reflection->newInstanceWithoutConstructor();
                $models[] = [
                    'class' => $className,
                    'table' => $instance->getTable(),
                ];
            } catch (Throwable) {
                continue;
            }
        }

        return $models;
    }
}
