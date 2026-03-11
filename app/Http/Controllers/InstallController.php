<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\SettingsOverlayServiceProvider;
use App\Settings\AppSettings;
use App\Settings\AuthSettings;
use App\Settings\BroadcastingSettings;
use App\Settings\FilesystemSettings;
use App\Settings\MailSettings;
use App\Settings\MonitoringSettings;
use App\Settings\PrismSettings;
use App\Settings\ScoutSettings;
use App\Settings\SeoSettings;
use App\Settings\SetupWizardSettings;
use App\Settings\TenancySettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * Web installer — mirrors every phase of `php artisan app:install`.
 *
 * Step order (15 total):
 *   database → migrate → admin → app
 *   → tenancy → infrastructure → mail → search → ai
 *   → social → storage → broadcasting → seo → monitoring
 *   → demo
 *
 * The first 4 steps are required. Steps 5–14 are optional (each has a Skip button).
 * Completed optional steps are tracked in the session under `install_optional_done`.
 */
final class InstallController extends Controller
{
    /** All optional steps in display order. */
    private const array OPTIONAL_STEPS = [
        'tenancy',
        'infrastructure',
        'mail',
        'search',
        'ai',
        'social',
        'storage',
        'broadcasting',
        'seo',
        'monitoring',
    ];

    /**
     * Demo module definitions — mirrors AppInstallCommand.
     *
     * @var array<string, array{label: string, description: string, seeders: list<string>}>
     */
    private const array MODULES = [
        'users' => [
            'label' => 'Users',
            'description' => 'Sample user accounts (admin, demo users)',
            'seeders' => ['Database\\Seeders\\Development\\UsersSeeder'],
        ],
        'organizations' => [
            'label' => 'Organizations',
            'description' => 'Sample organizations, domains, and invitations',
            'seeders' => [
                'Database\\Seeders\\Development\\OrganizationSeeder',
                'Database\\Seeders\\Development\\OrganizationDomainSeeder',
                'Database\\Seeders\\Development\\OrganizationInvitationSeeder',
            ],
        ],
        'billing' => [
            'label' => 'Billing & Subscriptions',
            'description' => 'Plans, gateways, subscriptions, credits, invoices',
            'seeders' => [
                'Database\\Seeders\\Development\\PlanSeeder',
                'Database\\Seeders\\Development\\PaymentGatewaySeeder',
                'Database\\Seeders\\Development\\GatewayProductSeeder',
                'Database\\Seeders\\Development\\BillingSeeder',
                'Database\\Seeders\\Development\\SubscriptionSeeder',
                'Database\\Seeders\\Development\\CreditPackSeeder',
                'Database\\Seeders\\Development\\CreditSeeder',
                'Database\\Seeders\\Development\\InvoiceSeeder',
                'Database\\Seeders\\Development\\BillingMetricSeeder',
                'Database\\Seeders\\Development\\FailedPaymentAttemptSeeder',
                'Database\\Seeders\\Development\\RefundRequestSeeder',
            ],
        ],
        'content' => [
            'label' => 'Content',
            'description' => 'Blog posts, pages, help articles, changelog, categories',
            'seeders' => [
                'Database\\Seeders\\Development\\CategorySeeder',
                'Database\\Seeders\\Development\\PostSeeder',
                'Database\\Seeders\\Development\\PageSeeder',
                'Database\\Seeders\\Development\\PageRevisionSeeder',
                'Database\\Seeders\\Development\\HelpArticleSeeder',
                'Database\\Seeders\\Development\\ChangelogEntrySeeder',
            ],
        ],
        'marketing' => [
            'label' => 'Marketing & CRM',
            'description' => 'Affiliates, contact forms, enterprise inquiries, vouchers',
            'seeders' => [
                'Database\\Seeders\\Development\\AffiliateSeeder',
                'Database\\Seeders\\Development\\AffiliateCommissionSeeder',
                'Database\\Seeders\\Development\\AffiliatePayoutSeeder',
                'Database\\Seeders\\Development\\ContactSubmissionSeeder',
                'Database\\Seeders\\Development\\EnterpriseInquirySeeder',
                'Database\\Seeders\\Development\\VoucherScopeSeeder',
            ],
        ],
        'developer' => [
            'label' => 'Developer Samples',
            'description' => 'Visibility demos, model flags, shareables, webhooks, embeddings',
            'seeders' => [
                'Database\\Seeders\\Development\\ModelFlagSeeder',
                'Database\\Seeders\\Development\\VisibilityDemoSeeder',
                'Database\\Seeders\\Development\\ShareableSeeder',
                'Database\\Seeders\\Development\\CategorizableSeeder',
                'Database\\Seeders\\Development\\WebhookLogSeeder',
                'Database\\Seeders\\Development\\EmbeddingDemoSeeder',
            ],
        ],
    ];

