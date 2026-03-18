<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Human-readable table of all DB-backed settings.
 *
 * Encrypted values are masked as "[encrypted]".
 * Use --group to narrow to a specific settings group.
 *
 * Usage:
 *   php artisan settings:list
 *   php artisan settings:list --group=mail
 *   php artisan settings:list --show-encrypted    # reveal encrypted values (careful!)
 */
final class SettingsListCommand extends Command
{
    /** Known encrypted property names to mask */
    private const array ENCRYPTED_KEYS = [
        'smtp_password', 'smtp_username',
        'openai_api_key', 'anthropic_api_key', 'gemini_api_key',
        'openrouter_api_key', 'groq_api_key', 'xai_api_key',
        'deepseek_api_key', 'mistral_api_key',
        'google_client_secret', 'github_client_secret',
        's3_secret',
        'reverb_app_secret',
        'sentry_dsn',
        'slack_webhook_url',
        'stripe_secret', 'stripe_webhook_secret',
        'paddle_secret', 'lemon_squeezy_api_key', 'lemon_squeezy_signing_secret',
    ];

    protected $signature = 'settings:list
                            {--group= : Filter by settings group (e.g. mail, app, auth, prism)}
                            {--show-encrypted : Show encrypted values in plain text (use with caution)}';

    protected $description = 'List all DB-backed settings in a human-readable table';

    public function handle(): int
    {
        $groupFilter = $this->option('group');
        $showEncrypted = (bool) $this->option('show-encrypted');

        $query = DB::table('settings');

        if ($groupFilter !== null) {
            $query->where('group', $groupFilter);
        }

        $rows = $query->orderBy('group')->orderBy('name')->get();

        if ($rows->isEmpty()) {
            $this->warn($groupFilter
                ? sprintf('No settings found for group "%s".', $groupFilter)
                : 'No settings found. Run php artisan app:install first.'
            );

            return self::SUCCESS;
        }

        /** @var array<string, array<int, array{string, string, string}>> $grouped */
        $grouped = [];

        foreach ($rows as $row) {
            $value = $row->payload ?? 'null';
            $decoded = json_decode($value, true);

            $display = match (true) {
                $decoded === null && $value === 'null' => '<fg=gray>null</>',
                is_bool($decoded) => $decoded ? '<fg=green>true</>' : '<fg=yellow>false</>',
                is_string($decoded) && $decoded === '' => '<fg=gray>(empty)</>',
                is_array($decoded) => '<fg=cyan>['.implode(', ', array_keys($decoded)).']</>',
                is_string($decoded) && ! $showEncrypted && $this->isEncryptedKey($row->name) => '<fg=yellow>[encrypted]</>',
                default => is_string($decoded) ? $decoded : $value,
            };

            $grouped[$row->group][] = [$row->name, $display, $row->locked ? '<fg=red>locked</>' : ''];
        }

        foreach ($grouped as $group => $settings) {
            $this->components->twoColumnDetail(sprintf('<fg=blue;options=bold>%s</>', $group), '<fg=gray>'.count($settings).' settings</>');

            foreach ($settings as [$name, $display, $locked]) {
                $label = sprintf('  <fg=gray>%s</>', $name);
                $this->components->twoColumnDetail($label, $display.($locked ? '  '.$locked : ''));
            }

            $this->newLine();
        }

        $total = $rows->count();
        $this->line(sprintf('<fg=gray>  Total: %d settings', $total).($groupFilter ? sprintf(' in group "%s"', $groupFilter) : '').'</>');

        return self::SUCCESS;
    }

    private function isEncryptedKey(string $name): bool
    {
        return array_any(self::ENCRYPTED_KEYS, fn ($key): bool => str_contains($name, (string) $key));
    }
}
