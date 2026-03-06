<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

final class SchemaWatcher
{
    /**
     * Detect changes in migrations since a given commit/branch.
     *
     * @return array<string>
     */
    public function detectMigrationChanges(?string $since = null): array
    {
        return $this->detectChanges(database_path('migrations'), 'database/migrations/', $since);
    }

    /**
     * Detect changes in model files since a given commit/branch.
     *
     * @return array<string>
     */
    public function detectModelChanges(?string $since = null): array
    {
        return $this->detectChanges(app_path('Models'), 'app/Models/', $since);
    }

    /**
     * Extract model name from migration file.
     */
    public function extractModelFromMigration(string $migrationPath): ?string
    {
        $content = File::get($migrationPath);
        $filename = basename($migrationPath);

        // Try to extract table name from migration
        if (preg_match('/create\([\'"](\w+)[\'"]/', $content, $matches)) {
            $tableName = $matches[1];

            return Str::studly(Str::singular($tableName));
        }

        // Fallback: try to extract from filename
        if (preg_match('/create_(\w+)_table/', $filename, $matches)) {
            $tableName = $matches[1];

            return Str::studly(Str::singular($tableName));
        }

        return null;
    }

    /**
     * Get models affected by migration changes.
     *
     * @return array<string>
     */
    public function getAffectedModels(?string $since = null): array
    {
        $migrations = $this->detectMigrationChanges($since);
        $models = [];

        foreach ($migrations as $migration) {
            $model = $this->extractModelFromMigration($migration);

            if ($model !== null) {
                $modelClass = 'App\Models\\'.$model;

                if (class_exists($modelClass)) {
                    $models[] = $modelClass;
                }
            }
        }

        // Also check direct model changes
        $modelFiles = $this->detectModelChanges($since);

        foreach ($modelFiles as $modelFile) {
            $className = $this->getClassNameFromFile($modelFile);

            if ($className !== null && class_exists($className)) {
                $models[] = $className;
            }
        }

        return array_unique($models);
    }

    /**
     * Detect changed PHP files in a directory, optionally since a git ref.
     *
     * @return array<string>
     */
    private function detectChanges(string $directory, string $gitPath, ?string $since): array
    {
        if (! File::isDirectory($directory)) {
            return [];
        }

        if ($since !== null) {
            return $this->getChangedPhpFilesFromGit($gitPath, $since);
        }

        return collect(File::allFiles($directory))
            ->map(fn ($file): string => $file->getPathname())
            ->all();
    }

    /**
     * Get changed PHP files from git diff.
     *
     * @return array<string>
     */
    private function getChangedPhpFilesFromGit(string $gitPath, string $since): array
    {
        $result = Process::run(['git', 'diff', '--name-only', $since, '--', $gitPath]);
        $output = $result->output();

        if ($output === '') {
            return [];
        }

        return array_values(
            array_filter(
                array_map(trim(...), explode("\n", $output)),
                fn (string $file): bool => $file !== '' && Str::endsWith($file, '.php'),
            )
        );
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

        return sprintf('%s\%s', $namespace, $className);
    }
}