    /** All steps in order (required then optional then demo). */
    private const array STEP_ORDER = [
        'database', 'migrate', 'admin', 'app',
        'tenancy', 'infrastructure', 'mail', 'search', 'ai', 'social',
        'storage', 'broadcasting', 'seo', 'monitoring', 'demo',
    ];

    public function show(): View|RedirectResponse
    {
        $resolved = $this->resolveStep();

        if (request()->boolean('back')) {
            $optionalAndDemo = array_merge(self::OPTIONAL_STEPS, ['demo']);
            if (in_array($resolved, $optionalAndDemo, true)) {
                $done = session('install_optional_done', []);
                array_pop($done);
                session(['install_optional_done' => array_values($done)]);

                return redirect()->route('install');
            }
        }

        $step = $resolved;
        $requestedStep = request()->query('step');
        if (is_string($requestedStep) && $requestedStep !== '') {
            $resolvedIdx = array_search($resolved, self::STEP_ORDER, true);
            $requestedIdx = array_search($requestedStep, self::STEP_ORDER, true);
            if ($resolvedIdx !== false && $requestedIdx !== false && $requestedIdx <= $resolvedIdx) {
                $step = $requestedStep;
            }
        }

        return view('install.index', [
            'step' => $step,
            'modules' => self::MODULES,
        ]);
    }

    /**
     * Express install: prepares SQLite and .env synchronously, then spawns a background
     * process to run migrations and seeders. Returns JSON so the browser can poll for progress.
     */
    public function express(Request $request): JsonResponse
    {
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        $envPath = base_path('.env');
        $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';

        if (! preg_match('/^APP_ENV=/m', $env)) {
            $env = $this->setEnvVar($env, 'APP_ENV', 'local');
        }

        $dbPath = database_path('database.sqlite');
        if (! file_exists($dbPath)) {
            touch($dbPath);
        }

        $env = $this->setEnvVar($env, 'DB_CONNECTION', 'sqlite');
        $env = $this->removeEnvVar($env, 'DB_HOST');
        $env = $this->removeEnvVar($env, 'DB_PORT');
        $env = $this->removeEnvVar($env, 'DB_DATABASE');
        $env = $this->removeEnvVar($env, 'DB_USERNAME');
        $env = $this->removeEnvVar($env, 'DB_PASSWORD');
        file_put_contents($envPath, $env);
        config(['database.default' => 'sqlite']);
        DB::purge();

        if (! $this->isDatabaseReachable()) {
            return response()->json(['error' => 'Could not connect to SQLite. Check storage permissions.'], 422);
        }

        $filename = 'install_progress_'.Str::uuid().'.json';
        $progressFile = storage_path('app/'.$filename);

        file_put_contents($progressFile, json_encode(['status' => 'running', 'steps' => []]));

        $appUrl = $request->root();

        // Run install steps after the HTTP response is sent. app()->terminating() callbacks
        // are called in Kernel::terminate() (public/index.php after $response->send()),
        // so the browser receives the JSON immediately and can start polling.
        app()->terminating(function () use ($progressFile, $appUrl): void {
            $this->runExpressInstallSteps($progressFile, $appUrl);
        });

        session(['install_optional_done' => self::OPTIONAL_STEPS]);

        return response()->json(['progressFile' => $filename]);
    }

    /**
     * Poll the progress of a background express install.
     */
    public function expressStatus(Request $request): JsonResponse
    {
        $filename = (string) $request->query('key', '');

        if ($filename === '' || ! str_starts_with($filename, 'install_progress_') || ! str_ends_with($filename, '.json')) {
            return response()->json(['error' => 'Invalid key.'], 400);
        }

        $progressFile = storage_path('app/'.$filename);

        if (! file_exists($progressFile)) {
            return response()->json(['status' => 'pending', 'steps' => []]);
        }

        $json = file_get_contents($progressFile);
        $state = is_string($json) ? (json_decode($json, true) ?? []) : [];

        return response()->json($state);
    }

