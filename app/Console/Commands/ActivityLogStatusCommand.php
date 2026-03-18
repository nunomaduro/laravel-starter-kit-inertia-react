<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ActivityType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ActivityLogStatusCommand extends Command
{
    protected $signature = 'activitylog:status';

    protected $description = 'Show activity logging status: models with LogsActivity, custom event types, and doc link';

    public function handle(): int
    {
        $this->info('Activity logging status');
        $this->newLine();

        $models = $this->modelsWithLogsActivity();
        if ($models !== []) {
            $this->components->twoColumnDetail('Models with LogsActivity', implode(', ', $models));
        } else {
            $this->components->twoColumnDetail('Models with LogsActivity', '<comment>none</comment>');
        }

        $types = array_map(fn (ActivityType $t): string => $t->value, ActivityType::cases());
        $this->components->twoColumnDetail('Custom event types', implode(', ', $types));

        $this->newLine();
        $this->line('  <comment>View logs:</comment> Filament admin → System → Logs');
        $this->line('  <comment>Docs:</comment> docs/developer/backend/activity-log.md');
        $this->line('  <comment>Add to new model:</comment> php artisan make:model:full ModelName');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function modelsWithLogsActivity(): array
    {
        $path = app_path('Models');
        if (! File::isDirectory($path)) {
            return [];
        }

        $models = [];
        foreach (File::files($path) as $file) {
            $name = $file->getFilenameWithoutExtension();
            $content = File::get($file->getPathname());
            if (str_contains($content, 'LogsActivity') && str_contains($content, 'getActivitylogOptions')) {
                $models[] = $name;
            }
        }

        sort($models);

        return $models;
    }
}
