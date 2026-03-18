<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModelRegistry;
use App\Services\SeedSpecGenerator;
use Illuminate\Console\Command;

final class ModelsAuditCommand extends Command
{
    protected $signature = 'models:audit
                            {--generate : Auto-generate missing factories and seeders}
                            {--category=development : Default category for generated seeders}
                            {--check-specs : Also check for missing seed specs}
                            {--fail-on-missing : Fail if any components are missing}';

    protected $description = 'Audit models for missing factories, seeders, and seed specs';

    public function handle(ModelRegistry $registry, SeedSpecGenerator $specGenerator): int
    {
        $report = $registry->getAuditReport();
        $checkSpecs = $this->option('check-specs');
        $failOnMissing = $this->option('fail-on-missing');

        if ($report === []) {
            $this->info('No models found.');

            return self::SUCCESS;
        }

        $this->info('Model Audit Report');
        $this->newLine();

        $headers = ['Model', 'Factory', 'Seeder', 'Category'];
        $rows = [];
        $missing = [];

        if ($checkSpecs) {
            $headers[] = 'Spec';
        }

        foreach ($report as $modelName => $data) {
            $modelClass = 'App\Models\\'.$modelName;
            $factoryStatus = $data['factory'] ? '✓' : '✗';
            $seederStatus = $data['seeder']['exists'] ? '✓' : '✗';
            $category = $data['seeder']['category'] ?? '-';

            $row = [$modelName, $factoryStatus, $seederStatus, $category];

            if ($checkSpecs) {
                $spec = $specGenerator->loadSpec($modelClass);
                $specStatus = $spec !== null ? '✓' : '✗';
                $row[] = $specStatus;
            }

            $rows[] = $row;

            $hasMissing = ! $data['factory'] || ! $data['seeder']['exists'];

            if ($checkSpecs) {
                $hasMissing = $hasMissing || ($specGenerator->loadSpec($modelClass) === null);
            }

            if ($hasMissing) {
                $missing[] = [
                    'model' => $modelName,
                    'missing_factory' => ! $data['factory'],
                    'missing_seeder' => ! $data['seeder']['exists'],
                    'missing_spec' => $checkSpecs && $specGenerator->loadSpec($modelClass) === null,
                ];
            }
        }

        $this->table($headers, $rows);

        if ($missing !== []) {
            $this->newLine();
            $this->warn(sprintf('Found %d model(s) with missing components:', count($missing)));

            foreach ($missing as $item) {
                $issues = [];
                if ($item['missing_factory']) {
                    $issues[] = 'factory';
                }

                if ($item['missing_seeder']) {
                    $issues[] = 'seeder';
                }

                if (isset($item['missing_spec']) && $item['missing_spec']) {
                    $issues[] = 'seed spec';
                }

                $this->line(sprintf('  - %s: missing ', $item['model']).implode(', ', $issues));
            }

            if ($this->option('generate')) {
                $this->newLine();
                $this->info('Generating missing components...');

                foreach ($missing as $item) {
                    $category = $this->option('category');

                    if ($item['missing_factory']) {
                        $this->call('make:factory', [
                            'name' => $item['model'].'Factory',
                            '--model' => $item['model'],
                            '--no-interaction' => true,
                        ]);
                    }

                    if ($item['missing_seeder']) {
                        $this->call('make:model:full', [
                            'name' => $item['model'],
                            '--category' => $category,
                            '--no-interaction' => true,
                        ]);
                    }

                    if (isset($item['missing_spec']) && $item['missing_spec']) {
                        $this->call('seeds:spec-sync', [
                            '--model' => $item['model'],
                            '--no-interaction' => true,
                        ]);
                    }
                }

                $this->info('Generation complete!');
            } else {
                $this->newLine();
                $this->info('Run with --generate to auto-generate missing components.');
            }

            if ($failOnMissing) {
                return self::FAILURE;
            }
        } else {
            $this->newLine();
            $this->info('All models have required components! ✓');
        }

        return self::SUCCESS;
    }
}
