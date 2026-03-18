<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Providers\SettingsOverlayServiceProvider;
use App\Settings\SetupWizardSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * Upgrades an existing installation:
 *   - Runs pending database migrations
 *   - Re-runs idempotent essential seeders (roles, permissions, mail templates, gamification)
 *   - Clears and re-warms config, route, and view caches
 *   - Re-applies the settings overlay
 *
 * Safe to run after any `composer update` or `git pull` on a live install.
 */
final class AppUpgradeCommand extends Command
{
    protected $signature = 'app:upgrade
                            {--force : Skip the confirmation prompt}
                            {--skip-cache : Do not clear and rebuild caches}
                            {--skip-seeders : Do not re-run essential seeders}';

    protected $description = 'Upgrade an existing installation — migrations, seeders, and cache rebuild';

    public function handle(): int
    {
        intro('  Application Upgrade  ');

        $this->checkInstalled();

        if (! $this->option('force') && ! confirm('  Run upgrade? This will run pending migrations and rebuild caches.', default: true)) {
            outro('  Upgrade cancelled.');

            return self::SUCCESS;
        }

        // ── 1. Migrations ──────────────────────────────────────────────────────
        note('Migrations');
        spin(
            fn () => Artisan::call('migrate', ['--force' => true]),
            'Running pending migrations…'
        );
        info('  Migrations up to date');

        // ── 2. Essential seeders ───────────────────────────────────────────────
        if (! $this->option('skip-seeders')) {
            note('Essential Seeders');
            $this->runEssentialSeeders();
        }

        // ── 3. Caches ──────────────────────────────────────────────────────────
        if (! $this->option('skip-cache')) {
            note('Caches');
            $this->rebuildCaches();
        }

        // ── 4. Settings overlay ────────────────────────────────────────────────
        note('Settings');
        spin(fn () => SettingsOverlayServiceProvider::applyOverlay(), 'Re-applying settings overlay…');
        info('  Settings overlay applied');

        outro(implode("\n", [
            '  Upgrade complete!',
            '',
            '  If you updated queue workers, restart them:',
            '    php artisan queue:restart',
            '  Health check:',
            '    php artisan app:health',
        ]));

        return self::SUCCESS;
    }

    private function checkInstalled(): void
    {
        try {
            $wizard = resolve(SetupWizardSettings::class);

            if (! $wizard->setup_completed) {
                warning('  Application has not been fully installed. Run php artisan app:install first.');
            }
        } catch (Throwable) {
            error('  Cannot reach the database. Ensure DB_* credentials are correct in .env.');
            exit(self::FAILURE);
        }
    }

    private function runEssentialSeeders(): void
    {
        $seeders = [
            \Database\Seeders\Essential\RolesAndPermissionsSeeder::class => 'Seeding roles and permissions…',
            \Database\Seeders\Essential\GamificationSeeder::class => 'Seeding gamification data…',
            \Database\Seeders\Essential\MailTemplatesSeeder::class => 'Seeding mail templates…',
            \GeneaLabs\LaravelGovernor\Database\Seeders\LaravelGovernorDatabaseSeeder::class => 'Seeding Governor (entities, roles)…',
        ];

        foreach ($seeders as $class => $label) {
            spin(
                function () use ($class): void {
                    try {
                        Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
                    } catch (Throwable) {
                        // Non-fatal — seeder may not exist or may fail in some environments
                    }
                },
                $label
            );
        }

        info('  Essential seeders complete');
    }

    private function rebuildCaches(): void
    {
        spin(fn () => Artisan::call('config:clear'), 'Clearing config cache…');
        spin(fn () => Artisan::call('route:clear'), 'Clearing route cache…');
        spin(fn () => Artisan::call('view:clear'), 'Clearing view cache…');
        spin(fn () => Artisan::call('cache:clear'), 'Clearing application cache…');
        info('  Caches cleared');

        spin(fn () => Artisan::call('config:cache'), 'Warming config cache…');
        spin(fn () => Artisan::call('route:cache'), 'Warming route cache…');
        spin(fn () => Artisan::call('view:cache'), 'Warming view cache…');
        info('  Caches warmed');
    }
}
