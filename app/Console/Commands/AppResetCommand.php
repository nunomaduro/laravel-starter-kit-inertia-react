<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * Hard-resets the application to a blank state for local development.
 *
 * Refuses to run in production unless --production-force is explicitly passed.
 * This is a destructive, irreversible operation.
 */
final class AppResetCommand extends Command
{
    protected $signature = 'app:reset
                            {--force : Skip the confirmation prompt}
                            {--production-force : Allow running in production (dangerous)}
                            {--keep-env : Do not remove DB credentials from .env}';

    protected $description = 'Reset the application to a blank state — drops all tables and clears all caches (development only)';

    public function handle(): int
    {
        intro('  Application Reset  ');

        if (! $this->option('production-force') && app()->environment('production')) {
            error('  Refused to run in production. Pass --production-force if you really mean it.');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->option('production-force')) {
            error('  This command is destructive. It will drop ALL data.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            warning('  This will:');
            $this->line('    • Drop ALL database tables and data');
            $this->line('    • Delete all cached files');
            $this->line('    • Reset setup_completed to false');
            $this->line('    • Remove the install resume file');
            $this->newLine();

            if (! confirm('  Are you absolutely sure?', default: false)) {
                outro('  Reset cancelled. Nothing changed.');

                return self::SUCCESS;
            }
        }

        // ── 1. Drop all tables ─────────────────────────────────────────────
        spin(fn () => Artisan::call('migrate:fresh', ['--force' => true]), 'Dropping all tables and re-running migrations…');
        info('  Database reset complete');

        // ── 2. Clear all caches ────────────────────────────────────────────
        spin(function (): void {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('event:clear');
        }, 'Clearing all caches…');
        info('  Caches cleared');

        // ── 3. Remove install progress file ───────────────────────────────
        $progressFile = storage_path('app/.install-progress.json');

        if (file_exists($progressFile)) {
            unlink($progressFile);
        }

        info('  Install progress cleared');

        outro(implode("\n", [
            '  Reset complete.',
            '',
            '  Run the installer to set up again:',
            '    php artisan app:install',
            '  Or visit /install in the browser.',
        ]));

        return self::SUCCESS;
    }
}
