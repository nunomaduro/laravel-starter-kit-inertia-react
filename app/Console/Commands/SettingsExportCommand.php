<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;

/**
 * Exports all settings from the database to a JSON file.
 *
 * Encrypted values are exported in their encrypted form — the APP_KEY
 * must be the same on the destination server for them to decrypt correctly.
 *
 * Usage:
 *   php artisan settings:export                         # prints JSON to stdout
 *   php artisan settings:export --output=settings.json  # writes to file
 *   php artisan settings:export --pretty                # pretty-printed JSON
 */
final class SettingsExportCommand extends Command
{
    protected $signature = 'settings:export
                            {--output= : Path to write the JSON file (defaults to stdout)}
                            {--pretty : Pretty-print the JSON output}
                            {--group=* : Export only specific group(s), e.g. --group=app --group=mail}';

    protected $description = 'Export all database settings to a JSON snapshot';

    public function handle(): int
    {
        $query = DB::table('settings')->orderBy('group')->orderBy('name');

        /** @var array<string> $groups */
        $groups = (array) $this->option('group');

        if ($groups !== []) {
            $query->whereIn('group', $groups);
        }

        $rows = $query->get(['group', 'name', 'payload', 'locked'])->toArray();

        if ($rows === []) {
            $this->warn('No settings found.');

            return self::SUCCESS;
        }

        $data = [
            'exported_at' => now()->toIso8601String(),
            'count' => count($rows),
            'note' => 'Encrypted values require the same APP_KEY on the destination server.',
            'settings' => array_map(fn (object $row): array => [
                'group' => $row->group,
                'name' => $row->name,
                'payload' => $row->payload,
                'locked' => $row->locked ?? false,
            ], $rows),
        ];

        $flags = $this->option('pretty') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES;
        $json = (string) json_encode($data, $flags);

        $output = $this->option('output');

        if ($output) {
            intro(sprintf('  Exporting %d settings…', $data['count']));
            file_put_contents($output, $json);
            info('  Written to: '.$output);
            outro(sprintf('  Export complete — %d settings exported.', $data['count']));
        } else {
            $this->line($json);
        }

        return self::SUCCESS;
    }
}
