<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModelRegistry;
use App\Services\SeedSpecGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class SeedsReplicaCommand extends Command
{
    protected $signature = 'seeds:replica
                            {--profile= : Path to production profile JSON}
                            {--count=1000 : Number of records to generate}
                            {--force : Force operation in non-dev environments}';

    protected $description = 'Generate synthetic replica data based on production profiles';

    public function handle(SeedSpecGenerator $specGenerator, ModelRegistry $registry): int
    {
        $profilePath = $this->option('profile') ?? database_path('seeders/profiles/production.json');
        $count = (int) $this->option('count');
        $force = $this->option('force');

        if (! app()->environment('local', 'testing') && ! $force) {
            $this->error('Replica generation only allowed in local/testing. Use --force to override.');

            return self::FAILURE;
        }

        if (! File::exists($profilePath)) {
            $this->error('Profile file not found: '.$profilePath);
            $this->info('Run seeds:profile first to generate profiles.');

            return self::FAILURE;
        }

        $this->info(sprintf('Generating synthetic replica data (count: %d)...', $count));
        $this->newLine();

        $profiles = json_decode(File::get($profilePath), true);

        if ($profiles === null) {
            $this->error('Invalid profile file.');

            return self::FAILURE;
        }

        $models = $registry->getAllModels();

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);

            if (! isset($profiles[$modelName])) {
                continue;
            }

            $profile = $profiles[$modelName];
            $spec = $specGenerator->loadSpec($modelClass);

            if ($spec === null) {
                $this->warn(sprintf('  %s: No spec found', $modelName));

                continue;
            }

            $this->line(sprintf('  %s: Generating %d records...', $modelName, $count));

            try {
                $factory = $modelClass::factory();
                $factory->count($count)->create();
                $this->info(sprintf('  %s: Generated', $modelName));
            } catch (Exception $e) {
                $this->error(sprintf('  %s: Error - %s', $modelName, $e->getMessage()));
            }
        }

        $this->newLine();
        $this->info('Replica generation complete.');

        return self::SUCCESS;
    }
}