    public function store(Request $request): RedirectResponse
    {
        return match ($request->input('step')) {
            'database' => $this->handleDatabase($request),
            'migrate' => $this->handleMigrate(),
            'admin' => $this->handleAdmin($request),
            'app' => $this->handleApp($request),
            'tenancy' => $this->handleOptional('tenancy', fn () => $this->saveTenancy($request)),
            'infrastructure' => $this->handleOptional('infrastructure', fn () => $this->saveInfrastructure($request)),
            'mail' => $this->handleOptional('mail', fn () => $this->saveMail($request)),
            'search' => $this->handleOptional('search', fn () => $this->saveSearch($request)),
            'ai' => $this->handleOptional('ai', fn () => $this->saveAi($request)),
            'social' => $this->handleOptional('social', fn () => $this->saveSocial($request)),
            'storage' => $this->handleOptional('storage', fn () => $this->saveStorage($request)),
            'broadcasting' => $this->handleOptional('broadcasting', fn () => $this->saveBroadcasting($request)),
            'seo' => $this->handleOptional('seo', fn () => $this->saveSeo($request)),
            'monitoring' => $this->handleOptional('monitoring', fn () => $this->saveMonitoring($request)),
            'demo' => $this->handleDemo($request),
            default => redirect()->route('install'),
        };
    }

    /**
     * Test a connection for the given installer step (database, infrastructure, mail, search).
     * Expects step + form fields in the request. Returns JSON { "ok": true } or { "ok": false, "message": "..." }.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate(['step' => 'required|in:database,infrastructure,mail,search']);

        $step = $request->input('step');

        try {
            match ($step) {
                'database' => $this->testDatabaseConnection($request),
                'infrastructure' => $this->testInfrastructureConnection($request),
                'mail' => $this->testMailConnection($request),
                'search' => $this->testSearchConnection($request),
            };

            return response()->json(['ok' => true]);
        } catch (Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─── Express install worker ───────────────────────────────────────────────

    private function runExpressInstallSteps(string $progressFile, string $appUrl): void
    {
        try {
            $this->writeProgress($progressFile, 'migrate', 'running');
            Artisan::call('migrate', ['--force' => true]);
            $this->writeProgress($progressFile, 'migrate', 'done');

            $this->writeProgress($progressFile, 'roles', 'running');
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Essential\\RolesAndPermissionsSeeder', '--force' => true]);
            $this->writeProgress($progressFile, 'roles', 'done');

            $this->writeProgress($progressFile, 'gamification', 'running');
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Essential\\GamificationSeeder', '--force' => true]);
            $this->writeProgress($progressFile, 'gamification', 'done');

            $this->writeProgress($progressFile, 'mail_tpl', 'running');
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Essential\\MailTemplatesSeeder', '--force' => true]);
            $this->writeProgress($progressFile, 'mail_tpl', 'done');

            $this->writeProgress($progressFile, 'admin', 'running');
            // Use raw DB insert to bypass ALL model events/observers (Scout, userstamps, etc.)
            // which may hang in the terminating callback due to missing auth/tenant context.
            $now = now()->toDateTimeString();
            $userId = DB::table('users')->insertGetId([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            // Directly assign super-admin role — bypasses OrganizationTeamResolver (returns 0,
            // not null, without tenant context) which deadlocks the permission cache flush.
            $superAdminRole = DB::table('roles')->where('name', 'super-admin')->whereNull('organization_id')->first();
            if ($superAdminRole) {
                // organization_id = 0 means "global" (no org context).
                // The column is NOT NULL, and OrganizationTeamResolver returns 0 when no tenant is set.
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $superAdminRole->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userId,
                    'organization_id' => 0,
                ]);
            }
            $this->writeProgress($progressFile, 'admin', 'done');

            $this->writeProgress($progressFile, 'settings', 'running');

            $app = resolve(AppSettings::class);
            $app->site_name = 'My App';
            $app->url = $appUrl;
            $app->timezone = 'UTC';
            $app->save();

            DB::table('settings')->where('group', MailSettings::group())->delete();
            App::forgetInstance(MailSettings::class);
            resolve(\Spatie\LaravelSettings\SettingsMapper::class)->save(MailSettings::class, collect([
                'mailer' => 'smtp',
                'smtp_host' => '127.0.0.1',
                'smtp_port' => 2525,
                'smtp_username' => 'My App',
                'smtp_password' => null,
                'smtp_encryption' => null,
                'from_address' => 'hello@example.com',
                'from_name' => 'My App',
            ]));

            $wizard = resolve(SetupWizardSettings::class);
            $wizard->setup_completed = true;
            $wizard->completed_steps = ['app', 'mail', 'billing', 'ai', 'complete'];
            $wizard->save();

            SettingsOverlayServiceProvider::applyOverlay();

            $this->writeProgress($progressFile, 'settings', 'done');

            $this->writeProgressFile($progressFile, [
                'status' => 'done',
                'steps' => $this->readProgressFile($progressFile)['steps'] ?? [],
                'redirect' => '/admin',
            ]);

        } catch (Throwable $e) {
            $this->writeProgressFile($progressFile, [
                'status' => 'error',
                'message' => $e->getMessage(),
                'steps' => $this->readProgressFile($progressFile)['steps'] ?? [],
            ]);
        }
    }

    private function writeProgress(string $progressFile, string $stepKey, string $status): void
    {
        $state = $this->readProgressFile($progressFile);
        $state['steps'][$stepKey] = $status;
        $this->writeProgressFile($progressFile, $state);
    }

    /** @return array<string, mixed> */
    private function readProgressFile(string $progressFile): array
    {
        if (! file_exists($progressFile)) {
            return ['status' => 'running', 'steps' => []];
        }

        $json = file_get_contents($progressFile);

        return is_string($json) ? (json_decode($json, true) ?? []) : [];
    }

