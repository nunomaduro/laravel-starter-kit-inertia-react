<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AISeederCodeGenerator;
use App\Services\EnhancedRelationshipAnalyzer;
use App\Services\ModelRegistry;
use App\Services\SeedSpecGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class SeedsRegenerateCommand extends Command
{
    protected $signature = 'seeds:regenerate
                            {--check : Check mode - only report what would change}
                            {--model= : Regenerate specific model only}
                            {--force : Force regeneration even if custom code exists}';

    protected $description = 'Regenerate seeder and JSON files from seed specs';

    public function handle(SeedSpecGenerator $generator, ModelRegistry $registry): int
    {
        $checkMode = $this->option('check');
        $specificModel = $this->option('model');
        $force = $this->option('force');

        $models = $specificModel
            ? ['App\Models\\'.$specificModel]
            : $registry->getAllModels();

        if ($models === []) {
            $this->info('No models found.');

            return self::SUCCESS;
        }

        $this->info('Regenerating seeders and JSON from specs...');
        $this->newLine();

        $hasChanges = false;

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);
            $spec = $generator->loadSpec($modelClass);

            if ($spec === null) {
                $this->warn(sprintf('  %s: No spec found (run seeds:spec-sync first)', $modelName));

                continue;
            }

            try {
                // Regenerate JSON file
                $jsonChanges = $this->regenerateJson($modelName, $spec, $checkMode);

                // Regenerate seeder skeleton
                $seederChanges = $this->regenerateSeeder($modelName, $spec, $checkMode, $force);

                if ($jsonChanges || $seederChanges) {
                    $hasChanges = true;
                } else {
                    $this->line(sprintf('  %s: Up to date', $modelName));
                }
            } catch (Exception $e) {
                $this->error(sprintf('  %s: Error - %s', $modelName, $e->getMessage()));
            }
        }

        $this->newLine();

        if ($checkMode && $hasChanges) {
            $this->warn('Files would be regenerated. Run without --check to apply changes.');

            return self::FAILURE;
        }

        $this->info('Regeneration complete.');

        return self::SUCCESS;
    }

    private function regenerateJson(string $modelName, array $spec, bool $checkMode): bool
    {
        $jsonKey = Str::snake(Str::plural($modelName));
        $jsonPath = database_path(sprintf('seeders/data/%s.json', $jsonKey));

        $fields = $spec['fields'] ?? [];
        $existingData = [];

        if (File::exists($jsonPath)) {
            $existingContent = File::get($jsonPath);
            $existingData = json_decode($existingContent, true) ?? [];
        }

        $newData = [
            $jsonKey => [],
        ];

        if (isset($existingData[$jsonKey]) && is_array($existingData[$jsonKey]) && $existingData[$jsonKey] !== []) {
            foreach ($existingData[$jsonKey] as $entry) {
                $updatedEntry = $entry;

                foreach ($fields as $field => $fieldSpec) {
                    if (! isset($updatedEntry[$field]) && $fieldSpec['default'] !== null) {
                        $updatedEntry[$field] = $fieldSpec['default'];
                    }
                }

                $newData[$jsonKey][] = $updatedEntry;
            }
        } else {
            $example = [];

            foreach ($fields as $field => $fieldSpec) {
                if (in_array($field, ['id', 'created_at', 'updated_at'], true)) {
                    continue;
                }

                $example[$field] = $this->generateExampleValue($field, $fieldSpec, $spec['value_hints'] ?? []);
            }

            $newData[$jsonKey][] = $example;
        }

        $newContent = json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $existingContent = File::exists($jsonPath) ? File::get($jsonPath) : '';

        if ($newContent !== $existingContent) {
            if ($checkMode) {
                $this->warn(sprintf('  %s: JSON would be updated', $modelName));
            } else {
                File::put($jsonPath, $newContent);
                $this->info(sprintf('  %s: JSON regenerated', $modelName));
            }

            return true;
        }

        return false;
    }

    private function regenerateSeeder(string $modelName, array $spec, bool $checkMode, bool $force): bool
    {
        $categories = ['essential', 'development', 'production'];
        $seederPath = null;
        $category = null;

        foreach ($categories as $cat) {
            $path = database_path(sprintf('seeders/%s/%sSeeder.php', $cat, $modelName));

            if (File::exists($path)) {
                $seederPath = $path;
                $category = $cat;
                break;
            }
        }

        if ($seederPath === null) {
            return false;
        }

        $content = File::get($seederPath);

        if (Str::contains($content, '// GENERATED START') && Str::contains($content, '// GENERATED END')) {
            $beforeGenerated = Str::before($content, '// GENERATED START');
            $afterGenerated = Str::after($content, '// GENERATED END');
            $generatedCode = $this->generateSeederCode($modelName, $spec, $category);

            $newContent = $beforeGenerated."// GENERATED START\n{$generatedCode}\n    // GENERATED END".$afterGenerated;

            if ($newContent !== $content) {
                if ($checkMode) {
                    $this->warn(sprintf('  %s: Seeder would be updated', $modelName));
                } else {
                    File::put($seederPath, $newContent);
                    $this->info(sprintf('  %s: Seeder regenerated', $modelName));
                }

                return true;
            }
        } elseif ($force) {
            $this->warn(sprintf('  %s: Seeder has no protected regions - skipping (use --force to overwrite)', $modelName));
        }

        return false;
    }

    private function generateSeederCode(string $modelName, array $spec, string $category): string
    {
        $modelClass = 'App\Models\\'.$modelName;
        $enhancedAnalyzer = resolve(EnhancedRelationshipAnalyzer::class);
        $relationships = class_exists($modelClass)
            ? $enhancedAnalyzer->analyzeModel($modelClass)
            : [];

        if (empty($relationships)) {
            $specRelationships = $spec['relationships'] ?? [];
            foreach ($specRelationships as $relName => $relSpec) {
                $relationships[$relName] = [
                    'type' => $relSpec['type'] ?? 'belongsTo',
                    'model' => $relSpec['model'] ?? null,
                    'foreignKey' => null,
                    'localKey' => null,
                    'pivotTable' => null,
                ];
            }
        }

        $aiGenerator = resolve(AISeederCodeGenerator::class);

        return $aiGenerator->generateSeederCode($modelName, $spec, $relationships, $category);
    }

    /**
     * @param  array<string, mixed>  $fieldSpec
     * @param  array<string, mixed>  $valueHints
     */
    private function generateExampleValue(string $field, array $fieldSpec, array $valueHints): mixed
    {
        if (isset($valueHints[$field])) {
            return $valueHints[$field]['example'] ?? null;
        }

        if ($fieldSpec['default'] !== null) {
            return $fieldSpec['default'];
        }

        $type = $fieldSpec['type'] ?? 'string';

        return match ($type) {
            'string' => Str::contains($field, 'email') ? 'example@example.com' : 'Example '.$field,
            'integer', 'bigint' => 1,
            'boolean' => false,
            'datetime', 'timestamp' => '2024-01-01 00:00:00',
            'text' => 'Example text',
            default => null,
        };
    }
}
