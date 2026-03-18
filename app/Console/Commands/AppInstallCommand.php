<?php

declare(strict_types=1);

namespace App\Console\Commands;

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
use App\Settings\InfrastructureSettings;
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
use App\Support\AssignRoleViaDb;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

final class AppInstallCommand extends Command
{
    // ─── Phase keys (used for resume tracking) ────────────────────────────────

    private const string PHASE_PREFLIGHT = 'preflight';

    private const string PHASE_DATABASE = 'database';

    private const string PHASE_MIGRATIONS = 'migrations';

    private const string PHASE_SEEDERS = 'seeders';

    private const string PHASE_ADMIN = 'admin';

    private const string PHASE_APP = 'app';

    private const string PHASE_TENANCY = 'tenancy';

    private const string PHASE_INFRA = 'infrastructure';

    private const string PHASE_MAIL = 'mail';

    private const string PHASE_SEARCH = 'search';

    private const string PHASE_AI = 'ai';

    private const string PHASE_SOCIAL = 'social';

    private const string PHASE_STORAGE = 'storage';

    private const string PHASE_BROADCASTING = 'broadcasting';

    private const string PHASE_SEO = 'seo';

    private const string PHASE_MONITORING = 'monitoring';

    private const string PHASE_BILLING = 'billing';

    private const string PHASE_INTEGRATIONS = 'integrations';

    private const string PHASE_THEME = 'theme';

    private const string PHASE_MEMORY = 'memory';

    private const string PHASE_BACKUP = 'backup';

    private const string PHASE_FEATURES = 'features';

    private const string PHASE_DEMO = 'demo';

    private const string PROGRESS_FILE = '.install-progress.json';

    // ─── Module definitions (keep in sync with InstallController::MODULES) ───

    /** @var array<string, array{label: string, description: string, seeders: list<string>}> */
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