    /** @param array<string, mixed> $data */
    private function writeProgressFile(string $progressFile, array $data): void
    {
        file_put_contents($progressFile, json_encode($data), LOCK_EX);
    }

    // ─── Required step handlers ────────────────────────────────────────────────

    private function handleDatabase(Request $request): RedirectResponse
    {
        $driver = $request->input('driver', 'sqlite');
        $envPath = base_path('.env');
        $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        if (! preg_match('/^APP_ENV=/m', $env)) {
            $env = $this->setEnvVar($env, 'APP_ENV', 'local');
        }

        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        if ($driver === 'sqlite') {
            $dbPath = database_path('database.sqlite');

            if (! file_exists($dbPath)) {
                touch($dbPath);
            }

            $env = $this->setEnvVar($env, 'DB_CONNECTION', 'sqlite');
            $env = $this->removeEnvVar($env, 'DB_HOST');
            $env = $this->removeEnvVar($env, 'DB_PORT');
            $env = $this->removeEnvVar($env, 'DB_DATABASE');
            $env = $this->removeEnvVar($env, 'DB_USERNAME');
            $env = $this->removeEnvVar($env, 'DB_PASSWORD');
            file_put_contents($envPath, $env);
            config(['database.default' => 'sqlite']);
        } else {
            $validated = Validator::make($request->all(), [
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_database' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
            ])->validate();

            $env = $this->setEnvVar($env, 'DB_CONNECTION', $driver);
            $env = $this->setEnvVar($env, 'DB_HOST', $validated['db_host']);
            $env = $this->setEnvVar($env, 'DB_PORT', (string) $validated['db_port']);
            $env = $this->setEnvVar($env, 'DB_DATABASE', $validated['db_database']);
            $env = $this->setEnvVar($env, 'DB_USERNAME', $validated['db_username']);
            $env = $this->setEnvVar($env, 'DB_PASSWORD', $validated['db_password'] ?? '');
            file_put_contents($envPath, $env);

            config([
                'database.default' => $driver,
                "database.connections.{$driver}.host" => $validated['db_host'],
                "database.connections.{$driver}.port" => $validated['db_port'],
                "database.connections.{$driver}.database" => $validated['db_database'],
                "database.connections.{$driver}.username" => $validated['db_username'],
                "database.connections.{$driver}.password" => $validated['db_password'] ?? '',
            ]);

            DB::purge();
        }

        if (! $this->isDatabaseReachable()) {
            return redirect()->route('install')
                ->withErrors(['db' => 'Could not connect. Please check your credentials.'])
                ->withInput();
        }

        return redirect()->route('install');
    }

    private function handleMigrate(): RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Essential\\RolesAndPermissionsSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Essential\\GamificationSeeder', '--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Essential\\MailTemplatesSeeder', '--force' => true]);

