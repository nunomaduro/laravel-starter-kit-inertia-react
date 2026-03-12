<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\SettingsOverlayServiceProvider;
use App\Settings\AiSettings;
use App\Settings\AppSettings;
use App\Settings\AuthSettings;
use App\Settings\BackupSettings;
use App\Settings\BillingSettings;
use App\Settings\BroadcastingSettings;
use App\Settings\FeatureFlagSettings;
use App\Settings\FilesystemSettings;
use App\Settings\IntegrationsSettings;
use App\Settings\LemonSqueezySettings;
use App\Settings\MailSettings;
use App\Settings\MemorySettings;
use App\Settings\MonitoringSettings;
use App\Settings\PaddleSettings;
use App\Settings\PrismSettings;
use App\Settings\ScoutSettings;
use App\Settings\SeoSettings;
use App\Settings\SetupWizardSettings;
use App\Settings\StripeSettings;
use App\Settings\TenancySettings;
use App\Settings\ThemeSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
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
 * Web installer — mirrors every phase of `php artisan app:install` (same step order and settings).
 *
 * Step order (required + optional + demo):
 *   database → migrate → admin → app
 *   → tenancy → infrastructure → mail → search → ai
 *   → social → storage → broadcasting → seo → monitoring
 *   → billing → integrations → theme → memory → backup → features → demo
 *
 * The first 4 steps are required. Steps 5–16 are optional (each has a Skip button).
 * Completed optional steps are tracked in the session under `install_optional_done`.
 * Billing and features are synced with CLI (configureBilling / configureFeatures).
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
        'billing',
        'integrations',
        'theme',
        'memory',
        'backup',
        'features',
    ];

    /**
     * Demo module definitions — keep in sync with AppInstallCommand::MODULES.
     *
     * @var array<string, array{label: string, description: string, seeders: list<string>}>
     */
    private const array MODULES = [
        'users' => [
            'label' => 'Users',
            'description' => 'Sample user accounts (admin, demo, regular users)',
            'seeders' => [\Database\Seeders\Development\UsersSeeder::class],
        ],
        'organizations' => [
            'label' => 'Organizations',
            'description' => 'Sample organizations, domains, and invitations',
            'seeders' => [
                \Database\Seeders\Development\OrganizationSeeder::class,
                \Database\Seeders\Development\OrganizationDomainSeeder::class,
                \Database\Seeders\Development\OrganizationInvitationSeeder::class,
            ],
        ],
        'billing' => [
            'label' => 'Billing & Subscriptions',
            'description' => 'Plans, gateways, subscriptions, credits, invoices, refunds',
            'seeders' => [
                \Database\Seeders\Development\PlanSeeder::class,
                \Database\Seeders\Development\PaymentGatewaySeeder::class,
                \Database\Seeders\Development\GatewayProductSeeder::class,
                \Database\Seeders\Development\BillingSeeder::class,
                \Database\Seeders\Development\SubscriptionSeeder::class,
                \Database\Seeders\Development\CreditPackSeeder::class,
                \Database\Seeders\Development\CreditSeeder::class,
                \Database\Seeders\Development\InvoiceSeeder::class,
                \Database\Seeders\Development\BillingMetricSeeder::class,
                \Database\Seeders\Development\FailedPaymentAttemptSeeder::class,
                \Database\Seeders\Development\RefundRequestSeeder::class,
            ],
        ],
        'content' => [
            'label' => 'Content',
            'description' => 'Blog posts, pages, help articles, changelog entries, categories',
            'seeders' => [
                \Database\Seeders\Development\CategorySeeder::class,
                \Database\Seeders\Development\PostSeeder::class,
                \Database\Seeders\Development\PageSeeder::class,
                \Database\Seeders\Development\PageRevisionSeeder::class,
                \Database\Seeders\Development\HelpArticleSeeder::class,
                \Database\Seeders\Development\ChangelogEntrySeeder::class,
            ],
        ],
        'marketing' => [
            'label' => 'Marketing & CRM',
            'description' => 'Affiliates, contact submissions, enterprise inquiries, vouchers',
            'seeders' => [
                \Database\Seeders\Development\AffiliateSeeder::class,
                \Database\Seeders\Development\AffiliateCommissionSeeder::class,
                \Database\Seeders\Development\AffiliatePayoutSeeder::class,
                \Database\Seeders\Development\ContactSubmissionSeeder::class,
                \Database\Seeders\Development\EnterpriseInquirySeeder::class,
                \Database\Seeders\Development\VoucherScopeSeeder::class,
            ],
        ],
        'developer' => [
            'label' => 'Developer Samples',
            'description' => 'Model flags, visibility demos, shareables, webhooks, embeddings',
            'seeders' => [
                \Database\Seeders\Development\ModelFlagSeeder::class,
                \Database\Seeders\Development\VisibilityDemoSeeder::class,
                \Database\Seeders\Development\ShareableSeeder::class,
                \Database\Seeders\Development\CategorizableSeeder::class,
                \Database\Seeders\Development\WebhookLogSeeder::class,
                \Database\Seeders\Development\EmbeddingDemoSeeder::class,
            ],
        ],
    ];

    /** All steps in order (required then optional then demo). */
    private const array STEP_ORDER = [
        'database', 'migrate', 'admin', 'app',
        'tenancy', 'infrastructure', 'mail', 'search', 'ai', 'social',
        'storage', 'broadcasting', 'seo', 'monitoring', 'billing',
        'integrations', 'theme', 'memory', 'backup', 'features', 'demo',
    ];

    public function show(): View|RedirectResponse
    {
        $this->syncMigrateDoneFromTerminatingCallback();

        $resolved = $this->resolveStep();

        if (request()->boolean('back')) {
            $optionalAndDemo = array_merge(self::OPTIONAL_STEPS, ['demo']);
            if (in_array($resolved, $optionalAndDemo, true)) {
                $done = session('install_optional_done', []);
                array_pop($done);
                session(['install_optional_done' => array_values($done)]);

                return to_route('install');
            }
        }

        $step = $resolved;
        $resolvedIdx = array_search($resolved, self::STEP_ORDER, true);

        $requestedStep = request()->query('step');
        if (is_string($requestedStep) && $requestedStep !== '') {
            $requestedIdx = array_search($requestedStep, self::STEP_ORDER, true);
            if ($resolvedIdx !== false && $requestedIdx !== false && $requestedIdx <= $resolvedIdx) {
                $step = $requestedStep;
            }
        } elseif ($resolvedIdx !== false) {
            $lastStep = session('install_last_step');
            if (is_string($lastStep) && $lastStep !== '') {
                $lastIdx = array_search($lastStep, self::STEP_ORDER, true);
                if ($lastIdx !== false && $lastIdx >= $resolvedIdx) {
                    $step = $lastStep;
                }
            }
        }

        session(['install_last_step' => $step]);

        return view('install.index', [
            'step' => $step,
            'modules' => self::MODULES,
            'featureFlags' => $this->installFeatureFlagsList(),
        ]);
    }

    /**
     * Express install: prepares SQLite and .env synchronously, then spawns a background
     * process to run migrations and seeders. Returns JSON so the browser can poll for progress.
     */
    public function express(Request $request): JsonResponse
    {
        try {
            $wizard = resolve(SetupWizardSettings::class);
            if ($wizard->setup_completed) {
                return response()->json(['error' => 'Already installed.'], 409);
            }
        } catch (Throwable) {
            // Settings table not yet available — proceed with install
        }

        $validator = Validator::make($request->all(), [
            'preset' => ['nullable', 'string', 'in:saas,internal,ai_first'],
            'tenancy' => ['nullable', 'string', 'in:single,multi'],
            'demo' => ['nullable', 'string', 'in:none,minimal,full'],
            'single_org_name' => ['nullable', 'string', 'max:255'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:20'],
            'fallback_locale' => ['nullable', 'string', 'max:20'],
            'ai_provider' => ['nullable', 'string', 'in:openrouter,openai,anthropic,groq,gemini,xai,deepseek,mistral,ollama'],
            'ai_api_key' => ['nullable', 'string', 'max:1024'],
            'ai_model' => ['nullable', 'string', 'max:255'],
            'thesys_api_key' => ['nullable', 'string', 'max:1024'],
        ], [
            'preset.in' => 'The preset must be one of: saas, internal, ai_first.',
            'tenancy.in' => 'The tenancy must be single or multi.',
            'demo.in' => 'The demo must be none, minimal, or full.',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        }

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
        config(['database.connections.sqlite.database' => $dbPath]);
        DB::purge();

        if (! $this->isDatabaseReachable()) {
            return response()->json(['error' => 'Could not connect to SQLite. Check storage permissions.'], 422);
        }

        $filename = 'install_progress_'.Str::uuid().'.json';
        $progressFile = storage_path('app/'.$filename);

        $expressOptions = $this->resolveExpressOptions($request);
        file_put_contents($progressFile, json_encode(['status' => 'running', 'steps' => [], 'options' => $expressOptions]));

        $appUrl = $request->root();

        // Run install steps after the HTTP response is sent. app()->terminating() callbacks
        // are called in Kernel::terminate() (public/index.php after $response->send()),
        // so the browser receives the JSON immediately and can start polling.
        app()->terminating(function () use ($progressFile, $appUrl, $expressOptions): void {
            $this->runExpressInstallSteps($progressFile, $appUrl, $expressOptions);
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
        $status = $state['status'] ?? 'running';
        if (in_array($status, ['done', 'error'], true)) {
            @unlink($progressFile);
        }

        return response()->json($state);
    }

    /**
     * Start the migrate step in the background; returns a progress key for polling.
     */
    public function migrateRun(): JsonResponse
    {
        $progressKey = 'install_migrate_'.Str::random(16).'.json';
        $progressFile = storage_path('app/'.$progressKey);
        $this->writeMigrateProgress($progressFile, 'Starting…', false);

        app()->terminating(function () use ($progressFile): void {
            $this->runMigrateStepsWithProgress($progressFile);
        });

        return response()->json(['progress_key' => $progressKey]);
    }

    /**
     * Poll status of the migrate step.
     */
    public function migrateStatus(Request $request): JsonResponse
    {
        $key = (string) $request->query('key', '');
        if ($key === '' || ! str_starts_with($key, 'install_migrate_') || ! str_ends_with($key, '.json')) {
            return response()->json(['error' => 'Invalid key.'], 400);
        }

        $progressFile = storage_path('app/'.$key);
        if (! file_exists($progressFile)) {
            return response()->json(['message' => 'Pending…', 'done' => false]);
        }

        $data = json_decode((string) file_get_contents($progressFile), true);
        if (! is_array($data)) {
            return response()->json(['message' => 'Running…', 'done' => false]);
        }

        $done = $data['done'] ?? false;
        if ($done) {
            @unlink($progressFile);
        }

        return response()->json([
            'message' => $data['message'] ?? 'Running…',
            'done' => $done,
            'error' => $data['error'] ?? null,
        ]);
    }

    /**
     * One-time auto-login after express install. Validates signed token and redirects to /admin.
     */
    public function complete(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        if (! is_string($token) || $token === '') {
            return redirect()->to('/admin');
        }

        try {
            $payload = Crypt::decrypt($token);
        } catch (Throwable) {
            return redirect()->to('/admin');
        }

        if (! is_array($payload) || empty($payload['user_id']) || empty($payload['expires']) || (int) $payload['expires'] < time()) {
            return redirect()->to('/admin');
        }

        $user = User::query()->find((int) $payload['user_id']);
        if (! $user) {
            return redirect()->to('/admin');
        }

        auth()->login($user, true);

        return redirect()->to('/admin');
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
            'billing' => $this->handleOptional('billing', fn () => $this->saveBilling($request)),
            'integrations' => $this->handleOptional('integrations', fn () => $this->saveIntegrations($request)),
            'theme' => $this->handleOptional('theme', fn () => $this->saveTheme($request)),
            'memory' => $this->handleOptional('memory', fn () => $this->saveMemory($request)),
            'backup' => $this->handleOptional('backup', fn () => $this->saveBackup($request)),
            'features' => $this->handleOptional('features', fn () => $this->saveFeatures($request)),
            'demo' => $this->handleDemo($request),
            default => to_route('install'),
        };
    }

    /**
     * Test a connection for the given installer step (database, infrastructure, mail, search, ai).
     * Expects step + form fields in the request. Returns JSON { "ok": true } or { "ok": false, "message": "..." }.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate(['step' => ['required', 'in:database,infrastructure,mail,search,ai,broadcasting']]);

        $step = $request->input('step');

        try {
            match ($step) {
                'database' => $this->testDatabaseConnection($request),
                'infrastructure' => $this->testInfrastructureConnection($request),
                'mail' => $this->testMailConnection($request),
                'search' => $this->testSearchConnection($request),
                'ai' => $this->testAiConnection($request),
                'broadcasting' => $this->testBroadcastingConnection($request),
            };

            return response()->json(['ok' => true]);
        } catch (Throwable $throwable) {
            return response()->json(['ok' => false, 'message' => $throwable->getMessage()], 422);
        }
    }

    /**
     * Return list of AI models for the installer default-model combobox.
     * GET: returns static curated list. POST with provider + api_key: for OpenRouter, can return live list from API.
     */
    public function aiModels(Request $request): JsonResponse
    {
        $models = config('install-ai-models', []);

        if ($request->isMethod('POST')) {
            $provider = $request->input('provider');
            $apiKey = $request->filled('api_key') ? (string) $request->input('api_key') : null;
            if ($provider === 'openrouter' && $apiKey !== null && $apiKey !== '') {
                $live = $this->fetchOpenRouterModels($apiKey);
                if ($live !== null && $live !== []) {
                    $models = $live;
                }
            }
        }

        return response()->json(['models' => $models]);
    }

    /**
     * Fetch models from OpenRouter API. Returns null on failure.
     *
     * @return array<int, array{id: string, name: string, pricing: string, free: bool}>|null
     */
    private function fetchOpenRouterModels(string $apiKey): ?array
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->get('https://openrouter.ai/api/v1/models');

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json('data');
            if (! is_array($data)) {
                return null;
            }

            $out = [];
            $seen = [];
            foreach ($data as $m) {
                $id = $m['id'] ?? null;
                if (! is_string($id) || $id === '' || isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $pricing = $m['pricing'] ?? [];
                $promptPerToken = (float) ($pricing['prompt'] ?? 1);
                $completionPerToken = (float) ($pricing['completion'] ?? 1);
                $free = $promptPerToken === 0.0 && $completionPerToken === 0.0;
                $pricingStr = $free ? 'Free' : sprintf('$%.2f/1M in, $%.2f/1M out', $promptPerToken * 1_000_000, $completionPerToken * 1_000_000);
                $out[] = [
                    'id' => $id,
                    'name' => $m['name'] ?? $id,
                    'pricing' => $pricingStr,
                    'free' => $free,
                ];
            }

            usort($out, static function (array $a, array $b): int {
                if ($a['free'] !== $b['free']) {
                    return $a['free'] ? -1 : 1;
                }

                return strcasecmp($a['name'], $b['name']);
            });

            return array_values($out);
        } catch (Throwable) {
            return null;
        }
    }

    // ─── Express install worker ───────────────────────────────────────────────

    /**
     * Resolve express install options from request. Accepts tenancy, demo, single_org_name,
     * optional preset (saas, internal, ai_first), and optional AI provider/key/model.
     *
     * @return array{tenancy: string, demo: string, single_org_name?: string, site_name?: string, ai_provider?: string, ai_api_key?: string, ai_model?: string, locale?: string, fallback_locale?: string}
     */
    private function resolveExpressOptions(Request $request): array
    {
        $tenancy = $request->input('tenancy');
        $demo = $request->input('demo');
        $preset = $request->input('preset');
        $singleOrgName = $request->input('single_org_name');

        if (in_array($preset, ['saas', 'internal', 'ai_first'], true)) {
            if (! in_array($tenancy, ['single', 'multi'], true)) {
                $tenancy = $preset === 'internal' ? 'single' : 'multi';
            }

            if (! in_array($demo, ['none', 'minimal', 'full'], true)) {
                $demo = $preset === 'ai_first' ? 'minimal' : 'none';
            }
        }

        $tenancy = in_array($tenancy, ['single', 'multi'], true) ? $tenancy : 'multi';
        $demo = in_array($demo, ['none', 'minimal', 'full'], true) ? $demo : 'none';

        $options = ['tenancy' => $tenancy, 'demo' => $demo];
        if ($tenancy === 'single' && is_string($singleOrgName) && Str::length(mb_trim($singleOrgName)) > 0) {
            $options['single_org_name'] = mb_trim($singleOrgName);
        }

        $siteName = $request->input('site_name');
        if (is_string($siteName) && Str::length(mb_trim($siteName)) > 0) {
            $options['site_name'] = mb_trim($siteName);
        }

        $locale = $request->input('locale');
        if (is_string($locale) && Str::length(mb_trim($locale)) > 0) {
            $options['locale'] = mb_trim($locale);
        }

        $fallbackLocale = $request->input('fallback_locale');
        if (is_string($fallbackLocale) && Str::length(mb_trim($fallbackLocale)) > 0) {
            $options['fallback_locale'] = mb_trim($fallbackLocale);
        }

        $aiProvider = $request->input('ai_provider');
        if (is_string($aiProvider) && $aiProvider !== '') {
            $options['ai_provider'] = $aiProvider;
            $apiKey = $request->input('ai_api_key');
            if (is_string($apiKey) && $apiKey !== '') {
                $options['ai_api_key'] = $apiKey;
            }

            $model = $request->input('ai_model');
            if (is_string($model) && Str::length(mb_trim($model)) > 0) {
                $options['ai_model'] = mb_trim($model);
            }
        }

        $thesysKey = $request->input('thesys_api_key');
        if (is_string($thesysKey) && Str::length(mb_trim($thesysKey)) > 0) {
            $options['thesys_api_key'] = mb_trim($thesysKey);
        }

        return $options;
    }

    /**
     * @param  array{tenancy?: string, demo?: string, single_org_name?: string, site_name?: string, locale?: string, fallback_locale?: string, ai_provider?: string, ai_api_key?: string, ai_model?: string, thesys_api_key?: string}  $options
     */
    private function runExpressInstallSteps(string $progressFile, string $appUrl, array $options = []): void
    {
        try {
            $this->writeProgress($progressFile, 'migrate', 'running');
            Artisan::call('migrate', ['--force' => true]);
            $this->writeProgress($progressFile, 'migrate', 'done');

            $this->writeProgress($progressFile, 'roles', 'running');
            Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\RolesAndPermissionsSeeder::class, '--force' => true]);
            $this->writeProgress($progressFile, 'roles', 'done');

            $this->writeProgress($progressFile, 'gamification', 'running');
            Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\GamificationSeeder::class, '--force' => true]);
            $this->writeProgress($progressFile, 'gamification', 'done');

            $this->writeProgress($progressFile, 'mail_tpl', 'running');
            Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\MailTemplatesSeeder::class, '--force' => true]);
            $this->writeProgress($progressFile, 'mail_tpl', 'done');

            $this->writeProgress($progressFile, 'governor', 'running');
            try {
                Artisan::call('db:seed', ['--class' => \GeneaLabs\LaravelGovernor\Database\Seeders\LaravelGovernorDatabaseSeeder::class, '--force' => true]);
            } catch (Throwable) {
                // Non-fatal — Governor may not be used
            }

            $this->writeProgress($progressFile, 'governor', 'done');

            $this->writeProgress($progressFile, 'admin', 'running');
            // Use raw DB insert to bypass ALL model events/observers (Scout, userstamps, etc.)
            // which may hang in the terminating callback due to missing auth/tenant context.
            $now = now()->toDateTimeString();
            $userId = DB::table('users')->insertGetId([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
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
                    'model_type' => User::class,
                    'model_id' => $userId,
                    'organization_id' => 0,
                ]);
            }

            $this->writeProgress($progressFile, 'admin', 'done');

            $this->writeProgress($progressFile, 'settings', 'running');

            $app = resolve(AppSettings::class);
            $app->site_name = Str::length((string) ($options['site_name'] ?? '')) > 0
                ? (string) $options['site_name']
                : 'My App';
            $app->url = $appUrl;
            $app->timezone = 'UTC';
            if (isset($options['locale']) && $options['locale'] !== '') {
                $app->locale = $options['locale'];
            }

            if (isset($options['fallback_locale']) && $options['fallback_locale'] !== '') {
                $app->fallback_locale = $options['fallback_locale'];
            }

            $app->save();

            $this->ensureMailSettingsExist();
            App::forgetInstance(MailSettings::class);

            $mail = resolve(MailSettings::class);
            $mail->mailer = 'smtp';
            $mail->smtp_host = '127.0.0.1';
            $mail->smtp_port = 2525;
            $mail->smtp_username = $app->site_name;
            $mail->smtp_password = null;
            $mail->smtp_encryption = null;
            $mail->from_address = 'hello@example.com';
            $mail->from_name = $app->site_name;
            $mail->save();

            $wizard = resolve(SetupWizardSettings::class);
            $wizard->setup_completed = true;
            $wizard->completed_steps = ['app', 'mail', 'billing', 'ai', 'complete'];
            $wizard->save();

            $tenancy = resolve(TenancySettings::class);
            if (($options['tenancy'] ?? 'multi') === 'single') {
                $tenancy->enabled = false;
                $tenancy->allow_user_org_creation = false;
                $tenancy->default_org_name = Str::length((string) ($options['single_org_name'] ?? '')) > 0
                    ? (string) $options['single_org_name']
                    : 'My Organization';
            }

            $tenancy->save();

            $provider = $options['ai_provider'] ?? null;
            if (is_string($provider) && $provider !== '') {
                $prism = resolve(PrismSettings::class);
                $prism->default_provider = $provider;
                $prism->default_model = (string) ($options['ai_model'] ?? '');
                $apiKey = $options['ai_api_key'] ?? null;
                if (is_string($apiKey) && $apiKey !== '' && $provider !== 'ollama') {
                    match ($provider) {
                        'openrouter' => $prism->openrouter_api_key = $apiKey,
                        'openai' => $prism->openai_api_key = $apiKey,
                        'anthropic' => $prism->anthropic_api_key = $apiKey,
                        'groq' => $prism->groq_api_key = $apiKey,
                        'gemini' => $prism->gemini_api_key = $apiKey,
                        'xai' => $prism->xai_api_key = $apiKey,
                        'deepseek' => $prism->deepseek_api_key = $apiKey,
                        'mistral' => $prism->mistral_api_key = $apiKey,
                        default => null,
                    };
                }

                $prism->save();
            }

            $thesysKey = $options['thesys_api_key'] ?? null;
            if (is_string($thesysKey) && $thesysKey !== '') {
                $envPath = base_path('.env');
                $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';
                $env = $this->setEnvVar($env, 'THESYS_API_KEY', $thesysKey);
                file_put_contents($envPath, $env);
            }

            $demo = $options['demo'] ?? 'none';
            if ($demo !== 'none') {
                $this->writeProgress($progressFile, 'demo', 'running');
                $modules = $demo === 'minimal' ? ['users', 'organizations', 'content'] : array_keys(self::MODULES);
                foreach ($modules as $key) {
                    $module = self::MODULES[$key] ?? null;
                    if ($module === null) {
                        continue;
                    }

                    foreach ($module['seeders'] as $seederClass) {
                        try {
                            Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
                        } catch (Throwable) {
                            // Non-fatal
                        }
                    }
                }

                $this->writeProgress($progressFile, 'demo', 'done');
            }

            SettingsOverlayServiceProvider::applyOverlay();

            $this->writeProgress($progressFile, 'settings', 'done');

            session()->put('show_next_steps', true);

            $loginToken = Crypt::encrypt([
                'user_id' => $userId,
                'expires' => time() + 300,
            ]);
            $redirectUrl = url()->route('install.complete', ['token' => $loginToken], true);

            $this->writeProgressFile($progressFile, [
                'status' => 'done',
                'steps' => $this->readProgressFile($progressFile)['steps'] ?? [],
                'redirect' => $redirectUrl,
            ]);

        } catch (Throwable $throwable) {
            $this->writeProgressFile($progressFile, [
                'status' => 'error',
                'message' => $throwable->getMessage(),
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
                'db_host' => ['required', 'string'],
                'db_port' => ['required', 'numeric'],
                'db_database' => ['required', 'string'],
                'db_username' => ['required', 'string'],
                'db_password' => ['nullable', 'string'],
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
                sprintf('database.connections.%s.host', $driver) => $validated['db_host'],
                sprintf('database.connections.%s.port', $driver) => $validated['db_port'],
                sprintf('database.connections.%s.database', $driver) => $validated['db_database'],
                sprintf('database.connections.%s.username', $driver) => $validated['db_username'],
                sprintf('database.connections.%s.password', $driver) => $validated['db_password'] ?? '',
            ]);

            DB::purge();
        }

        if (! $this->isDatabaseReachable()) {
            return to_route('install')
                ->withErrors(['db' => 'Could not connect. Please check your credentials.'])
                ->withInput();
        }

        session(['install_database_done' => true]);

        return to_route('install');
    }

    private function handleMigrate(): RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\RolesAndPermissionsSeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\GamificationSeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\MailTemplatesSeeder::class, '--force' => true]);
        try {
            Artisan::call('db:seed', ['--class' => \GeneaLabs\LaravelGovernor\Database\Seeders\LaravelGovernorDatabaseSeeder::class, '--force' => true]);
        } catch (Throwable) {
            // Non-fatal
        }

        session(['install_migrate_done' => true]);

        return to_route('install');
    }

    private function writeMigrateProgress(string $progressFile, string $message, bool $done, ?string $error = null): void
    {
        $data = ['message' => $message, 'done' => $done];
        if ($error !== null) {
            $data['error'] = $error;
        }

        file_put_contents($progressFile, json_encode($data), LOCK_EX);
    }

    private function runMigrateStepsWithProgress(string $progressFile): void
    {
        try {
            $this->writeMigrateProgress($progressFile, 'Running migrations…', false);
            Artisan::call('migrate', ['--force' => true]);

            $this->writeMigrateProgress($progressFile, 'Seeding roles & permissions…', false);
            Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\RolesAndPermissionsSeeder::class, '--force' => true]);

            $this->writeMigrateProgress($progressFile, 'Seeding gamification levels…', false);
            Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\GamificationSeeder::class, '--force' => true]);

            $this->writeMigrateProgress($progressFile, 'Seeding email templates…', false);
            Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\MailTemplatesSeeder::class, '--force' => true]);

            $this->writeMigrateProgress($progressFile, 'Seeding Governor…', false);
            try {
                Artisan::call('db:seed', ['--class' => \GeneaLabs\LaravelGovernor\Database\Seeders\LaravelGovernorDatabaseSeeder::class, '--force' => true]);
            } catch (Throwable) {
                // Non-fatal
            }

            session(['install_migrate_done' => true]);
            $this->writeMigrateProgress($progressFile, 'Done.', true);
            file_put_contents(storage_path('app/install_migrate_completed'), (string) time(), LOCK_EX);
        } catch (Throwable $throwable) {
            $this->writeMigrateProgress($progressFile, $throwable->getMessage(), true, $throwable->getMessage());
        }
    }

    /**
     * When migrate runs in a terminating callback, session is not persisted. A sentinel file is written
     * instead; sync it into the session on the next request so the wizard advances.
     */
    private function syncMigrateDoneFromTerminatingCallback(): void
    {
        $sentinel = storage_path('app/install_migrate_completed');
        if (is_file($sentinel)) {
            session(['install_migrate_done' => true]);
            @unlink($sentinel);
        }
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

        $now = now()->toDateTimeString();
        $userId = DB::table('users')->insertGetId([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $superAdminRole = DB::table('roles')->where('name', 'super-admin')->whereNull('organization_id')->first();
        if ($superAdminRole) {
            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $superAdminRole->id,
                'model_type' => User::class,
                'model_id' => $userId,
                'organization_id' => 0,
            ]);
        }

        return to_route('install');
    }

    private function handleApp(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'site_name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'locale' => ['nullable', 'string', 'max:12'],
            'fallback_locale' => ['nullable', 'string', 'max:12'],
            'preset' => ['nullable', 'string', 'in:none,saas,internal,ai_first'],
        ])->validate();

        if (! empty($validated['preset'])) {
            session()->put('install_preset', $validated['preset']);
        }

        $app = resolve(AppSettings::class);
        $app->site_name = $validated['site_name'];
        $app->url = $validated['url'];
        $app->timezone = $validated['timezone'] ?? 'UTC';
        $app->locale = $validated['locale'] ?? 'en';
        $app->fallback_locale = $validated['fallback_locale'] ?? 'en';
        $app->save();

        $mail = resolve(MailSettings::class);
        $mail->from_name = $validated['site_name'];
        $mail->save();

        return to_route('install');
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

        session()->put('show_next_steps', true);

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

        return to_route('install');
    }

    // ─── Optional step save closures ─────────────────────────────────────────

    private function saveTenancy(Request $request): void
    {
        $tenancy = resolve(TenancySettings::class);
        $enabled = $request->boolean('enabled', true);
        $tenancy->enabled = $enabled;
        $tenancy->default_org_name = (string) $request->input('default_org_name', "{name}'s Workspace");
        if (! $enabled) {
            $tenancy->allow_user_org_creation = false;
            $singleOrgName = (string) $request->input('single_org_name', '');
            if ($singleOrgName !== '') {
                $tenancy->default_org_name = $singleOrgName;
            }
        } else {
            $tenancy->allow_user_org_creation = $request->boolean('allow_user_org_creation', true);
        }

        $tenancy->auto_create_personal_org = $request->boolean('auto_create_personal_org_for_admins', true);
        $tenancy->auto_create_personal_org_for_admins = $request->boolean('auto_create_personal_org_for_admins', true);
        $tenancy->auto_create_personal_org_for_members = $request->boolean('auto_create_personal_org_for_members', false);

        if ($enabled) {
            $tenancy->domain = $request->filled('domain') ? (string) $request->input('domain') : null;
            $tenancy->subdomain_resolution = $request->boolean('subdomain_resolution', true);
            $tenancy->term = (string) $request->input('term', 'Organization');
            $tenancy->term_plural = (string) $request->input('term_plural', 'Organizations');
            $tenancy->invitation_expires_in_days = (int) $request->input('invitation_expires_in_days', 7);
            $tenancy->invitation_allow_registration = $request->boolean('invitation_allow_registration', true);
            $tenancy->sharing_restrict_to_connected = $request->boolean('sharing_restrict_to_connected', false);
            $tenancy->sharing_edit_ownership = (string) $request->input('sharing_edit_ownership', 'original_owner');
            $tenancy->super_admin_can_view_all = $request->boolean('super_admin_can_view_all', true);
            $tenancy->super_admin_default_share_new_to_all_orgs = $request->boolean('super_admin_default_share_new_to_all_orgs', true);
        }

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

        $this->ensureMailSettingsExist();
        App::forgetInstance(MailSettings::class);

        try {
            $mail = resolve(MailSettings::class);
        } catch (Throwable $throwable) {
            if ($this->isDecryptUnserializeError($throwable)) {
                $this->fixMailSettingsSmtpPasswordPayload();
                App::forgetInstance(MailSettings::class);
                $mail = resolve(MailSettings::class);
            } else {
                throw $throwable;
            }
        }

        $mail->mailer = $properties['mailer'];
        $mail->smtp_host = $properties['smtp_host'];
        $mail->smtp_port = $properties['smtp_port'];
        $mail->smtp_username = $properties['smtp_username'];
        $mail->smtp_password = $properties['smtp_password'];
        $mail->smtp_encryption = $properties['smtp_encryption'];
        $mail->from_address = $properties['from_address'];
        $mail->from_name = $properties['from_name'];
        $mail->save();
    }

    private function isDecryptUnserializeError(Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'unserialize()') || str_contains($message, 'decrypt');
    }

    private function fixMailSettingsSmtpPasswordPayload(): void
    {
        DB::table('settings')
            ->where('group', MailSettings::group())
            ->where('name', 'smtp_password')
            ->update(['payload' => json_encode(Crypt::encrypt(null)), 'updated_at' => now()]);
    }

    private function ensureMailSettingsExist(): void
    {
        $now = now();

        $defaults = [
            ['name' => 'mailer',           'payload' => '"log"'],
            ['name' => 'smtp_host',        'payload' => '"127.0.0.1"'],
            ['name' => 'smtp_port',        'payload' => '2525'],
            ['name' => 'smtp_username',    'payload' => 'null'],
            ['name' => 'smtp_password',    'payload' => json_encode(Crypt::encrypt(null))],
            ['name' => 'smtp_encryption',  'payload' => 'null'],
            ['name' => 'from_address',     'payload' => '"hello@example.com"'],
            ['name' => 'from_name',        'payload' => '"Example"'],
        ];

        foreach ($defaults as $row) {
            DB::table('settings')->insertOrIgnore([
                'group' => MailSettings::group(),
                'name' => $row['name'],
                'payload' => $row['payload'],
                'locked' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
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

        $scout->prefix = (string) $request->input('prefix', '');
        $scout->queue = $request->boolean('queue', false);
        $scout->identify = $request->boolean('identify', false);

        $scout->save();
    }

    private function saveAi(Request $request): void
    {
        $provider = (string) $request->input('provider', '');
        $apiKey = $request->filled('api_key') ? (string) $request->input('api_key') : null;

        $prism = resolve(PrismSettings::class);
        if ($provider !== '') {
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

        $ai = resolve(AiSettings::class);
        if ($request->filled('cohere_api_key')) {
            $cohereKey = (string) $request->input('cohere_api_key');
            $ai->cohere_api_key = $cohereKey;
            $ai->save();
            $envPath = base_path('.env');
            $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'COHERE_API_KEY', $cohereKey);
            file_put_contents($envPath, $env);
        }

        if ($request->filled('jina_api_key')) {
            $jinaKey = (string) $request->input('jina_api_key');
            $ai->jina_api_key = $jinaKey;
            $ai->save();
            $envPath = base_path('.env');
            $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'JINA_API_KEY', $jinaKey);
            file_put_contents($envPath, $env);
        }

        if ($request->filled('thesys_api_key')) {
            $thesysKey = (string) $request->input('thesys_api_key');
            $ai->thesys_api_key = $thesysKey;
            $ai->save();
            $envPath = base_path('.env');
            $env = is_file($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'THESYS_API_KEY', $thesysKey);
            file_put_contents($envPath, $env);
        }
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

    private function saveBilling(Request $request): void
    {
        $billing = resolve(BillingSettings::class);
        $billing->default_gateway = (string) $request->input('default_gateway', 'stripe');
        if ($billing->default_gateway === '') {
            $billing->default_gateway = 'none';
        }
        $billing->currency = (string) $request->input('currency', 'usd');
        $billing->trial_days = (int) $request->input('trial_days', 14);
        $billing->enable_seat_based_billing = $request->boolean('enable_seat_based_billing', false);
        $billing->allow_multiple_subscriptions = $request->boolean('allow_multiple_subscriptions', false);
        $billing->credit_expiration_days = (int) $request->input('credit_expiration_days', 365);

        $dunningRaw = $request->input('dunning_intervals', '');
        if (is_string($dunningRaw) && mb_trim($dunningRaw) !== '') {
            $parts = array_map('intval', preg_split('/[\s,]+/', mb_trim($dunningRaw)) ?: []);
            $parts = array_values(array_filter($parts));
            if ($parts !== []) {
                $billing->dunning_intervals = $parts;
            }
        }

        $billing->geo_restriction_enabled = $request->boolean('geo_restriction_enabled', false);
        if ($billing->geo_restriction_enabled && $request->filled('geo_blocked_countries')) {
            $billing->geo_blocked_countries = array_values(array_filter(preg_split('/[\s,]+/', (string) $request->input('geo_blocked_countries')) ?: []));
        }
        if ($billing->geo_restriction_enabled && $request->filled('geo_allowed_countries')) {
            $billing->geo_allowed_countries = array_values(array_filter(preg_split('/[\s,]+/', (string) $request->input('geo_allowed_countries')) ?: []));
        }

        $billing->save();

        if ($request->filled('stripe_key')) {
            $stripe = resolve(StripeSettings::class);
            $stripe->key = (string) $request->input('stripe_key');
            if ($request->filled('stripe_secret')) {
                $stripe->secret = (string) $request->input('stripe_secret');
            }
            if ($request->filled('stripe_webhook_secret')) {
                $stripe->webhook_secret = (string) $request->input('stripe_webhook_secret');
            }
            $stripe->save();
        }

        if ($request->filled('paddle_vendor_id')) {
            $paddle = resolve(PaddleSettings::class);
            $paddle->vendor_id = (string) $request->input('paddle_vendor_id');
            if ($request->filled('paddle_vendor_auth_code')) {
                $paddle->vendor_auth_code = (string) $request->input('paddle_vendor_auth_code');
            }
            if ($request->filled('paddle_public_key')) {
                $paddle->public_key = (string) $request->input('paddle_public_key');
            }
            if ($request->filled('paddle_webhook_secret')) {
                $paddle->webhook_secret = (string) $request->input('paddle_webhook_secret');
            }
            $paddle->sandbox = $request->boolean('paddle_sandbox', true);
            $paddle->save();
        }

        if ($request->filled('lemon_squeezy_api_key')) {
            $lemon = resolve(LemonSqueezySettings::class);
            $lemon->api_key = (string) $request->input('lemon_squeezy_api_key');
            if ($request->filled('lemon_squeezy_signing_secret')) {
                $lemon->signing_secret = (string) $request->input('lemon_squeezy_signing_secret');
            }
            if ($request->filled('lemon_squeezy_store')) {
                $lemon->store = (string) $request->input('lemon_squeezy_store');
            }
            $lemon->path = (string) $request->input('lemon_squeezy_path', 'lemon-squeezy');
            $lemon->currency_locale = (string) $request->input('lemon_squeezy_currency_locale', 'en');
            if ($request->filled('lemon_squeezy_generic_variant_id')) {
                $lemon->generic_variant_id = (string) $request->input('lemon_squeezy_generic_variant_id');
            }
            $lemon->save();
        }
    }

    private function saveIntegrations(Request $request): void
    {
        $integrations = resolve(IntegrationsSettings::class);
        if ($request->filled('slack_webhook_url')) {
            $integrations->slack_webhook_url = (string) $request->input('slack_webhook_url');
        }
        if ($request->filled('slack_bot_token')) {
            $integrations->slack_bot_token = (string) $request->input('slack_bot_token');
        }
        if ($request->filled('slack_channel')) {
            $integrations->slack_channel = (string) $request->input('slack_channel');
        }
        if ($request->filled('postmark_token')) {
            $integrations->postmark_token = (string) $request->input('postmark_token');
        }
        if ($request->filled('resend_key')) {
            $integrations->resend_key = (string) $request->input('resend_key');
        }
        $integrations->save();
    }

    private function saveTheme(Request $request): void
    {
        $theme = resolve(ThemeSettings::class);
        $theme->preset = (string) $request->input('preset', 'default');
        $theme->base_color = (string) $request->input('base_color', 'neutral');
        $theme->radius = (string) $request->input('radius', 'default');
        $theme->font = (string) $request->input('font', 'instrument-sans');
        $theme->default_appearance = (string) $request->input('default_appearance', 'system');
        $theme->dark_color_scheme = (string) $request->input('dark_color_scheme', '');
        $theme->primary_color = (string) $request->input('primary_color', '');
        $theme->light_color_scheme = (string) $request->input('light_color_scheme', '');
        $theme->card_skin = (string) $request->input('card_skin', 'shadow');
        $theme->border_radius = (string) $request->input('border_radius', 'default');
        $theme->sidebar_layout = (string) $request->input('sidebar_layout', 'main');
        $theme->menu_color = (string) $request->input('menu_color', 'default');
        $theme->menu_accent = (string) $request->input('menu_accent', 'subtle');
        $theme->allow_user_theme_customization = $request->boolean('allow_user_theme_customization', true);
        $theme->allow_user_logo_upload = $request->boolean('allow_user_logo_upload', false);
        $theme->save();
    }

    private function saveMemory(Request $request): void
    {
        $memory = resolve(MemorySettings::class);
        $memory->dimensions = (int) $request->input('dimensions', 1536);
        $memory->similarity_threshold = (float) $request->input('similarity_threshold', 0.5);
        $memory->recall_limit = (int) $request->input('recall_limit', 10);
        $memory->middleware_recall_limit = (int) $request->input('middleware_recall_limit', 5);
        $memory->recall_oversample_factor = (int) $request->input('recall_oversample_factor', 2);
        $memory->table = (string) $request->input('table', 'memories');
        $memory->save();
    }

    private function saveBackup(Request $request): void
    {
        $backup = resolve(BackupSettings::class);
        $backup->name = (string) $request->input('name', 'laravel-backup');
        $backup->keep_all_backups_for_days = (int) $request->input('keep_all_backups_for_days', 7);
        $backup->keep_daily_backups_for_days = (int) $request->input('keep_daily_backups_for_days', 16);
        $backup->keep_weekly_backups_for_weeks = (int) $request->input('keep_weekly_backups_for_weeks', 8);
        $backup->keep_monthly_backups_for_months = (int) $request->input('keep_monthly_backups_for_months', 4);
        $backup->keep_yearly_backups_for_years = (int) $request->input('keep_yearly_backups_for_years', 2);
        $backup->delete_oldest_when_size_mb = (int) $request->input('delete_oldest_when_size_mb', 5000);
        $backup->save();
    }

    private function saveFeatures(Request $request): void
    {
        $allKeys = array_keys(config('feature-flags.inertia_features', []));
        $enabledRaw = $request->input('feature_enabled', []);
        $enabled = is_array($enabledRaw) ? array_keys(array_filter($enabledRaw)) : [];
        $disabled = array_values(array_diff($allKeys, $enabled));

        $settings = resolve(FeatureFlagSettings::class);
        $settings->globally_disabled_modules = $disabled;
        $settings->save();
    }

    /**
     * Feature keys and labels for the install feature-flags step (super-admin level).
     *
     * @return array<int, array{key: string, label: string}>
     */
    private function installFeatureFlagsList(): array
    {
        $labels = [
            'registration' => 'Registration (public sign-up)',
            'api_access' => 'API access',
            'blog' => 'Blog',
            'changelog' => 'Changelog',
            'help' => 'Help center',
            'contact' => 'Contact form',
            'onboarding' => 'Onboarding wizard',
            'two_factor_auth' => 'Two-factor authentication',
            'impersonation' => 'User impersonation (super-admin)',
            'personal_data_export' => 'Personal data export',
            'cookie_consent' => 'Cookie consent banner',
            'profile_pdf_export' => 'Profile PDF export',
            'scramble_api_docs' => 'Scramble API docs',
            'appearance_settings' => 'Appearance settings',
            'gamification' => 'Gamification (badges, points)',
        ];
        $keys = array_keys(config('feature-flags.inertia_features', []));
        $list = [];
        foreach ($keys as $key) {
            $list[] = ['key' => $key, 'label' => $labels[$key] ?? str_replace('_', ' ', ucfirst((string) $key))];
        }

        return $list;
    }

    // ─── Step resolver ────────────────────────────────────────────────────────

    private function resolveStep(): string
    {
        if (! session('install_database_done', false)) {
            return 'database';
        }

        if (! $this->isDatabaseReachable()) {
            return 'database';
        }

        if (! session('install_migrate_done', false) || ! $this->migrationsRan()) {
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
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
        ])->validate();

        config([
            'database.default' => $driver,
            sprintf('database.connections.%s.host', $driver) => $validated['db_host'],
            sprintf('database.connections.%s.port', $driver) => $validated['db_port'],
            sprintf('database.connections.%s.database', $driver) => $validated['db_database'],
            sprintf('database.connections.%s.username', $driver) => $validated['db_username'],
            sprintf('database.connections.%s.password', $driver) => $validated['db_password'] ?? '',
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
            ], fn ($v): bool => $v !== null),
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
        throw_if($socket === false, RuntimeException::class, sprintf('Could not reach SMTP server %s:%d — %s (errno %d)', $host, $port, $errstr, $errno));

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

        throw_if($apiKey === '', RuntimeException::class, 'Typesense API key is required.');

        $url = sprintf('%s://%s:%d/health', $protocol, $host, $port);
        $response = Http::withHeaders(['X-TYPESENSE-API-KEY' => $apiKey])
            ->timeout(5)
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf('Typesense returned HTTP %d.', $response->status()));
        }
    }

    private function testBroadcastingConnection(Request $request): void
    {
        $host = (string) $request->input('reverb_host', 'reverb.herd.test');
        $port = (int) $request->input('reverb_port', 443);
        $scheme = (string) $request->input('reverb_scheme', 'https');

        $url = sprintf('%s://%s:%d/up', $scheme, $host, $port);
        $client = Http::timeout(5);
        if ($scheme === 'https' && (str_ends_with($host, '.test') || str_ends_with($host, '.local') || $host === 'localhost')) {
            $client = $client->withOptions(['verify' => false]);
        }
        $response = $client->get($url);

        if ($response->successful()) {
            return;
        }

        if ($host === 'reverb.herd.test') {
            $dashboardClient = Http::timeout(5)->withOptions(['verify' => false]);
            $dashboardResponse = $dashboardClient->get('http://reverb-dashboard.herd.test/');
            if ($dashboardResponse->successful()) {
                return;
            }
        }

        throw new RuntimeException(sprintf('Reverb returned HTTP %d at %s. Is Reverb running? Dashboard: http://reverb-dashboard.herd.test/', $response->status(), $host));
    }

    private function testAiConnection(Request $request): void
    {
        $aiTest = $request->input('ai_test', 'provider');

        if ($aiTest === 'thesys') {
            $apiKey = $request->filled('thesys_api_key') ? (string) $request->input('thesys_api_key') : '';
            throw_if($apiKey === '', RuntimeException::class, 'Thesys API key is required to test.');

            return;
        }

        if ($aiTest === 'cohere') {
            $apiKey = $request->filled('cohere_api_key') ? (string) $request->input('cohere_api_key') : '';
            throw_if($apiKey === '', RuntimeException::class, 'Cohere API key is required to test.');

            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post('https://api.cohere.com/v1/check-api-key');

            if (! $response->successful()) {
                $status = $response->status();
                $body = $response->json();
                $message = $body['message'] ?? $response->body();
                throw new RuntimeException(sprintf('Cohere API returned HTTP %d. %s', $status, is_string($message) ? $message : ''));
            }

            return;
        }

        $provider = (string) $request->input('provider', '');
        $apiKey = $request->filled('api_key') ? (string) $request->input('api_key') : '';

        if ($provider === '' || $provider === 'ollama') {
            if ($provider === 'ollama') {
                $response = Http::timeout(3)->get('http://localhost:11434/api/tags');
                if (! $response->successful()) {
                    throw new RuntimeException('Cannot reach Ollama at localhost:11434. Is Ollama running?');
                }
            }

            return;
        }

        throw_if($apiKey === '', RuntimeException::class, 'API key is required to test this provider.');

        $response = match ($provider) {
            'openrouter' => Http::withToken($apiKey)->timeout(10)->get('https://openrouter.ai/api/v1/auth/key'),
            'openai' => Http::withToken($apiKey)->timeout(10)->get('https://api.openai.com/v1/models'),
            'groq' => Http::withToken($apiKey)->timeout(10)->get('https://api.groq.com/openai/v1/models'),
            'anthropic', 'gemini', 'xai', 'deepseek', 'mistral' => throw new RuntimeException(
                'Key validation is not available for this provider. Save and continue to use it.'
            ),
            default => throw new RuntimeException('Unknown provider.'),
        };

        if (! $response->successful()) {
            $status = $response->status();
            $body = $response->json();
            $message = $body['error']['message'] ?? $body['message'] ?? $response->body();
            throw new RuntimeException(sprintf('API returned HTTP %d. %s', $status, is_string($message) ? $message : ''));
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
        $line = sprintf('%s=%s', $key, $escaped);

        if (preg_match(sprintf('/^%s=.*/m', $key), $env)) {
            return (string) preg_replace(sprintf('/^%s=.*/m', $key), $line, $env);
        }

        return mb_rtrim($env).(PHP_EOL.$line.PHP_EOL);
    }

    private function removeEnvVar(string $env, string $key): string
    {
        return (string) preg_replace("/^{$key}=.*\n?/m", '', $env);
    }
}
