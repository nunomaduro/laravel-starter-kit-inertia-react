<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModelRegistry;
use App\Services\SeedSpecGenerator;
use Exception;
use Illuminate\Console\Command;

final class SeedsSpecSyncCommand extends Command
{
    protected $signature = 'seeds:spec-sync
                            {--check : Check mode - only report differences without updating}
                            {--model= : Sync specific model only}
                            {--force : Force update even if needs approval items exist}';

    protected $description = 'Sync seed specs with current model and migration state';

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

        $this->info('Syncing seed specs...');
        $this->newLine();

        $hasChanges = false;
        $hasApprovalNeeded = false;

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);

            try {
                $newSpec = $generator->generateSpec($modelClass);
                $oldSpec = $generator->loadSpec($modelClass);

                if ($oldSpec === null) {
                    if ($checkMode) {
                        $this->warn(sprintf('  %s: Missing spec (would be created)', $modelName));
                        $hasChanges = true;
                    } else {
                        $generator->saveSpec($modelClass, $newSpec);
                        $this->info(sprintf('  %s: Created new spec', $modelName));
                    }
                } else {
                    $diff = $generator->diffSpecs($oldSpec, $newSpec);

                    if (! empty($diff['added_fields']) || ! empty($diff['removed_fields']) || ! empty($diff['changed_fields']) || ! empty($diff['added_relationships']) || ! empty($diff['removed_relationships'])) {
                        $hasChanges = true;

                        if (! empty($diff['needs_approval'])) {
                            $hasApprovalNeeded = true;
                            $this->warn(sprintf('  %s: Changes require approval:', $modelName));
                            foreach ($diff['needs_approval'] as $item) {
                                $this->line('    - '.$item);
                            }
                        }

                        if ($checkMode) {
                            $this->warn(sprintf('  %s: Spec out of sync (would be updated)', $modelName));
                            if (! empty($diff['added_fields'])) {
                                $this->line('    Added fields: '.implode(', ', array_keys($diff['added_fields'])));
                            }

                            if (! empty($diff['removed_fields'])) {
                                $this->line('    Removed fields: '.implode(', ', array_keys($diff['removed_fields'])));
                            }

                            if (! empty($diff['added_relationships'])) {
                                $this->line('    Added relationships: '.implode(', ', array_keys($diff['added_relationships'])));
                            }
                        } elseif ($hasApprovalNeeded && ! $force) {
                            $this->error(sprintf('  %s: Cannot update - approval needed. Use --force to override.', $modelName));
                        } else {
                            $updatedSpec = $oldSpec;
                            $updatedSpec['fields'] = $newSpec['fields'];
                            $updatedSpec['relationships'] = $newSpec['relationships'];
                            $updatedSpec['value_hints'] = array_merge($oldSpec['value_hints'] ?? [], $newSpec['value_hints']);

                            $generator->saveSpec($modelClass, $updatedSpec);
                            $this->info(sprintf('  %s: Updated spec', $modelName));
                        }
                    } else {
                        $this->line(sprintf('  %s: Up to date', $modelName));
                    }
                }
            } catch (Exception $e) {
                $this->error(sprintf('  %s: Error - %s', $modelName, $e->getMessage()));
            }
        }

        $this->newLine();

        if ($checkMode) {
            if ($hasChanges) {
                $this->warn('Specs are out of sync. Run without --check to update.');
                if ($hasApprovalNeeded) {
                    $this->warn('Some changes require approval. Review and use --force if needed.');
                }

                return self::FAILURE;
            }

            $this->info('All specs are in sync.');

            return self::SUCCESS;

        }

        $this->info('Spec sync complete.');

        return self::SUCCESS;
    }
}
