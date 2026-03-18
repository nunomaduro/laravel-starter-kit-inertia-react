<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

final class FeaturesResetToDefaultsCommand extends Command
{
    protected $signature = 'features:reset-to-defaults
                            {--force : Skip confirmation}';

    protected $description = 'Clear stored feature state and segments so all features use class defaults (on). Run after setup so gamification and others are visible for everyone; adjust later in Filament → Manage Features & Segments.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will purge all Pennant feature storage and clear all feature segments. Every feature will re-resolve to its class default (all on). Continue?')) {
            return self::FAILURE;
        }

        Feature::purge();
        Feature::flushCache();

        $segmentTable = 'feature_segments';
        if (DB::getSchemaBuilder()->hasTable($segmentTable)) {
            $deleted = DB::table($segmentTable)->delete();
            $this->info(sprintf('Purged Pennant feature storage and removed %d feature segment(s).', $deleted));
        } else {
            $this->info('Purged Pennant feature storage. No feature_segments table found.');
        }

        $this->newLine();
        $this->line('All features will now resolve to their class default (on) until you create segments in Filament → Settings → Manage Features & Segments.');

        return self::SUCCESS;
    }
}