    protected $signature = 'app:install
                            {--non-interactive : Skip prompts, use option values or defaults}
                            {--resume : Resume from last checkpoint, skip completed phases}
                            {--fresh : Drop and re-run all migrations (WARNING: destroys all data)}
                            {--site-name= : Application name}
                            {--url= : Application URL}
                            {--admin-name= : Admin user full name}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}
                            {--mail-mailer= : Mail driver (log|smtp|ses|postmark|resend)}
                            {--mail-from= : From email address}
                            {--mail-from-name= : From name}
                            {--google-client-id= : Google OAuth client ID}
                            {--google-client-secret= : Google OAuth client secret}
                            {--github-client-id= : GitHub OAuth client ID}
                            {--github-client-secret= : GitHub OAuth client secret}
                            {--s3-disk= : Storage disk (local|s3)}
                            {--s3-key= : S3 access key ID}
                            {--s3-secret= : S3 secret access key}
                            {--s3-region= : S3 region (default: us-east-1)}
                            {--s3-bucket= : S3 bucket name}
                            {--s3-url= : S3 custom endpoint URL}
                            {--reverb-app-id= : Reverb App ID}
                            {--reverb-app-key= : Reverb App key}
                            {--reverb-app-secret= : Reverb App secret}
                            {--sentry-dsn= : Sentry DSN for error tracking}
                            {--meta-title= : SEO meta title}
                            {--meta-description= : SEO meta description}
                            {--tenancy= : Tenancy mode (multi|single)}
                            {--org-name= : Default organization name (single-tenant mode only)}
                            {--cache-driver= : Cache/session/queue driver (database|redis)}
                            {--redis-host= : Redis host (default: 127.0.0.1)}
                            {--redis-port= : Redis port (default: 6379)}
                            {--redis-password= : Redis password}
                            {--search-driver= : Scout search driver (collection|typesense)}
                            {--typesense-key= : Typesense API key}
                            {--typesense-host= : Typesense host (default: localhost)}
                            {--typesense-port= : Typesense port (default: 8108)}
                            {--ai-provider= : Default AI provider (openrouter|openai|anthropic|gemini|groq|xai|deepseek|mistral|ollama)}
                            {--ai-api-key= : API key for the chosen AI provider}
                            {--default-gateway= : Billing default gateway (stripe|paddle|lemon_squeezy)}
                            {--currency= : Billing currency (usd|eur|gbp)}
                            {--trial-days= : Default trial days for subscriptions}
                            {--demo : Install all demo modules}
                            {--no-demo : Skip demo data entirely}
                            {--modules= : Comma-separated module keys (users,organizations,billing,content,marketing,developer)}
                            {--cohere-api-key= : Cohere API key (reranking)}
                            {--jina-api-key= : Jina API key (reranking alternative)}
                            {--thesys-api-key= : Thesys C1 API key}
                            {--scout-prefix= : Scout index prefix}
                            {--scout-queue : Queue Scout indexing}
                            {--scout-identify : Identify models when indexing}';

    protected $description = 'Full application installer — database, admin, app, tenancy, infra, mail, search, AI, social, storage, broadcasting, SEO, monitoring, billing, integrations, theme, memory, backup, features, demo';

    /** @var array<string, bool> */
    private array $progress = [];

    public function handle(): int
    {
        intro('  Application Installer  ');

        $this->loadProgress();

        $nonInteractive = (bool) $this->option('non-interactive');
        $resume = (bool) $this->option('resume');

        // ════════════════════════════════════════════════════════
        // Phase 1 — Pre-flight
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_PREFLIGHT, 'Pre-flight checks', $resume)) {
            if (! $this->runPreflight()) {
                return self::FAILURE;
            }

            $this->completePhase(self::PHASE_PREFLIGHT);
        }

        // ════════════════════════════════════════════════════════
        // Phase 2 — Database
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_DATABASE, 'Database', $resume)) {
            if (! $this->isDatabaseReachable()) {
                if (! $this->configureDatabaseEnv()) {
                    return self::FAILURE;
                }
            } else {
                info('  Database connection OK');
            }

            $this->completePhase(self::PHASE_DATABASE);
        }

        // ════════════════════════════════════════════════════════
        // Phase 3 — Migrations
        // ════════════════════════════════════════════════════════
        $isFresh = (bool) $this->option('fresh');

        if (! $this->runPhase(self::PHASE_MIGRATIONS, 'Migrations', $resume) || $isFresh) {
            if ($isFresh) {
                if ($nonInteractive || confirm('  ⚠  Drop ALL tables and re-run migrations? This cannot be undone.', default: false)) {
                    spin(fn () => Artisan::call('migrate:fresh', ['--force' => true]), 'Running fresh migration…');
                    info('  Fresh migration complete');
                    $this->clearProgress();
                }
            } elseif (! $this->migrationsRan()) {
                spin(fn () => Artisan::call('migrate', ['--force' => true]), 'Running migrations…');
                info('  Migrations complete');
            } else {
                // Check if already installed
                try {
                    $wizard = resolve(SetupWizardSettings::class);

                    if ($wizard->setup_completed && ! $resume) {
                        if ($nonInteractive) {
                            $this->clearProgress();
                            outro('  Setup already complete — nothing to do.');

                            return self::SUCCESS;
                        }

                        if (! confirm('  Setup is already complete. Re-run and reconfigure?', default: false)) {
                            $this->clearProgress();
                            outro('  Nothing changed.');

                            return self::SUCCESS;
                        }
                    }
                } catch (Throwable) {
                    // Settings table not ready yet — migrations likely just ran
                }

                info('  Migrations already up to date');
            }

            $this->completePhase(self::PHASE_MIGRATIONS);
        }

        // ════════════════════════════════════════════════════════
        // Phase 4 — Essential seeders
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_SEEDERS, 'Essential Seeders', $resume)) {
            spin(
                fn () => Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\RolesAndPermissionsSeeder::class, '--force' => true]),
                'Seeding roles and permissions…'
            );
            spin(
                fn () => Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\GamificationSeeder::class, '--force' => true]),
                'Seeding gamification levels and achievements…'
            );
            spin(
                fn () => Artisan::call('db:seed', ['--class' => \Database\Seeders\Essential\MailTemplatesSeeder::class, '--force' => true]),
                'Seeding mail templates…'
            );
            spin(
                function (): void {
                    try {
                        Artisan::call('db:seed', ['--class' => \GeneaLabs\LaravelGovernor\Database\Seeders\LaravelGovernorDatabaseSeeder::class, '--force' => true]);
                    } catch (Throwable) {
                        // Non-fatal — Governor may not be used
                    }
                },
                'Seeding Governor (entities, roles)…'
            );
            info('  Essential data seeded');
            $this->completePhase(self::PHASE_SEEDERS);
        }

        // ════════════════════════════════════════════════════════
        // Phase 5 — Admin user
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_ADMIN, 'Admin User', $resume)) {
            if (! $this->createAdminUser($nonInteractive)) {
                return self::FAILURE;
            }

            $this->completePhase(self::PHASE_ADMIN);
        }

        // ════════════════════════════════════════════════════════
        // Phase 6 — App basics
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_APP, 'App Basics', $resume)) {
            $siteName = $nonInteractive
                ? (string) ($this->option('site-name') ?: 'My App')
                : text('  Site name', default: 'My App', required: true);

            $url = $nonInteractive
                ? (string) ($this->option('url') ?: 'http://localhost')
                : text('  Application URL', placeholder: 'https://example.com', default: 'http://localhost', required: true);

            $timezone = $nonInteractive
                ? 'UTC'
                : search(
                    label: '  Timezone',
                    options: fn (string $q): array => $this->searchTimezones($q),
                    placeholder: 'UTC',
                    scroll: 10,
                );

            $app = resolve(AppSettings::class);
            $app->site_name = $siteName;
            $app->url = $url;
            $app->timezone = (string) $timezone;
            $app->locale = 'en';
            $app->fallback_locale = 'en';
            $app->save();
            info('  App settings saved');
            $this->completePhase(self::PHASE_APP);
        }

        // ════════════════════════════════════════════════════════
        // Phase 7 — Tenancy mode
        // ════════════════════════════════════════════════════════
        $hasTenancyFlag = $this->option('tenancy') !== null;

        if (! $this->runPhase(self::PHASE_TENANCY, 'Application Mode', $resume)) {
            if ($hasTenancyFlag || ! $nonInteractive) {
                $mode = $nonInteractive
                    ? (string) ($this->option('tenancy') ?: 'multi')
                    : select(
                        '  Operating mode',
                        [
                            'multi' => 'Multi-tenant SaaS  — users belong to organizations',
                            'single' => 'Single-tenant tool — one organization, no switching',
                        ],
                        default: 'multi',
                    );

                $tenancy = resolve(TenancySettings::class);
                $tenancy->enabled = $mode === 'multi';

                if ($mode === 'single') {
                    $orgName = $nonInteractive
                        ? (string) ($this->option('org-name') ?: resolve(AppSettings::class)->site_name ?? 'My Org')
                        : text('  Organization name', default: resolve(AppSettings::class)->site_name ?? 'My Org', required: true);
                    $tenancy->default_org_name = $orgName;
                    $tenancy->allow_user_org_creation = false;
                }

                $tenancy->save();
                info('  Mode configured — '.$mode);
            }

            $this->completePhase(self::PHASE_TENANCY);
        }

        // ════════════════════════════════════════════════════════
        // Phase 8 — Infrastructure (cache / session / queue)
        // ════════════════════════════════════════════════════════
        $hasInfraFlag = $this->option('cache-driver') !== null;

        if (! $this->runPhase(self::PHASE_INFRA, 'Infrastructure', $resume)) {
            if ($hasInfraFlag || ! $nonInteractive) {
                $this->configureInfrastructure($nonInteractive);
            }

            $this->completePhase(self::PHASE_INFRA);
        }

        // ════════════════════════════════════════════════════════
        // Phase 9 — Mail
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_MAIL, 'Mail', $resume)) {
            $this->configureMail($nonInteractive);
            $this->completePhase(self::PHASE_MAIL);
        }

        // ════════════════════════════════════════════════════════
        // Phase 10 — Full-text Search (optional)
        // ════════════════════════════════════════════════════════
        $hasSearchFlag = $this->option('search-driver') !== null;

        if (! $this->runPhase(self::PHASE_SEARCH, 'Full-text Search', $resume, optional: true)) {
            if ($hasSearchFlag) {
                $this->configureSearch($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure full-text search (Typesense)?', default: false)) {
                $this->configureSearch($nonInteractive);
            }

            $this->completePhase(self::PHASE_SEARCH);
        }

        // ════════════════════════════════════════════════════════
        // Phase 11 — AI Providers (optional)
        // ════════════════════════════════════════════════════════
        $hasAiFlag = $this->option('ai-provider') !== null;

        if (! $this->runPhase(self::PHASE_AI, 'AI Providers', $resume, optional: true)) {
            if ($hasAiFlag) {
                $this->configureAi($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure AI providers?', default: false)) {
                $this->configureAi($nonInteractive);
            }

            $this->completePhase(self::PHASE_AI);
        }

        // ════════════════════════════════════════════════════════
        // Phase 12 — Social Auth (optional)
        // ════════════════════════════════════════════════════════
        $hasSocialFlags = $this->option('google-client-id') || $this->option('github-client-id');

        if (! $this->runPhase(self::PHASE_SOCIAL, 'Social Auth', $resume, optional: true)) {
            if ($hasSocialFlags) {
                $this->configureSocialAuth();
            } elseif (! $nonInteractive && confirm('  Configure Google / GitHub social login?', default: false)) {
                $this->configureSocialAuth();
            }

            $this->completePhase(self::PHASE_SOCIAL);
        }

        // ════════════════════════════════════════════════════════
        // Phase 13 — File Storage (optional)
        // ════════════════════════════════════════════════════════
        $hasStorageFlags = $this->option('s3-disk') || $this->option('s3-bucket');

        if (! $this->runPhase(self::PHASE_STORAGE, 'File Storage', $resume, optional: true)) {
            if ($hasStorageFlags) {
                $this->configureStorage($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure S3-compatible file storage?', default: false)) {
                $this->configureStorage(false);
            }

            $this->completePhase(self::PHASE_STORAGE);
        }

        // ════════════════════════════════════════════════════════
        // Phase 14 — Broadcasting / Reverb (optional)
        // ════════════════════════════════════════════════════════
        $hasReverbFlags = $this->option('reverb-app-id') !== null;

        if (! $this->runPhase(self::PHASE_BROADCASTING, 'Broadcasting', $resume, optional: true)) {
            if ($hasReverbFlags) {
                $this->configureBroadcasting($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure real-time broadcasting (Reverb / WebSockets)?', default: false)) {
                $this->configureBroadcasting(false);
            }

            $this->completePhase(self::PHASE_BROADCASTING);
        }

        // ════════════════════════════════════════════════════════
        // Phase 15 — SEO (optional)
        // ════════════════════════════════════════════════════════
        $hasSeoFlags = $this->option('meta-title') || $this->option('meta-description');

        if (! $this->runPhase(self::PHASE_SEO, 'SEO', $resume, optional: true)) {
            if ($hasSeoFlags) {
                $this->configureSeo($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure SEO meta tags?', default: true)) {
                $this->configureSeo(false);
            }

            $this->completePhase(self::PHASE_SEO);
        }

        // ════════════════════════════════════════════════════════
        // Phase 16 — Monitoring / Error Tracking (optional)
        // ════════════════════════════════════════════════════════
        $hasSentryFlag = $this->option('sentry-dsn') !== null;

        if (! $this->runPhase(self::PHASE_MONITORING, 'Monitoring', $resume, optional: true)) {
            if ($hasSentryFlag) {
                $this->configureMonitoring($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure error tracking (Sentry)?', default: false)) {
                $this->configureMonitoring(false);
            }

            $this->completePhase(self::PHASE_MONITORING);
        }

        // ════════════════════════════════════════════════════════
        // Phase 17 — Billing (optional, mirrors web installer)
        // ════════════════════════════════════════════════════════
        $hasBillingFlag = $this->option('default-gateway') !== null || $this->option('currency') !== null || $this->option('trial-days') !== null;

        if (! $this->runPhase(self::PHASE_BILLING, 'Billing', $resume, optional: true)) {
            if ($hasBillingFlag) {
                $this->configureBilling($nonInteractive);
            } elseif (! $nonInteractive && confirm('  Configure billing defaults (gateway, currency, trial)?', default: false)) {
                $this->configureBilling(false);
            }

            $this->completePhase(self::PHASE_BILLING);
        }

        // ════════════════════════════════════════════════════════
        // Integrations — Slack, Postmark, Resend (optional, mirrors web)
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_INTEGRATIONS, 'Integrations', $resume, optional: true)) {
            if (! $nonInteractive && confirm('  Configure integrations (Slack, Postmark, Resend)?', default: false)) {
                $this->configureIntegrations($nonInteractive);
            }

            $this->completePhase(self::PHASE_INTEGRATIONS);
        }

        // ════════════════════════════════════════════════════════
        // Theme — appearance defaults (optional, mirrors web)
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_THEME, 'Theme', $resume, optional: true)) {
            if (! $nonInteractive && confirm('  Configure theme & appearance defaults?', default: false)) {
                $this->configureTheme($nonInteractive);
            }

            $this->completePhase(self::PHASE_THEME);
        }

        // ════════════════════════════════════════════════════════
        // AI Memory — recall limits (optional, mirrors web)
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_MEMORY, 'AI Memory', $resume, optional: true)) {
            if (! $nonInteractive && confirm('  Configure AI memory / vector recall settings?', default: false)) {
                $this->configureMemory($nonInteractive);
            }

            $this->completePhase(self::PHASE_MEMORY);
        }

        // ════════════════════════════════════════════════════════
        // Backups — retention (optional, mirrors web)
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_BACKUP, 'Backups', $resume, optional: true)) {
            if (! $nonInteractive && confirm('  Configure backup retention?', default: false)) {
                $this->configureBackup($nonInteractive);
            }

            $this->completePhase(self::PHASE_BACKUP);
        }

        // ════════════════════════════════════════════════════════
        // Feature flags (optional, mirrors web installer)
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_FEATURES, 'Feature Flags', $resume, optional: true)) {
            if (! $nonInteractive && confirm('  Configure which features to enable globally?', default: false)) {
                $this->configureFeatures(false);
            }

            $this->completePhase(self::PHASE_FEATURES);
        }

        // ════════════════════════════════════════════════════════
        // Phase 19 — Demo Data
        // ════════════════════════════════════════════════════════
        if (! $this->runPhase(self::PHASE_DEMO, 'Demo Data', $resume, optional: true)) {
            $this->installDemoData($nonInteractive);
            $this->completePhase(self::PHASE_DEMO);
        }

        // ════════════════════════════════════════════════════════
        // Phase 20 — Finalize
        // ════════════════════════════════════════════════════════
        note('Finalizing');

        $wizard = resolve(SetupWizardSettings::class);
        $wizard->setup_completed = true;
        $wizard->completed_steps = ['app', 'mail', 'billing', 'ai', 'complete'];
        $wizard->save();

        SettingsOverlayServiceProvider::applyOverlay();

        $this->clearProgress();

        $appSettings = resolve(AppSettings::class);
        $url = $appSettings->url ?? 'http://localhost';
        $adminUrl = mb_rtrim($url, '/').'/admin';

        $mail = resolve(MailSettings::class);
        $prism = resolve(PrismSettings::class);
        $fs = resolve(FilesystemSettings::class);
        $bc = resolve(BroadcastingSettings::class);
        $scout = resolve(ScoutSettings::class);

        $aiConfigured = isset($this->progress[self::PHASE_AI]) && $prism->default_provider;
        $storageConfigured = ($fs->default_disk ?? 'local') !== 'local';
        $broadcastConfigured = isset($this->progress[self::PHASE_BROADCASTING]) && $bc->reverb_app_id !== null;
        $searchConfigured = isset($this->progress[self::PHASE_SEARCH]) && ($scout->driver ?? 'collection') !== 'collection';

        $configuredSummary = implode("\n", array_filter([
            '    ✔  App name    : '.$appSettings->site_name,
            '    ✔  URL         : '.$url,
            sprintf('    ✔  Mail        : %s (%s)', $mail->mailer, $mail->from_address),
            $aiConfigured ? '    ✔  AI provider : '.$prism->default_provider : null,
            $searchConfigured ? '    ✔  Search      : '.$scout->driver : null,
            $storageConfigured ? '    ✔  Storage     : '.$fs->default_disk : null,
            $broadcastConfigured ? '    ✔  Broadcasting: reverb' : null,
        ]));

        outro(implode("\n", [
            '  Installation complete!',
            '',
            $configuredSummary,
            '',
            '  ── Next steps ──',
            '',
            '    Open app    : '.$url,
            '    Admin panel : '.$adminUrl,
            '',
            '    Frontend    : bun run dev  (or: bun run build for production)',
            '    Queue       : php artisan horizon  (or: php artisan queue:work)',
            '',
            '    Health check: php artisan app:health',
            '    Settings    : php artisan settings:list',
        ]));

        return self::SUCCESS;
    }

    // ─── Phase tracking (resume support) ──────────────────────────────────────

    private function loadProgress(): void
    {
        $path = storage_path('app/'.self::PROGRESS_FILE);

        if (file_exists($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            $this->progress = is_array($decoded) ? $decoded : [];
        }
    }

    private function saveProgress(): void
    {
        $dir = storage_path('app');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(storage_path('app/'.self::PROGRESS_FILE), json_encode($this->progress));
    }

    private function completePhase(string $phase): void
    {
        $this->progress[$phase] = true;
        $this->saveProgress();
    }

    private function clearProgress(): void
    {
        $path = storage_path('app/'.self::PROGRESS_FILE);

        if (file_exists($path)) {
            unlink($path);
        }

        $this->progress = [];
    }

    /**
     * Returns true if the phase should be SKIPPED (already done + resuming).
     * Prints the section header in all cases.
     */
    private function runPhase(string $phase, string $label, bool $resume, bool $optional = false): bool
    {
        $done = isset($this->progress[$phase]);

        if ($done && $resume) {
            $suffix = $optional ? ' (optional — skipped)' : ' (already done)';
            note($label.$suffix);
            info('  Skipping — completed in previous run');

            return true; // caller should skip this phase
        }

        note($label);

        return false; // caller should run this phase
    }

    // ─── Pre-flight ───────────────────────────────────────────────────────────

    private function runPreflight(): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            file_put_contents($envPath, '');
        }

        $env = (string) file_get_contents($envPath);

        if (! preg_match('/^APP_ENV=/m', $env)) {
            $env = $this->setEnvVar($env, 'APP_ENV', 'local');
            file_put_contents($envPath, $env);
            info('  APP_ENV=local written to .env');
        }

        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
            info('  APP_KEY generated');
        }

        $missing = array_filter(
            ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'],
            fn (string $ext): bool => ! extension_loaded($ext)
        );

        if ($missing !== []) {
            error('  Missing PHP extensions: '.implode(', ', $missing));

            return false;
        }

        foreach ([storage_path(), base_path('bootstrap/cache')] as $path) {
            if (! is_writable($path)) {
                error('  Not writable: '.$path);

                return false;
            }
        }

        info('  All checks passed  (PHP '.PHP_VERSION.')');

        return true;
    }

    // ─── Database ─────────────────────────────────────────────────────────────

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

    private function configureDatabaseEnv(): bool
    {
        warning('  No database connection detected.');

        $driver = select(
            '  Database driver',
            [
                'sqlite' => 'SQLite  — file-based, no server needed (great for local dev)',
                'pgsql' => 'PostgreSQL',
                'mysql' => 'MySQL / MariaDB',
            ],
            default: 'sqlite',
        );

        $envPath = base_path('.env');
        $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

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
            $defaultPort = $driver === 'pgsql' ? '5432' : '3306';
            $host = text('  DB host', default: '127.0.0.1');
            $port = text('  DB port', default: $defaultPort);
            $database = text('  DB database', default: 'laravel', required: true);
            $username = text('  DB username', default: 'root');
            $pass = password('  DB password');

            $env = $this->setEnvVar($env, 'DB_CONNECTION', $driver);
            $env = $this->setEnvVar($env, 'DB_HOST', $host);
            $env = $this->setEnvVar($env, 'DB_PORT', $port);
            $env = $this->setEnvVar($env, 'DB_DATABASE', $database);
            $env = $this->setEnvVar($env, 'DB_USERNAME', $username);
            $env = $this->setEnvVar($env, 'DB_PASSWORD', $pass);
            file_put_contents($envPath, $env);

            config([
                'database.default' => $driver,
                sprintf('database.connections.%s.host', $driver) => $host,
                sprintf('database.connections.%s.port', $driver) => $port,
                sprintf('database.connections.%s.database', $driver) => $database,
                sprintf('database.connections.%s.username', $driver) => $username,
                sprintf('database.connections.%s.password', $driver) => $pass,
            ]);
        }

        DB::purge();

        if (! $this->isDatabaseReachable()) {
            error('  Could not connect. Check credentials and try again.');

            return false;
        }

        info('  Database connection established');

        return true;
    }

    // ─── Admin user ───────────────────────────────────────────────────────────

    private function createAdminUser(bool $nonInteractive): bool
    {
        $existing = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->exists();

        if ($existing) {
            info('  Super-admin account already exists — skipping');

            return true;
        }

        $name = $nonInteractive
            ? (string) ($this->option('admin-name') ?: 'Admin')
            : text('  Full name', default: 'Admin', required: true);

        $email = $nonInteractive
            ? (string) ($this->option('admin-email') ?: 'superadmin@example.com')
            : text(
                '  Email address',
                default: 'superadmin@example.com',
                required: true,
                validate: fn ($v): ?string => Validator::make(['email' => $v], ['email' => ['email']])->fails()
                    ? 'Please enter a valid email address.'
                    : null,
            );

        $pass = $nonInteractive
            ? (string) ($this->option('admin-password') ?: 'password')
            : password(
                '  Password',
                required: true,
                validate: fn ($v): ?string => mb_strlen((string) $v) < 8 ? 'Password must be at least 8 characters.' : null,
            );

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($pass),
            'email_verified_at' => now(),
        ]);

        AssignRoleViaDb::assignGlobal($user, ['super-admin']);

        info('  Admin created: '.$email);

        return true;
    }

    // ─── Infrastructure (cache / session / queue) ─────────────────────────────

    private function configureInfrastructure(bool $nonInteractive = false): void
    {
        if ($nonInteractive) {
            $useRedis = (string) ($this->option('cache-driver') ?: 'database') === 'redis';
        } else {
            $this->line('  Default driver for cache, sessions, and queues is "database" (works out of the box).');
            $this->line('  Switching to Redis improves performance for high-traffic or real-time apps.');
            $this->newLine();
            $useRedis = confirm('  Use Redis for cache, session, and queue?', default: false);
        }

        $envPath = base_path('.env');
        $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        if ($useRedis) {
            $host = $nonInteractive
                ? (string) ($this->option('redis-host') ?: '127.0.0.1')
                : text('  Redis host', default: '127.0.0.1');

            $port = $nonInteractive
                ? (string) ($this->option('redis-port') ?: '6379')
                : text('  Redis port', default: '6379');

            $pass = $nonInteractive
                ? (string) ($this->option('redis-password') ?? '')
                : password('  Redis password (leave blank if none)');

            $env = $this->setEnvVar($env, 'REDIS_HOST', $host);
            $env = $this->setEnvVar($env, 'REDIS_PORT', $port);

            if ($pass !== '') {
                $env = $this->setEnvVar($env, 'REDIS_PASSWORD', $pass);
            }

            $env = $this->setEnvVar($env, 'CACHE_STORE', 'redis');
            $env = $this->setEnvVar($env, 'SESSION_DRIVER', 'redis');
            $env = $this->setEnvVar($env, 'QUEUE_CONNECTION', 'redis');
            file_put_contents($envPath, $env);

            // Update runtime config so the ping below uses the new credentials.
            config([
                'database.redis.default.host' => $host,
                'database.redis.default.port' => (int) $port,
                'database.redis.default.password' => $pass !== '' ? $pass : null,
            ]);

            $this->verifyRedis();

            // Persist to DB so ManageInfrastructure Filament page shows current values.
            $infra = resolve(InfrastructureSettings::class);
            $infra->cache_driver = 'redis';
            $infra->session_driver = 'redis';
            $infra->queue_connection = 'redis';
            $infra->redis_host = $host;
            $infra->redis_port = (int) $port;
            $infra->redis_password = $pass !== '' ? $pass : null;
            $infra->save();

            info('  Redis configured for cache, session, and queue');
            info('  Tip: Run php artisan horizon to process queued jobs');
        } else {
            $env = $this->setEnvVar($env, 'CACHE_STORE', 'database');
            $env = $this->setEnvVar($env, 'SESSION_DRIVER', 'database');
            $env = $this->setEnvVar($env, 'QUEUE_CONNECTION', 'database');
            file_put_contents($envPath, $env);

            $infra = resolve(InfrastructureSettings::class);
            $infra->cache_driver = 'database';
            $infra->session_driver = 'database';
            $infra->queue_connection = 'database';
            $infra->save();

            info('  Using database driver for cache, session, and queue');
        }
    }

    // ─── Mail ─────────────────────────────────────────────────────────────────

    private function configureMail(bool $nonInteractive): void
    {
        $mailer = $nonInteractive
            ? (string) ($this->option('mail-mailer') ?: 'log')
            : select(
                '  Mailer',
                [
                    'log' => 'Log      — write to log file (local dev default)',
                    'smtp' => 'SMTP     — custom SMTP server',
                    'ses' => 'SES      — Amazon Simple Email Service',
                    'postmark' => 'Postmark',
                    'resend' => 'Resend',
                    'sendmail' => 'Sendmail',
                ],
                default: 'log',
            );

        $mail = resolve(MailSettings::class);
        $mail->mailer = $mailer;
        $mail->smtp_host = '127.0.0.1';
        $mail->smtp_port = 587;
        $mail->smtp_username = null;
        $mail->smtp_password = null;
        $mail->smtp_encryption = 'tls';

        if ($mailer === 'smtp' && ! $nonInteractive) {
            $mail->smtp_host = text('  SMTP host', default: 'smtp.mailtrap.io');
            $mail->smtp_port = (int) text('  SMTP port', default: '587');
            $mail->smtp_username = text('  SMTP username') ?: null;
            $mail->smtp_password = password('  SMTP password') ?: null;
            $enc = select('  Encryption', ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'], default: 'tls');
            $mail->smtp_encryption = $enc === '' ? null : $enc;
        }

        $mail->from_address = $nonInteractive
            ? (string) ($this->option('mail-from') ?: 'hello@example.com')
            : text('  From address', default: 'hello@example.com', required: true);

        $mail->from_name = $nonInteractive
            ? (string) ($this->option('mail-from-name') ?: 'Example')
            : text('  From name', default: 'Example', required: true);

        $mail->save();

        if ($mailer === 'smtp') {
            $this->verifySmtp($mail->smtp_host, $mail->smtp_port);
        }

        info('  Mail configured');
    }

    // ─── Search ───────────────────────────────────────────────────────────────

    private function configureSearch(bool $nonInteractive = false): void
    {
        $driver = $nonInteractive
            ? (string) ($this->option('search-driver') ?: 'typesense')
            : select(
                '  Search driver',
                [
                    'collection' => 'Collection  — in-database search (no extra setup)',
                    'typesense' => 'Typesense   — blazing-fast full-text search (recommended for production)',
                ],
                default: 'typesense',
            );

        $scout = resolve(ScoutSettings::class);
        $scout->driver = $driver;

        if ($driver === 'typesense') {
            $apiKey = $nonInteractive
                ? (string) ($this->option('typesense-key') ?: 'LARAVEL-HERD')
                : text('  Typesense API key', default: 'LARAVEL-HERD', required: true);

            $host = $nonInteractive
                ? (string) ($this->option('typesense-host') ?: 'localhost')
                : text('  Typesense host', default: 'localhost');

            $port = $nonInteractive
                ? (string) ($this->option('typesense-port') ?: '8108')
                : text('  Typesense port', default: '8108');

            $protocol = $nonInteractive ? 'http' : select('  Typesense protocol', ['http' => 'http', 'https' => 'https'], default: 'http');

            $scout->typesense_api_key = $apiKey;
            $scout->typesense_host = $host;
            $scout->typesense_port = (int) $port;
            $scout->typesense_protocol = $protocol;

            $envPath = base_path('.env');
            $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'TYPESENSE_API_KEY', $apiKey);
            $env = $this->setEnvVar($env, 'TYPESENSE_HOST', $host);
            $env = $this->setEnvVar($env, 'TYPESENSE_PORT', $port);
            file_put_contents($envPath, $env);

            info('  Typesense credentials written to .env and Scout settings');
            $this->verifyTypesense($host, (int) $port, $apiKey);
        }

        // Scout prefix / queue / identify (mirrors web installer saveSearch)
        if ($nonInteractive) {
            if ($this->option('scout-prefix') !== null && (string) $this->option('scout-prefix') !== '') {
                $scout->prefix = (string) $this->option('scout-prefix');
            }
            if ($this->option('scout-queue')) {
                $scout->queue = true;
            }
            if ($this->option('scout-identify')) {
                $scout->identify = true;
            }
        } else {
            if (confirm('  Set Scout index prefix?', default: false)) {
                $scout->prefix = (string) (text('  Index prefix', default: '') ?? '');
            }
            if (confirm('  Queue Scout indexing?', default: false)) {
                $scout->queue = true;
            }
            if (confirm('  Identify models when indexing?', default: false)) {
                $scout->identify = true;
            }
        }

        $scout->save();

        info('  Search configured — driver: '.$driver);
    }

    // ─── AI ───────────────────────────────────────────────────────────────────

    private function configureAi(bool $nonInteractive = false): void
    {
        $provider = $nonInteractive
            ? (string) ($this->option('ai-provider') ?: 'openrouter')
            : select(
                '  Default AI provider',
                [
                    'openrouter' => 'OpenRouter  — access 200+ models via one API',
                    'openai' => 'OpenAI      — GPT-4o, o1, etc.',
                    'anthropic' => 'Anthropic   — Claude 3.5 Sonnet, etc.',
                    'gemini' => 'Gemini      — Google Gemini 2.0, etc.',
                    'groq' => 'Groq        — ultra-fast inference',
                    'xai' => 'xAI         — Grok',
                    'deepseek' => 'DeepSeek    — DeepSeek R1',
                    'mistral' => 'Mistral',
                    'ollama' => 'Ollama      — local models, no API key needed',
                ],
                default: 'openrouter',
            );

        $prism = resolve(PrismSettings::class);
        $prism->default_provider = $provider;

        if ($provider !== 'ollama') {
            $key = $nonInteractive
                ? (string) ($this->option('ai-api-key') ?? '')
                : password('  API key for '.$provider, required: true);

            if ($key !== '') {
                match ($provider) {
                    'openrouter' => $prism->openrouter_api_key = $key,
                    'openai' => $prism->openai_api_key = $key,
                    'anthropic' => $prism->anthropic_api_key = $key,
                    'gemini' => $prism->gemini_api_key = $key,
                    'groq' => $prism->groq_api_key = $key,
                    'xai' => $prism->xai_api_key = $key,
                    'deepseek' => $prism->deepseek_api_key = $key,
                    'mistral' => $prism->mistral_api_key = $key,
                    default => null,
                };
            }
        }

        $prism->save();

        $ai = resolve(AiSettings::class);
        $ai->default_provider = $provider === 'ollama' ? 'openai' : $provider;
        $ai->save();

        if (isset($key) && $key !== '' && $provider !== 'ollama') {
            $this->verifyAiProvider($provider, $key);
        }

        info('  AI configured — default: '.$provider);

        $envPath = base_path('.env');

        $thesysOpt = (string) ($this->option('thesys-api-key') ?? '');
        if ($thesysOpt !== '') {
            $ai = resolve(AiSettings::class);
            $ai->thesys_api_key = $thesysOpt;
            $ai->save();
            $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'THESYS_API_KEY', $thesysOpt);
            file_put_contents($envPath, $env);
            info('  THESYS_API_KEY written to .env and AiSettings');
        } elseif (! $nonInteractive && confirm('  Add Thesys C1 API key (optional)?', default: false)) {
            $thesysKey = password('  Thesys API key');
            if ($thesysKey !== '') {
                $ai = resolve(AiSettings::class);
                $ai->thesys_api_key = $thesysKey;
                $ai->save();
                $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
                $env = $this->setEnvVar($env, 'THESYS_API_KEY', $thesysKey);
                file_put_contents($envPath, $env);
                info('  THESYS_API_KEY written to .env and AiSettings');
            }
        }

        $cohereOpt = (string) ($this->option('cohere-api-key') ?? '');
        if ($cohereOpt !== '') {
            $ai = resolve(AiSettings::class);
            $ai->cohere_api_key = $cohereOpt;
            $ai->save();
            $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'COHERE_API_KEY', $cohereOpt);
            file_put_contents($envPath, $env);
            info('  Cohere API key saved (reranking)');
        } elseif (! $nonInteractive && confirm('  Add Cohere API key for reranking (optional)?', default: false)) {
            $k = password('  Cohere API key');
            if ($k !== '') {
                $ai = resolve(AiSettings::class);
                $ai->cohere_api_key = $k;
                $ai->save();
                $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
                $env = $this->setEnvVar($env, 'COHERE_API_KEY', $k);
                file_put_contents($envPath, $env);
                info('  Cohere API key saved');
            }
        }

        $jinaOpt = (string) ($this->option('jina-api-key') ?? '');
        if ($jinaOpt !== '') {
            $ai = resolve(AiSettings::class);
            $ai->jina_api_key = $jinaOpt;
            $ai->save();
            $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
            $env = $this->setEnvVar($env, 'JINA_API_KEY', $jinaOpt);
            file_put_contents($envPath, $env);
            info('  Jina API key saved (reranking alternative)');
        } elseif (! $nonInteractive && confirm('  Add Jina API key for reranking (optional)?', default: false)) {
            $k = password('  Jina API key');
            if ($k !== '') {
                $ai = resolve(AiSettings::class);
                $ai->jina_api_key = $k;
                $ai->save();
                $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
                $env = $this->setEnvVar($env, 'JINA_API_KEY', $k);
                file_put_contents($envPath, $env);
                info('  Jina API key saved');
            }
        }
    }

    // ─── Social Auth ──────────────────────────────────────────────────────────

    private function configureSocialAuth(): void
    {
        $auth = resolve(AuthSettings::class);

        $googleId = (string) ($this->option('google-client-id') ?? '');
        $googleSecret = (string) ($this->option('google-client-secret') ?? '');
        $githubId = (string) ($this->option('github-client-id') ?? '');
        $githubSecret = (string) ($this->option('github-client-secret') ?? '');

        if ($googleId !== '' || confirm('  Enable Google OAuth?', default: false)) {
            $auth->google_client_id = $googleId !== '' ? $googleId : text('  Google Client ID', required: true);
            $auth->google_client_secret = $googleSecret !== '' ? $googleSecret : password('  Google Client Secret', required: true);
            $auth->google_oauth_enabled = true;
        }

        if ($githubId !== '' || confirm('  Enable GitHub OAuth?', default: false)) {
            $auth->github_client_id = $githubId !== '' ? $githubId : text('  GitHub Client ID', required: true);
            $auth->github_client_secret = $githubSecret !== '' ? $githubSecret : password('  GitHub Client Secret', required: true);
            $auth->github_oauth_enabled = true;
        }

        $auth->save();
        info('  Social auth configured — ensure the OAuth redirect URL is registered with each provider.');
    }

    // ─── Storage ──────────────────────────────────────────────────────────────

    private function configureStorage(bool $nonInteractive = false): void
    {
        $optDisk = (string) ($this->option('s3-disk') ?? '');

        $driver = $optDisk !== '' ? $optDisk : select(
            '  Default storage disk',
            [
                'local' => 'Local — store files on this server (default)',
                's3' => 'S3     — Amazon S3 or any S3-compatible service (Cloudflare R2, DigitalOcean Spaces, etc.)',
            ],
            default: 's3',
        );

        $fs = resolve(FilesystemSettings::class);
        $fs->default_disk = $driver;

        if ($driver === 's3') {
            $fs->s3_key = (string) ($this->option('s3-key') ?: text('  Access key ID', required: true));
            $fs->s3_secret = (string) ($this->option('s3-secret') ?: password('  Secret access key', required: true));
            $fs->s3_region = (string) ($this->option('s3-region') ?: text('  Region', default: 'us-east-1', required: true));
            $fs->s3_bucket = (string) ($this->option('s3-bucket') ?: text('  Bucket name', required: true));
            $optUrl = (string) ($this->option('s3-url') ?? '');

            // Only prompt for the optional custom endpoint URL in interactive mode.
            $fs->s3_url = $optUrl !== '' ? $optUrl : ($nonInteractive ? null : (text('  Custom endpoint URL (leave blank for AWS)') ?: null));
        }

        $fs->save();

        if ($driver === 's3') {
            $this->verifyS3($fs);
        }

        info('  Storage configured — disk: '.$driver);
    }

    // ─── Broadcasting ─────────────────────────────────────────────────────────

    private function configureBroadcasting(bool $nonInteractive = false): void
    {
        $bc = resolve(BroadcastingSettings::class);
        $bc->default_connection = 'reverb';
        $bc->reverb_app_id = (string) ($this->option('reverb-app-id') ?: text('  Reverb App ID', required: true));
        $bc->reverb_app_key = (string) ($this->option('reverb-app-key') ?: text('  Reverb App Key', required: true));
        $bc->reverb_app_secret = (string) ($this->option('reverb-app-secret') ?: password('  Reverb App Secret', required: true));

        // Host/port/scheme have sensible defaults; only prompt in interactive mode.
        $bc->reverb_host = $nonInteractive ? 'localhost' : text('  Reverb host', default: 'localhost');
        $bc->reverb_port = $nonInteractive ? 8080 : (int) text('  Reverb port', default: '8080');
        $bc->reverb_scheme = $nonInteractive ? 'http' : select('  Scheme', ['http' => 'http', 'https' => 'https'], default: 'http');
        $bc->save();

        info('  Broadcasting configured — run php artisan reverb:start to start the WebSocket server.');
    }

    // ─── SEO ──────────────────────────────────────────────────────────────────

    private function configureSeo(bool $nonInteractive = false): void
    {
        $app = resolve(AppSettings::class);
        $defaultTitle = $app->site_name ?? config('app.name', 'My App');

        $seo = resolve(SeoSettings::class);
        $seo->meta_title = (string) ($this->option('meta-title') ?: text('  Default page title', default: $defaultTitle, required: true));
        $seo->meta_description = (string) ($this->option('meta-description') ?: text('  Default meta description (160 chars)', required: true));

        // Open Graph image is optional; skip the prompt in non-interactive mode.
        $seo->og_image = $nonInteractive ? null : (text('  Open Graph image URL (optional)') ?: null);
        $seo->save();

        info('  SEO meta tags saved');
    }

    // ─── Monitoring ───────────────────────────────────────────────────────────

    private function configureMonitoring(bool $nonInteractive = false): void
    {
        $dsn = (string) ($this->option('sentry-dsn') ?: text('  Sentry DSN (from sentry.io → Project Settings → Client Keys)', required: true));

        $monitoring = resolve(MonitoringSettings::class);
        $monitoring->sentry_dsn = $dsn;

        // Sample rate has a sensible default; skip the prompt in non-interactive mode.
        $monitoring->sentry_sample_rate = $nonInteractive ? 1.0 : (float) text('  Error sample rate (0.0–1.0)', default: '1.0');
        $monitoring->save();

        info('  Sentry error tracking configured');
    }

    // ─── Billing (mirrors web installer saveBilling) ───────────────────────────

    private function configureBilling(bool $nonInteractive = false): void
    {
        $gateway = $nonInteractive
            ? (string) ($this->option('default-gateway') ?: 'stripe')
            : select(
                '  Default gateway',
                [
                    'none' => 'None (free app)',
                    'stripe' => 'Stripe',
                    'paddle' => 'Paddle',
                    'lemon_squeezy' => 'Lemon Squeezy',
                ],
                default: 'stripe',
            );

        if ($gateway === 'none') {
            $billing = resolve(BillingSettings::class);
            $billing->default_gateway = 'none';
            $billing->save();
            info('  Billing set to none — no payment gateway.');

            return;
        }

        $currency = $nonInteractive
            ? (string) ($this->option('currency') ?: 'usd')
            : select('  Currency', ['usd' => 'USD', 'eur' => 'EUR', 'gbp' => 'GBP'], default: 'usd');

        $trialDays = $nonInteractive
            ? (int) ($this->option('trial-days') ?: 14)
            : (int) text('  Trial days', default: '14', required: true);

        $billing = resolve(BillingSettings::class);
        $billing->default_gateway = $gateway;
        $billing->currency = $currency;
        $billing->trial_days = $trialDays;
        $billing->save();

        if (! $nonInteractive && confirm('  Enter payment gateway API keys now (optional)?', default: false)) {
            if ($gateway === 'stripe') {
                $stripe = resolve(StripeSettings::class);
                $stripe->key = text('  Stripe publishable key (pk_...)') ?: null;
                $stripe->secret = password('  Stripe secret key (sk_...)') ?: null;
                $stripe->webhook_secret = password('  Stripe webhook secret (whsec_...)') ?: null;
                $stripe->save();
                info('  Stripe keys saved');
            }
            if ($gateway === 'paddle') {
                $paddle = resolve(PaddleSettings::class);
                $paddle->vendor_id = text('  Paddle vendor ID') ?: null;
                $paddle->vendor_auth_code = password('  Paddle vendor auth code') ?: null;
                $paddle->public_key = text('  Paddle public key') ?: null;
                $paddle->webhook_secret = password('  Paddle webhook secret') ?: null;
                $paddle->sandbox = confirm('  Use Paddle sandbox?', default: true);
                $paddle->save();
                info('  Paddle keys saved');
            }
            if ($gateway === 'lemon_squeezy') {
                $lemon = resolve(LemonSqueezySettings::class);
                $lemon->api_key = password('  Lemon Squeezy API key') ?: null;
                $lemon->signing_secret = password('  Lemon Squeezy signing secret') ?: null;
                $lemon->store = text('  Store ID') ?: null;
                $lemon->save();
                info('  Lemon Squeezy keys saved');
            }
        } else {
            info('  Billing defaults saved — configure keys later in Settings → Billing.');
        }
    }

    private function configureIntegrations(bool $nonInteractive = false): void
    {
        $integrations = resolve(IntegrationsSettings::class);
        if (! $nonInteractive) {
            $integrations->slack_webhook_url = text('  Slack webhook URL (optional)') ?: null;
            $integrations->slack_bot_token = password('  Slack bot token (optional)') ?: null;
            $integrations->slack_channel = text('  Slack channel (optional)') ?: null;
            $integrations->postmark_token = password('  Postmark token (optional)') ?: null;
            $integrations->resend_key = password('  Resend API key (optional)') ?: null;
        }
        $integrations->save();
        info('  Integrations saved');
    }

    private function configureTheme(bool $nonInteractive = false): void
    {
        $theme = resolve(ThemeSettings::class);
        if (! $nonInteractive) {
            $theme->preset = select('  Theme preset', ['default' => 'Default', 'saas' => 'SaaS', 'minimal' => 'Minimal'], default: 'default');
            $theme->default_appearance = select('  Default appearance', ['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'], default: 'system');
            $theme->font = select(
                '  Font',
                [
                    'instrument-sans' => 'Instrument Sans',
                    'inter' => 'Inter',
                    'geist' => 'Geist',
                    'poppins' => 'Poppins',
                    'outfit' => 'Outfit',
                    'plus-jakarta-sans' => 'Plus Jakarta Sans',
                ],
                default: 'instrument-sans',
            );
        }
        $theme->save();
        info('  Theme settings saved');
    }

    private function configureMemory(bool $nonInteractive = false): void
    {
        $memory = resolve(MemorySettings::class);
        if (! $nonInteractive) {
            $memory->dimensions = (int) text('  Embedding dimensions', default: '1536');
            $memory->similarity_threshold = (float) text('  Similarity threshold (0–1)', default: '0.5');
            $memory->recall_limit = (int) text('  Recall limit', default: '10');
        }
        $memory->save();
        info('  AI memory settings saved');
    }

    private function configureBackup(bool $nonInteractive = false): void
    {
        $backup = resolve(BackupSettings::class);
        if (! $nonInteractive) {
            $backup->keep_daily_backups_for_days = (int) text('  Keep daily backups for (days)', default: '16');
            $backup->delete_oldest_when_size_mb = (int) text('  Delete oldest when over (MB)', default: '5000');
        }
        $backup->save();
        info('  Backup retention saved');
    }

    // ─── Feature flags (mirrors web installer saveFeatures) ─────────────────────

    private function configureFeatures(bool $nonInteractive = false): void
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

        $allKeys = array_keys(config('feature-flags.inertia_features', []));
        $options = [];
        foreach ($allKeys as $key) {
            $options[$key] = $labels[$key] ?? str_replace('_', ' ', ucfirst((string) $key));
        }

        $enabled = $nonInteractive
            ? $allKeys
            : multiselect(
                '  Features to enable (space to toggle, enter to confirm)',
                $options,
                default: $allKeys,
            );

        $disabled = array_values(array_diff($allKeys, $enabled));
        $settings = resolve(FeatureFlagSettings::class);
        $settings->globally_disabled_modules = $disabled;
        $settings->save();

        info('  Feature flags saved — '.count($enabled).' enabled, '.count($disabled).' disabled.');
    }

    // ─── Connection checks ────────────────────────────────────────────────────

    /**
     * Ping Redis with the currently configured default connection.
     * Prints a one-line status; never throws.
     */
    private function verifyRedis(): void
    {
        try {
            Redis::connection()->ping();
            info('  ✔ Redis connection verified');
        } catch (Throwable $throwable) {
            warning('  ✗ Could not connect to Redis: '.$throwable->getMessage());
            warning('  Settings were saved — fix the connection and run php artisan redis:ping to verify.');
        }
    }

    /**
     * Open a raw TCP socket to the SMTP host:port to confirm it is reachable.
     * Prints a one-line status; never throws.
     */
    private function verifySmtp(string $host, int $port): void
    {
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);

            if ($socket !== false) {
                fclose($socket);
                info(sprintf('  ✔ SMTP connection verified (%s:%d)', $host, $port));
            } else {
                warning(sprintf('  ✗ Could not reach SMTP server %s:%d — %s (errno %d)', $host, $port, $errstr, $errno));
                warning('  Settings were saved — check your SMTP credentials and firewall rules.');
            }
        } catch (Throwable $throwable) {
            warning('  ✗ SMTP check failed: '.$throwable->getMessage());
        }
    }

    /**
     * Hit Typesense's /health endpoint to confirm the server is reachable.
     * Prints a one-line status; never throws.
     */
    private function verifyTypesense(string $host, int $port, string $apiKey): void
    {
        try {
            $scheme = $port === 443 ? 'https' : 'http';
            $response = Http::withHeaders(['X-TYPESENSE-API-KEY' => $apiKey])
                ->timeout(5)
                ->get(sprintf('%s://%s:%d/health', $scheme, $host, $port));

            if ($response->successful()) {
                info(sprintf('  ✔ Typesense connection verified (%s:%d)', $host, $port));
            } else {
                warning(sprintf('  ✗ Typesense returned HTTP %d — credentials saved, but check the server.', $response->status()));
            }
        } catch (Throwable $throwable) {
            warning('  ✗ Could not reach Typesense: '.$throwable->getMessage());
            warning('  Settings were saved — ensure Typesense is running and accessible.');
        }
    }

    /**
     * List objects in the configured S3 bucket (limit 1) to verify credentials.
     * Prints a one-line status; never throws.
     */
    private function verifyS3(FilesystemSettings $fs): void
    {
        try {
            // Apply S3 config to the runtime filesystem before testing.
            config([
                'filesystems.disks.s3.key' => $fs->s3_key,
                'filesystems.disks.s3.secret' => $fs->s3_secret,
                'filesystems.disks.s3.region' => $fs->s3_region,
                'filesystems.disks.s3.bucket' => $fs->s3_bucket,
                'filesystems.disks.s3.url' => $fs->s3_url,
            ]);

            Storage::disk('s3')->files('', false);
            info(sprintf('  ✔ S3 connection verified (bucket: %s)', $fs->s3_bucket));
        } catch (Throwable $throwable) {
            $msg = $throwable->getMessage();

            // Trim noisy SDK traces to keep the output readable.
            if (mb_strlen($msg) > 120) {
                $msg = mb_substr($msg, 0, 120).'…';
            }

            warning('  ✗ Could not connect to S3: '.$msg);
            warning('  Credentials were saved — check your key, secret, region, and bucket name.');
        }
    }

    /**
     * Verify the AI provider API key is accepted by sending a minimal HEAD/GET
     * to the provider's base URL.  Prints a one-line status; never throws.
     */
    private function verifyAiProvider(string $provider, string $apiKey): void
    {
        /** @var array<string, array{url: string, header: string}> */
        $endpoints = [
            'openai' => ['url' => 'https://api.openai.com/v1/models', 'header' => 'Authorization'],
            'anthropic' => ['url' => 'https://api.anthropic.com/v1/models', 'header' => 'x-api-key'],
            'gemini' => ['url' => 'https://generativelanguage.googleapis.com/v1beta/models?key='.$apiKey, 'header' => ''],
            'groq' => ['url' => 'https://api.groq.com/openai/v1/models', 'header' => 'Authorization'],
            'xai' => ['url' => 'https://api.x.ai/v1/models', 'header' => 'Authorization'],
            'deepseek' => ['url' => 'https://api.deepseek.com/models', 'header' => 'Authorization'],
            'mistral' => ['url' => 'https://api.mistral.ai/v1/models', 'header' => 'Authorization'],
            'openrouter' => ['url' => 'https://openrouter.ai/api/v1/models', 'header' => 'Authorization'],
        ];

        $config = $endpoints[$provider] ?? null;

        if ($config === null) {
            return;
        }

        try {
            $request = Http::timeout(8);

            if ($config['header'] === 'Authorization') {
                $request = $request->withToken($apiKey);
            } elseif ($config['header'] !== '') {
                $request = $request->withHeaders([$config['header'] => $apiKey]);
            }

            $response = $request->get($config['url']);

            if ($response->status() === 401 || $response->status() === 403) {
                warning(sprintf('  ✗ %s rejected the API key (HTTP %s) — credentials saved, but the key may be invalid.', $provider, $response->status()));
            } elseif ($response->successful() || $response->status() === 200) {
                info(sprintf('  ✔ %s API key verified', $provider));
            } else {
                warning(sprintf('  ✗ %s returned HTTP %s — credentials saved.', $provider, $response->status()));
            }
        } catch (Throwable $throwable) {
            warning(sprintf('  ✗ Could not reach %s: ', $provider).$throwable->getMessage());
            warning('  Credentials were saved — check your internet connection or API key.');
        }
    }

    // ─── Demo data ────────────────────────────────────────────────────────────

    private function installDemoData(bool $nonInteractive): void
    {
        if ($this->option('no-demo')) {
            return;
        }

        if ($this->option('demo')) {
            // Install all modules
            $this->runModules(array_keys(self::MODULES));

            return;
        }

        if ($nonInteractive) {
            // --modules option or skip
            $modulesCsv = (string) ($this->option('modules') ?? '');

            if ($modulesCsv !== '') {
                $keys = array_filter(array_map(trim(...), explode(',', $modulesCsv)));
                $this->runModules($keys);
            }

            return;
        }

        $this->line('  Demo data populates the application with realistic sample content.');
        $this->line('  Select which modules to install (space to toggle, enter to confirm).');
        $this->newLine();

        $choices = [];

        foreach (self::MODULES as $key => $module) {
            $choices[$key] = sprintf('%s  — %s', $module['label'], $module['description']);
        }

        $selected = multiselect(
            label: '  Demo modules',
            options: $choices,
            default: ['users', 'organizations', 'content'],
            hint: 'Recommended: users, organizations, content',
        );

        if ($selected === []) {
            info('  No demo data installed');

            return;
        }

        $this->runModules($selected);
    }

    /**
     * @param  array<int, string>|list<string>  $moduleKeys
     */
    private function runModules(array $moduleKeys): void
    {
        foreach ($moduleKeys as $key) {
            $module = self::MODULES[$key] ?? null;

            if ($module === null) {
                warning(sprintf('  Unknown module: %s — skipping', $key));

                continue;
            }

            spin(function () use ($module): void {
                foreach ($module['seeders'] as $seederClass) {
                    try {
                        Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);
                    } catch (Throwable) {
                        // Individual seeder failures are non-fatal
                    }
                }
            }, sprintf('Installing %s…', $module['label']));

            info(sprintf('  %s installed', $module['label']));
        }
    }

    // ─── Timezone search ──────────────────────────────────────────────────────

    /**
     * @return array<string, string>
     */
    private function searchTimezones(string $query): array
    {
        $all = DateTimeZone::listIdentifiers();

        if ($query === '') {
            $popular = [
                'UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
                'America/Toronto', 'America/Vancouver', 'Europe/London', 'Europe/Paris', 'Europe/Berlin',
                'Europe/Amsterdam', 'Europe/Madrid', 'Europe/Rome', 'Europe/Moscow', 'Asia/Dubai',
                'Asia/Kolkata', 'Asia/Singapore', 'Asia/Tokyo', 'Asia/Shanghai', 'Australia/Sydney',
            ];

            return array_combine($popular, $popular);
        }

        $matches = array_filter($all, fn (string $tz): bool => mb_stripos($tz, $query) !== false);
        $matches = array_slice($matches, 0, 20);

        return array_combine($matches, $matches);
    }

    // ─── .env helpers ─────────────────────────────────────────────────────────

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
