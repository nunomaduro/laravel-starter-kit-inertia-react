<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class SeedsMetricsCommand extends Command
{
    protected $signature = 'seeds:metrics
                            {--file= : Specific metrics file to display}
                            {--latest : Show latest metrics file}';

    protected $description = 'Display seeding metrics from last run';

    public function handle(): int
    {
        $metricsPath = storage_path('logs');
        $specificFile = $this->option('file');
        $latest = $this->option('latest');

        if ($specificFile !== null) {
            $file = $specificFile;
        } elseif ($latest) {
            $files = glob($metricsPath.'/seeding_metrics_*.json');

            if ($files === false) {
                $files = [];
            }

            rsort($files);

            if ($files === []) {
                $this->error('No metrics files found.');

                return self::FAILURE;
            }

            $file = $files[0];
        } else {
            $this->error('Specify --file or --latest');

            return self::FAILURE;
        }

        if (! File::exists($file)) {
            $this->error('Metrics file not found: '.$file);

            return self::FAILURE;
        }

        $data = json_decode(File::get($file), true);

        if ($data === null) {
            $this->error('Invalid metrics file.');

            return self::FAILURE;
        }

        $this->info('Seeding Metrics');
        $this->newLine();

        $summary = $data['summary'] ?? [];
        $timestamp = $data['timestamp'] ?? 'Unknown';
        $seedersRun = $summary['seeders_run'] ?? 0;
        $totalDuration = $summary['total_duration'] ?? 0;
        $totalRecords = $summary['total_records'] ?? 0;
        $totalWarnings = $summary['total_warnings'] ?? 0;
        $totalErrors = $summary['total_errors'] ?? 0;

        $this->line('Timestamp: '.$timestamp);
        $this->line('Seeders Run: '.$seedersRun);
        $this->line(sprintf('Total Duration: %ss', $totalDuration));
        $this->line('Total Records: '.$totalRecords);
        $this->line('Warnings: '.$totalWarnings);
        $this->line('Errors: '.$totalErrors);

        $this->newLine();
        $this->info('Per-Seeder Details:');

        $seeders = $data['seeders'] ?? [];

        foreach ($seeders as $seederName => $metrics) {
            $this->newLine();
            $this->line(sprintf('  %s:', $seederName));
            $this->line('    Duration: '.($metrics['duration'] ?? 0).'s');

            $records = $metrics['records_created'] ?? [];

            if (! empty($records)) {
                $this->line('    Records:');

                foreach ($records as $model => $count) {
                    $this->line(sprintf('      - %s: %s', $model, $count));
                }
            }

            $warnings = $metrics['warnings'] ?? [];

            if (! empty($warnings)) {
                $this->warn('    Warnings:');

                foreach ($warnings as $warning) {
                    $this->line('      - '.$warning);
                }
            }

            $errors = $metrics['errors'] ?? [];

            if (! empty($errors)) {
                $this->error('    Errors:');

                foreach ($errors as $error) {
                    $this->line('      - '.$error);
                }
            }
        }

        return self::SUCCESS;
    }
}
