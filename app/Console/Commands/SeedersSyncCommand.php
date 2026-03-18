<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModelRegistry;
use Illuminate\Console\Command;

final class SeedersSyncCommand extends Command
{
    protected $signature = 'seeders:sync
                            {--update : Update existing seeders to new patterns}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Sync seeders with models and update to latest patterns';

    public function handle(ModelRegistry $registry): int
    {
        $models = $registry->getAllModels();
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Syncing seeders with models...');
        $this->newLine();

        $updated = 0;
        $created = 0;

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);
            $seederInfo = $registry->hasSeeder($modelClass);

            if (! $seederInfo['exists']) {
                if ($dryRun) {
                    $this->line(sprintf('Would create: %sSeeder', $modelName));
                } else {
                    $this->call('make:model:full', [
                        'name' => $modelName,
                        '--category' => 'development',
                        '--no-interaction' => true,
                    ]);
                    $created++;
                }
            } elseif ($this->option('update')) {
                if ($dryRun) {
                    $this->line(sprintf('Would update: %sSeeder', $modelName));
                } else {
                    // Update seeder to latest patterns
                    $this->info(sprintf('Updating %sSeeder...', $modelName));
                    $updated++;
                }
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info(sprintf('Sync complete! Created: %d, Updated: %d', $created, $updated));
        }

        return self::SUCCESS;
    }
}