        return redirect()->route('install');
    }

    private function handleAdmin(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'An account with this email already exists.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ])->validate();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles(['super-admin']);

        return redirect()->route('install');
    }

    private function handleApp(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'url' => 'required|url',
            'timezone' => 'nullable|string|timezone',
        ])->validate();

        $app = resolve(AppSettings::class);
        $app->site_name = $validated['site_name'];
        $app->url = $validated['url'];
        $app->timezone = $validated['timezone'] ?? 'UTC';
        $app->save();

        $mail = resolve(MailSettings::class);
        $mail->from_name = $validated['site_name'];
        $mail->save();

        return redirect()->route('install');
    }

    private function handleDemo(Request $request): RedirectResponse
    {
        $selectedModules = $request->input('modules', []);

        if (is_array($selectedModules) && $selectedModules !== []) {
            foreach ($selectedModules as $key) {
                $module = self::MODULES[$key] ?? null;

                if ($module === null) {
                    continue;
                }

                foreach ($module['seeders'] as $seederClass) {
                    try {
                        Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
                    } catch (Throwable) {
                        // Non-fatal — continue with remaining seeders
                    }
                }
            }
        }

        $wizard = resolve(SetupWizardSettings::class);
        $wizard->setup_completed = true;
        $wizard->completed_steps = ['app', 'mail', 'billing', 'ai', 'complete'];
        $wizard->save();

        SettingsOverlayServiceProvider::applyOverlay();

        return redirect('/admin');
    }

    // ─── Optional step dispatcher ─────────────────────────────────────────────

    /**
     * Shared wrapper for all optional steps:
     * marks the step done in session, then runs the save closure (unless skip=1).
     */
    private function handleOptional(string $step, callable $save): RedirectResponse
    {
        $request = request();

        if (! $request->boolean('skip')) {
            $save();
        }

        $done = session('install_optional_done', []);
        $done[] = $step;
        session(['install_optional_done' => array_unique($done)]);

        return redirect()->route('install');
    }

    // ─── Optional step save closures ─────────────────────────────────────────

    private function saveTenancy(Request $request): void
    {
        $tenancy = resolve(TenancySettings::class);
        $tenancy->enabled = $request->boolean('enabled', true);
        $tenancy->allow_user_org_creation = $request->boolean('allow_user_org_creation', true);
        $tenancy->auto_create_personal_org = $request->boolean('auto_create_personal_org', true);
        $tenancy->save();
    }

    private function saveInfrastructure(Request $request): void
    {
        $driver = $request->input('driver', 'database');

        if ($driver === 'redis') {
            $host = (string) $request->input('redis_host', '127.0.0.1');
            $port = (string) $request->input('redis_port', '6379');
            $password = (string) $request->input('redis_password', '');

            $envPath = base_path('.env');
            $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'REDIS_HOST', $host);
            $env = $this->setEnvVar($env, 'REDIS_PORT', $port);

            if ($password !== '') {
                $env = $this->setEnvVar($env, 'REDIS_PASSWORD', $password);
            } else {
                $env = $this->removeEnvVar($env, 'REDIS_PASSWORD');
            }

            $env = $this->setEnvVar($env, 'CACHE_STORE', 'redis');
            $env = $this->setEnvVar($env, 'SESSION_DRIVER', 'redis');
            $env = $this->setEnvVar($env, 'QUEUE_CONNECTION', 'redis');
            file_put_contents($envPath, $env);

            // Do not change config() here: the current request still uses cookie/database
            // session, so the session (including install_optional_done) is persisted.
            // Next request will read .env and use Redis.
        }
    }

    private function saveMail(Request $request): void
    {
        $mailer = (string) $request->input('mailer', 'log');
        $fromAddress = (string) $request->input('from_address', '');
        $fromName = (string) $request->input('from_name', '');

        $properties = collect([
            'mailer' => $mailer,
            'smtp_host' => $mailer === 'smtp' ? (string) $request->input('smtp_host', '') : '127.0.0.1',
            'smtp_port' => $mailer === 'smtp' ? (int) $request->input('smtp_port', 587) : 2525,
            'smtp_username' => $mailer === 'smtp' ? (string) $request->input('smtp_username', '') : null,
            'smtp_password' => $mailer === 'smtp' ? (string) $request->input('smtp_password', '') : null,
            'smtp_encryption' => $mailer === 'smtp' ? $this->normalizeSmtpEncryption($request->input('smtp_encryption')) : null,
            'from_address' => $fromAddress !== '' ? $fromAddress : 'hello@example.com',
            'from_name' => $fromName !== '' ? $fromName : 'Example',
        ]);

        DB::table('settings')->where('group', MailSettings::group())->delete();
        App::forgetInstance(MailSettings::class);

        resolve(\Spatie\LaravelSettings\SettingsMapper::class)->save(MailSettings::class, $properties);
    }

    private function normalizeSmtpEncryption(mixed $value): ?string
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return (string) $value;
    }

    private function saveSearch(Request $request): void
    {
        $scout = resolve(ScoutSettings::class);
        $scout->driver = (string) $request->input('driver', 'collection');

        if ($scout->driver === 'typesense') {
            $scout->typesense_api_key = $request->filled('typesense_api_key') ? (string) $request->input('typesense_api_key') : null;
            $scout->typesense_host = (string) $request->input('typesense_host', 'localhost');
            $scout->typesense_port = (int) $request->input('typesense_port', 8108);
            $scout->typesense_protocol = (string) $request->input('typesense_protocol', 'http');
        }

        $scout->save();
    }

    private function saveAi(Request $request): void
    {
        $provider = (string) $request->input('provider', 'openrouter');
        $apiKey = $request->filled('api_key') ? (string) $request->input('api_key') : null;

        $prism = resolve(PrismSettings::class);
        $prism->default_provider = $provider;
        $prism->default_model = (string) $request->input('model', '');

        if ($apiKey !== null) {
            match ($provider) {
                'openai' => $prism->openai_api_key = $apiKey,
                'anthropic' => $prism->anthropic_api_key = $apiKey,
                'groq' => $prism->groq_api_key = $apiKey,
                'xai' => $prism->xai_api_key = $apiKey,
                'gemini' => $prism->gemini_api_key = $apiKey,
                'deepseek' => $prism->deepseek_api_key = $apiKey,
                'mistral' => $prism->mistral_api_key = $apiKey,
                'openrouter' => $prism->openrouter_api_key = $apiKey,
                default => null,
            };
        }

        $prism->save();
    }

    private function saveSocial(Request $request): void
    {
        $auth = resolve(AuthSettings::class);

        if ($request->filled('google_client_id')) {
            $auth->google_client_id = (string) $request->input('google_client_id');
            $auth->google_client_secret = (string) $request->input('google_client_secret', '');
            $auth->google_oauth_enabled = true;
        }

        if ($request->filled('github_client_id')) {
            $auth->github_client_id = (string) $request->input('github_client_id');
            $auth->github_client_secret = (string) $request->input('github_client_secret', '');
            $auth->github_oauth_enabled = true;
        }

        $auth->save();
    }

    private function saveStorage(Request $request): void
    {
        $fs = resolve(FilesystemSettings::class);
        $fs->default_disk = (string) $request->input('disk', 'local');

        if ($fs->default_disk === 's3') {
            $fs->s3_key = (string) $request->input('s3_key', '');
            $fs->s3_secret = (string) $request->input('s3_secret', '');
            $fs->s3_region = (string) $request->input('s3_region', 'us-east-1');
            $fs->s3_bucket = (string) $request->input('s3_bucket', '');
            $fs->s3_url = $request->filled('s3_url') ? (string) $request->input('s3_url') : null;
        }

        $fs->save();
    }

    private function saveBroadcasting(Request $request): void
    {
        $bc = resolve(BroadcastingSettings::class);
        $bc->default_connection = 'reverb';
        $bc->reverb_app_id = (string) $request->input('reverb_app_id', '');
        $bc->reverb_app_key = (string) $request->input('reverb_app_key', '');
        $bc->reverb_app_secret = (string) $request->input('reverb_app_secret', '');
        $bc->reverb_host = (string) $request->input('reverb_host', 'localhost');
        $bc->reverb_port = (int) $request->input('reverb_port', 8080);
        $bc->reverb_scheme = (string) $request->input('reverb_scheme', 'http');
        $bc->save();
    }

    private function saveSeo(Request $request): void
    {
        $seo = resolve(SeoSettings::class);
        $seo->meta_title = (string) $request->input('meta_title', '');
        $seo->meta_description = (string) $request->input('meta_description', '');
        $seo->og_image = $request->filled('og_image') ? (string) $request->input('og_image') : null;
        $seo->save();
    }

    private function saveMonitoring(Request $request): void
    {
        $mon = resolve(MonitoringSettings::class);
        $mon->sentry_dsn = $request->filled('sentry_dsn') ? (string) $request->input('sentry_dsn') : null;
        $mon->sentry_sample_rate = (float) $request->input('sentry_sample_rate', 1.0);
        $mon->save();
    }

    // ─── Step resolver ────────────────────────────────────────────────────────

    private function resolveStep(): string
    {
        if (! $this->isDatabaseReachable()) {
            return 'database';
        }

        if (! $this->migrationsRan()) {
            return 'migrate';
        }

        if (! User::query()->whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->exists()) {
            return 'admin';
        }

        try {
            $app = resolve(AppSettings::class);

            if (empty($app->site_name) || $app->site_name === 'My App') {
                return 'app';
            }
        } catch (Throwable) {
            return 'app';
        }

        $done = session('install_optional_done', []);

        foreach (self::OPTIONAL_STEPS as $optStep) {
            if (! in_array($optStep, $done, true)) {
                return $optStep;
            }
        }

        return 'demo';
    }

    private function testDatabaseConnection(Request $request): void
    {
        $driver = $request->input('driver', 'pgsql');

        if ($driver === 'sqlite') {
            $path = $request->input('db_database', database_path('database.sqlite'));
            if (! is_string($path) || $path === '') {
                $path = database_path('database.sqlite');
            }
            if (! str_contains($path, DIRECTORY_SEPARATOR)) {
                $path = database_path($path);
            }
            config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $path]);
            DB::purge('sqlite');
            DB::connection()->getPdo();

            return;
        }

        $validated = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ])->validate();

        config([
            'database.default' => $driver,
            "database.connections.{$driver}.host" => $validated['db_host'],
            "database.connections.{$driver}.port" => $validated['db_port'],
            "database.connections.{$driver}.database" => $validated['db_database'],
            "database.connections.{$driver}.username" => $validated['db_username'],
            "database.connections.{$driver}.password" => $validated['db_password'] ?? '',
        ]);
        DB::purge($driver);
        DB::connection()->getPdo();
    }

    private function testInfrastructureConnection(Request $request): void
    {
        if ($request->input('driver') !== 'redis') {
            return;
        }

        $host = $request->input('redis_host', '127.0.0.1');
        $port = (int) $request->input('redis_port', 6379);
        $password = $request->input('redis_password');

        config([
            'database.redis.default' => array_filter([
                'host' => $host,
                'port' => $port,
                'password' => $password !== '' && $password !== null ? $password : null,
                'database' => 0,
            ], fn ($v) => $v !== null),
        ]);
        Redis::purge('default');
        Redis::connection()->ping();
    }

    private function testMailConnection(Request $request): void
    {
        if ($request->input('mailer') !== 'smtp') {
            return;
        }

        $host = $request->input('smtp_host', '127.0.0.1');
        $port = (int) $request->input('smtp_port', 2525);

        $socket = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($socket === false) {
            throw new RuntimeException("Could not reach SMTP server {$host}:{$port} — {$errstr} (errno {$errno})");
        }
        fclose($socket);
    }

    private function testSearchConnection(Request $request): void
    {
        if ($request->input('driver') !== 'typesense') {
            return;
        }

        $host = $request->input('typesense_host', 'localhost');
        $port = (int) $request->input('typesense_port', 8108);
        $protocol = $request->input('typesense_protocol', 'http');
        $apiKey = $request->input('typesense_api_key', '');

        if ($apiKey === '') {
            throw new RuntimeException('Typesense API key is required.');
        }

        $url = "{$protocol}://{$host}:{$port}/health";
        $response = Http::withHeaders(['X-TYPESENSE-API-KEY' => $apiKey])
            ->timeout(5)
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Typesense returned HTTP {$response->status()}.");
        }
    }

    // ─── .env helpers ────────────────────────────────────────────────────────

    private function isDatabaseReachable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function migrationsRan(): bool
    {
        try {
            DB::table('settings')->limit(1)->get();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function setEnvVar(string $env, string $key, string $value): string
    {
        $escaped = preg_match('/\s/', $value) ? '"'.$value.'"' : $value;
        $line = "{$key}={$escaped}";

        if (preg_match("/^{$key}=.*/m", $env)) {
            return (string) preg_replace("/^{$key}=.*/m", $line, $env);
        }

        return mb_rtrim($env)."\n{$line}\n";
    }

    private function removeEnvVar(string $env, string $key): string
    {
        return (string) preg_replace("/^{$key}=.*\n?/m", '', $env);
    }
}
