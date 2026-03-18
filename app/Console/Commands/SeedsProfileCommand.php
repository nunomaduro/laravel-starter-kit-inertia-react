<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModelRegistry;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class SeedsProfileCommand extends Command
{
    protected $signature = 'seeds:profile
                            {--connection= : Database connection to profile}
                            {--output= : Output file path (default: database/seeders/profiles/production.json)}
                            {--safe : Only profile if connection is not production}';

    protected $description = 'Profile production/staging database to generate seed profiles (read-only)';

    public function handle(ModelRegistry $registry): int
    {
        $connection = $this->option('connection') ?? config('database.default');
        $output = $this->option('output') ?? database_path('seeders/profiles/production.json');
        $safe = $this->option('safe');

        if ($safe && app()->environment('production')) {
            $this->error('Cannot profile production database in safe mode.');

            return self::FAILURE;
        }

        $this->info('Profiling database connection: '.$connection);
        $this->warn('This is a READ-ONLY operation. No data will be modified.');
        $this->newLine();

        $models = $registry->getAllModels();
        $profiles = [];

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);

            try {
                $model = new $modelClass;
                $table = $model->getTable();

                if (! Schema::connection($connection)->hasTable($table)) {
                    continue;
                }

                $this->line(sprintf('Profiling %s...', $modelName));

                $profile = $this->profileModel($modelClass, $connection);
                $profiles[$modelName] = $profile;
            } catch (Exception $e) {
                $this->warn(sprintf('  %s: Error - %s', $modelName, $e->getMessage()));
            }
        }

        // Save profiles
        $outputDir = dirname($output);

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        file_put_contents($output, json_encode($profiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->info('Profiles saved to: '.$output);

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function profileModel(string $modelClass, string $connection): array
    {
        $model = new $modelClass;
        $table = $model->getTable();

        $totalCount = DB::connection($connection)->table($table)->count();

        if ($totalCount === 0) {
            return [
                'total_count' => 0,
                'cardinalities' => [],
                'distributions' => [],
            ];
        }

        return [
            'total_count' => $totalCount,
            'cardinalities' => $this->calculateCardinalities(),
            'distributions' => $this->calculateDistributions($table, $connection),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateCardinalities(): array
    {
        // This would analyze relationships and calculate avg/min/max children per parent
        // For now, return empty - can be enhanced based on actual relationships
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateDistributions(string $table, string $connection): array
    {
        $distributions = [];
        $columns = Schema::connection($connection)->getColumnListing($table);

        foreach ($columns as $column) {
            $type = Schema::connection($connection)->getColumnType($table, $column);

            if ($type === 'string' || $type === 'text') {
                $lengths = DB::connection($connection)
                    ->table($table)
                    ->selectRaw(sprintf('LENGTH(%s) as len', $column))
                    ->whereNotNull($column)
                    ->limit(1000)
                    ->pluck('len')
                    ->toArray();

                if (! empty($lengths)) {
                    $distributions[$column] = [
                        'type' => 'string',
                        'avg_length' => round(array_sum($lengths) / count($lengths), 2),
                        'min_length' => min($lengths),
                        'max_length' => max($lengths),
                    ];
                }
            } elseif ($type === 'integer' || $type === 'bigint') {
                $stats = DB::connection($connection)
                    ->table($table)
                    ->selectRaw(sprintf('MIN(%s) as min_val, MAX(%s) as max_val, AVG(%s) as avg_val', $column, $column, $column))
                    ->whereNotNull($column)
                    ->first();

                if ($stats && $stats->min_val !== null) {
                    $distributions[$column] = [
                        'type' => 'numeric',
                        'min' => $stats->min_val,
                        'max' => $stats->max_val,
                        'avg' => round((float) $stats->avg_val, 2),
                    ];
                }
            } elseif ($type === 'boolean') {
                $trueCount = DB::connection($connection)
                    ->table($table)
                    ->where($column, true)
                    ->count();

                $falseCount = DB::connection($connection)
                    ->table($table)
                    ->where($column, false)
                    ->count();

                $total = $trueCount + $falseCount;

                if ($total > 0) {
                    $distributions[$column] = [
                        'type' => 'boolean',
                        'true_percentage' => round(($trueCount / $total) * 100, 2),
                        'false_percentage' => round(($falseCount / $total) * 100, 2),
                    ];
                }
            }
        }

        return $distributions;
    }
}
