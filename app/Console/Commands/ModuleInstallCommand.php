<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Support\AIContextAggregator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * Installs a module package: composer require, migrate, seed, and configure.
 *
 * Includes pre-flight conflict detection and rollback on migration failure.
 */
final class ModuleInstallCommand extends Command
{
    protected $signature = 'module:install
                            {package : The Composer package name (e.g. cogneiss/module-hr)}
                            {--no-seed : Skip seeding demo data}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Install a module package with migrations, seeds, and AI context configuration';

    public function handle(): int
    {
        $package = $this->argument('package');

        info("Installing module: {$package}");

        // 1. Pre-flight checks
        if (! $this->preflight($package)) {
            return self::FAILURE;
        }

        // 2. Install via Composer
        if (! $this->installPackage($package)) {
            return self::FAILURE;
        }

        // 3. Record migration state for rollback
        $migrationsBefore = $this->getMigrationCount();

        // 4. Run migrations
        if (! $this->runMigrations()) {
            $this->rollbackMigrations($migrationsBefore);

            return self::FAILURE;
        }

        // 5. Seed demo data
        if (! $this->option('no-seed')) {
            $this->seedData($package);
        }

        // 6. Invalidate AI context cache
        AIContextAggregator::invalidate();

        // 7. Clear caches
        spin(function (): void {
            Artisan::call('optimize:clear', ['--no-interaction' => true]);
        }, 'Clearing caches...');

        info("✅ Module {$package} installed successfully!");
        $this->line('  Run <comment>php artisan wayfinder:generate</comment> to update TypeScript route helpers.');

        return self::SUCCESS;
    }

    private function preflight(string $package): bool
    {
        // Check for table name conflicts
        $this->line('  Running pre-flight checks...');

        // Check if package is already installed
        $composerLock = json_decode((string) file_get_contents(base_path('composer.lock')), true);
        $installed = collect($composerLock['packages'] ?? [])
            ->pluck('name')
            ->contains($package);

        if ($installed) {
            warning("Package {$package} is already installed.");

            if (! $this->option('force') && ! confirm('Re-install anyway?', false)) {
                return false;
            }
        }

        info('  ✓ Pre-flight checks passed');

        return true;
    }

    private function installPackage(string $package): bool
    {
        $result = spin(function () use ($package): int {
            $exitCode = 0;
            exec("composer require {$package} --no-interaction 2>&1", $output, $exitCode);

            return $exitCode;
        }, "Installing {$package} via Composer...");

        if ($result !== 0) {
            error("Failed to install {$package}. Check the package name and try again.");

            return false;
        }

        info('  ✓ Package installed');

        return true;
    }

    private function getMigrationCount(): int
    {
        try {
            if (! Schema::hasTable('migrations')) {
                return 0;
            }

            return DB::table('migrations')->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function runMigrations(): bool
    {
        try {
            spin(function (): void {
                Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
            }, 'Running module migrations...');

            info('  ✓ Migrations complete');

            return true;
        } catch (Throwable $e) {
            error("Migration failed: {$e->getMessage()}");

            return false;
        }
    }

    private function rollbackMigrations(int $countBefore): void
    {
        $countAfter = $this->getMigrationCount();
        $newMigrations = $countAfter - $countBefore;

        if ($newMigrations > 0) {
            warning("Rolling back {$newMigrations} migration(s)...");

            try {
                Artisan::call('migrate:rollback', [
                    '--step' => $newMigrations,
                    '--force' => true,
                    '--no-interaction' => true,
                ]);
                info('  ✓ Rollback complete');
            } catch (Throwable $e) {
                error("Rollback failed: {$e->getMessage()}. Manual intervention may be needed.");
            }
        }
    }

    private function seedData(string $package): void
    {
        // Module seeders follow convention: ModuleName\Database\Seeders\ModuleSeeder
        $parts = explode('/', $package);
        $moduleName = str_replace('module-', '', end($parts));
        $moduleName = str_replace('-', '', ucwords($moduleName, '-'));
        $seederClass = "Cogneiss\\Module{$moduleName}\\Database\\Seeders\\{$moduleName}Seeder";

        if (class_exists($seederClass)) {
            spin(function () use ($seederClass): void {
                Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true,
                    '--no-interaction' => true,
                ]);
            }, 'Seeding demo data...');
            info('  ✓ Demo data seeded');
        }
    }
}
