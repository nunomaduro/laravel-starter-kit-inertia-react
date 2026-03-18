<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * Imports settings from a JSON snapshot produced by settings:export.
 *
 * By default, existing values are overwritten. Use --no-overwrite to
 * skip settings that already exist in the database.
 *
 * Usage:
 *   php artisan settings:import settings.json
 *   php artisan settings:import settings.json --dry-run
 *   php artisan settings:import settings.json --no-overwrite
 *   php artisan settings:import settings.json --group=app --group=mail
 */
final class SettingsImportCommand extends Command
{
    protected $signature = 'settings:import
                            {file : Path to the JSON snapshot file}
                            {--dry-run : Preview changes without writing to the database}
                            {--no-overwrite : Skip settings that already exist}
                            {--force : Skip the confirmation prompt}
                            {--group=* : Import only specific group(s)}';

    protected $description = 'Import database settings from a JSON snapshot';

    public function handle(): int
    {
        $file = (string) $this->argument('file');

        if (! file_exists($file)) {
            error('  File not found: '.$file);

            return self::FAILURE;
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        if (! is_array($decoded) || ! isset($decoded['settings'])) {
            error('  Invalid snapshot — missing "settings" key. Was this exported with settings:export?');

            return self::FAILURE;
        }

        /** @var array<array{group: string, name: string, payload: string, locked?: bool}> $settings */
        $settings = $decoded['settings'];

        /** @var array<string> $groups */
        $groups = (array) $this->option('group');

        if ($groups !== []) {
            $settings = array_filter($settings, fn (array $s): bool => in_array($s['group'], $groups));
            $settings = array_values($settings);
        }

        $isDryRun = (bool) $this->option('dry-run');
        $noOverwrite = (bool) $this->option('no-overwrite');
        $exportedAt = $decoded['exported_at'] ?? 'unknown';

        intro($isDryRun ? '  Settings Import (dry run)  ' : '  Settings Import  ');
        $this->line('  Source   : '.$file);
        $this->line('  Exported : '.$exportedAt);
        $this->line('  Records  : '.count($settings));
        $this->newLine();

        if (! $isDryRun && ! $this->option('force') && ! confirm('  Import '.count($settings).' settings? Existing values will be '.($noOverwrite ? 'preserved.' : 'overwritten.'), default: true)) {
            outro('  Import cancelled.');

            return self::SUCCESS;
        }

        $imported = 0;
        $skipped = 0;
        $failed = 0;

        $process = function () use ($settings, $noOverwrite, $isDryRun, &$imported, &$skipped, &$failed): void {
            foreach ($settings as $row) {
                try {
                    $exists = DB::table('settings')
                        ->where('group', $row['group'])
                        ->where('name', $row['name'])
                        ->exists();

                    if ($exists && $noOverwrite) {
                        $skipped++;

                        continue;
                    }

                    if (! $isDryRun) {
                        DB::table('settings')->updateOrInsert(
                            ['group' => $row['group'], 'name' => $row['name']],
                            ['payload' => $row['payload'], 'locked' => $row['locked'] ?? false]
                        );
                    }

                    $imported++;
                } catch (Throwable $e) {
                    warning(sprintf('  Failed: %s.%s — %s', $row['group'], $row['name'], $e->getMessage()));
                    $failed++;
                }
            }
        };

        if ($isDryRun) {
            $process();
        } else {
            spin($process, 'Importing settings…');
        }

        $this->newLine();

        if ($isDryRun) {
            info(sprintf('  Dry run — would import: %d, skip: %d, fail: %d', $imported, $skipped, $failed));
            info('  Run without --dry-run to apply.');
        } else {
            info('  Imported: '.$imported);

            if ($skipped > 0) {
                info('  Skipped (already exist): '.$skipped);
            }

            if ($failed > 0) {
                warning('  Failed: '.$failed);
            }

            outro('  Import complete. Run php artisan config:clear to apply changes.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
