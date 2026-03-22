<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Providers\SettingsOverlayServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

/**
 * One-command demo setup for evaluating the starter kit.
 *
 * Configures SQLite, seeds demo data, sets up mock AI, and opens the browser.
 * Designed for the 5-minute attention window — a buyer should see value immediately.
 */
final class AppDemoCommand extends Command
{
    protected $signature = 'app:demo
                            {--no-open : Do not open the browser automatically}
                            {--reset : Reset existing demo data and start fresh}';

    protected $description = 'Set up a demo environment with sample data in under 60 seconds';

    public function handle(): int
    {
        intro('🚀 Setting up demo environment...');

        if ($this->option('reset')) {
            $this->resetDemo();
        }

        // 1. Configure SQLite if no DB is configured
        $this->configureDatabase();

        // 2. Run migrations
        spin(function (): void {
            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        }, 'Running migrations...');
        info('✓ Database ready');

        // 3. Run settings migrations
        try {
            spin(function (): void {
                Artisan::call('migrate', [
                    '--path' => 'database/settings',
                    '--force' => true,
                    '--no-interaction' => true,
                ]);
            }, 'Running settings migrations...');
        } catch (Throwable) {
            // Settings migrations may already be run
        }

        // 4. Apply settings overlay
        try {
            SettingsOverlayServiceProvider::applyOverlay();
        } catch (Throwable) {
            // Settings may not be available yet
        }

        // 5. Seed demo data
        spin(function (): void {
            Artisan::call('db:seed', ['--force' => true, '--no-interaction' => true]);
        }, 'Seeding demo data...');
        info('✓ Demo data seeded');

        // 6. Sync permissions
        try {
            spin(function (): void {
                Artisan::call('permission:sync', ['--no-interaction' => true]);
            }, 'Syncing permissions...');
        } catch (Throwable) {
            // Command may not exist
        }

        // 7. Create demo admin user
        $this->createDemoUser();

        // 8. Build frontend assets if needed
        $this->buildFrontend();

        // 9. Display access info
        $this->newLine();
        info('✅ Demo environment ready!');
        $this->newLine();
        $this->table(['', ''], [
            ['URL', config('app.url', 'http://localhost:8000')],
            ['Email', 'demo@example.com'],
            ['Password', 'password'],
            ['Admin Panel', config('app.url', 'http://localhost:8000').'/admin'],
        ]);

        $this->newLine();
        $this->line('  <comment>Features to explore:</comment>');
        $this->line('  • Multi-tenancy — switch between organizations');
        $this->line('  • AI Chat — talk to the AI assistant at /chat');
        $this->line('  • Admin Panel — full Filament admin at /admin');
        $this->line('  • DataTables — server-side tables at /users');
        $this->line('  • Billing — subscription flow at /billing');

        // 10. Open browser
        if (! $this->option('no-open')) {
            $url = config('app.url', 'http://localhost:8000');
            $this->openBrowser($url);
        }

        return self::SUCCESS;
    }

    private function configureDatabase(): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            File::copy(base_path('.env.example'), $envPath);
            info('✓ Created .env from .env.example');
        }

        $envContent = File::get($envPath);

        // If DB is already configured with a non-SQLite driver, respect it
        if (preg_match('/^DB_CONNECTION=(?!sqlite)/m', $envContent)) {
            info('✓ Using existing database configuration');

            return;
        }

        // Configure SQLite
        $dbPath = database_path('database.sqlite');

        if (! File::exists($dbPath)) {
            File::put($dbPath, '');
        }

        // Update .env for SQLite
        $envContent = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*/m', "DB_DATABASE={$dbPath}", $envContent);
        File::put($envPath, $envContent);

        // Generate app key if missing
        if (empty(config('app.key')) || config('app.key') === 'base64:') {
            Artisan::call('key:generate', ['--force' => true, '--no-interaction' => true]);
        }

        info('✓ SQLite database configured');
    }

    private function createDemoUser(): void
    {
        try {
            $userClass = config('auth.providers.users.model', \App\Models\User::class);
            $user = $userClass::query()->where('email', 'demo@example.com')->first();

            if ($user) {
                $this->ensureDemoUserHasOrganization($user);
                info('✓ Demo user exists');

                return;
            }

            $user = $userClass::query()->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'onboarding_completed' => true,
            ]);

            // Try to assign super-admin role
            try {
                $user->assignRole('super-admin');
            } catch (Throwable) {
                // Role may not exist
            }

            $this->ensureDemoUserHasOrganization($user);

            info('✓ Demo user created (demo@example.com / password)');
        } catch (Throwable $e) {
            warning('Could not create demo user: '.$e->getMessage());
        }
    }

    /**
     * Ensure the demo user has at least one organization with a default set,
     * so TenantContext can resolve and the user is not stuck without org context.
     */
    private function ensureDemoUserHasOrganization(mixed $user): void
    {
        try {
            if ($user->organizations()->exists()) {
                // Ensure a default is set
                if ($user->defaultOrganization() === null) {
                    $firstOrg = $user->organizations()->first();
                    if ($firstOrg !== null) {
                        $user->organizations()->updateExistingPivot($firstOrg->id, ['is_default' => true]);
                    }
                }

                return;
            }

            // Create a demo organization for the user
            $orgClass = \App\Models\Organization::class;
            $org = $orgClass::query()->firstOrCreate(
                ['name' => 'Demo Organization'],
                ['owner_id' => $user->id]
            );

            if (! $user->organizations()->where('organizations.id', $org->id)->exists()) {
                $org->addMember($user, 'admin');
            }

            $user->organizations()->updateExistingPivot($org->id, ['is_default' => true]);
        } catch (Throwable) {
            // Organization setup is optional for demo
        }
    }

    private function buildFrontend(): void
    {
        $manifestPath = public_path('build/manifest.json');

        if (File::exists($manifestPath)) {
            return;
        }

        // Check if npm/node is available
        $npmPath = mb_trim((string) shell_exec('which npm 2>/dev/null'));

        if (empty($npmPath)) {
            warning('npm not found — run "npm install && npm run build" manually');

            return;
        }

        spin(function (): void {
            exec('npm install --no-audit --no-fund 2>&1');
            exec('npm run build 2>&1');
        }, 'Building frontend assets...');

        info('✓ Frontend built');
    }

    private function resetDemo(): void
    {
        $dbPath = database_path('database.sqlite');

        if (File::exists($dbPath)) {
            File::delete($dbPath);
            File::put($dbPath, '');
            info('✓ Database reset');
        }
    }

    private function openBrowser(string $url): void
    {
        $command = PHP_OS_FAMILY === 'Darwin'
            ? "open {$url}"
            : (PHP_OS_FAMILY === 'Windows' ? "start {$url}" : "xdg-open {$url}");

        exec("{$command} 2>/dev/null &");
    }
}
