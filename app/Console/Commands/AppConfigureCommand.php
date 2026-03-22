<?php

declare(strict_types=1);

namespace App\Console\Commands;

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
use App\Settings\StripeSettings;
use App\Settings\TenancySettings;
use App\Settings\ThemeSettings;
use App\Support\ModuleLoader;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

final class AppConfigureCommand extends Command
{
    /** @var array<string, array{label: string, description: string, settings: string|null}> */
    private const array SECTIONS = [
        'app' => ['label' => 'App Basics', 'description' => 'Site name, URL, timezone, locale', 'settings' => AppSettings::class],
        'mail' => ['label' => 'Mail', 'description' => 'Mail driver and SMTP settings', 'settings' => MailSettings::class],
        'ai' => ['label' => 'AI Providers', 'description' => 'Default AI provider and API keys', 'settings' => PrismSettings::class],
        'billing' => ['label' => 'Billing', 'description' => 'Payment gateway, currency, trial days', 'settings' => BillingSettings::class],
        'search' => ['label' => 'Full-text Search', 'description' => 'Scout driver and Typesense settings', 'settings' => ScoutSettings::class],
        'social' => ['label' => 'Social Auth', 'description' => 'Google and GitHub OAuth', 'settings' => AuthSettings::class],
        'storage' => ['label' => 'File Storage', 'description' => 'Filesystem disk (local or S3)', 'settings' => FilesystemSettings::class],
        'broadcasting' => ['label' => 'Broadcasting', 'description' => 'Reverb / WebSocket settings', 'settings' => BroadcastingSettings::class],
        'seo' => ['label' => 'SEO', 'description' => 'Meta title, description, Open Graph', 'settings' => SeoSettings::class],
        'monitoring' => ['label' => 'Monitoring', 'description' => 'Sentry DSN and error tracking', 'settings' => MonitoringSettings::class],
        'tenancy' => ['label' => 'Tenancy Mode', 'description' => 'Multi-tenant or single-tenant', 'settings' => TenancySettings::class],
        'theme' => ['label' => 'Theme', 'description' => 'Preset, appearance, font', 'settings' => ThemeSettings::class],
        'features' => ['label' => 'Feature Flags', 'description' => 'Enable or disable feature flags', 'settings' => FeatureFlagSettings::class],
        'modules' => ['label' => 'Modules', 'description' => 'Enable or disable application modules', 'settings' => null],
        'infra' => ['label' => 'Infrastructure', 'description' => 'Cache, session, queue drivers (database or Redis)', 'settings' => InfrastructureSettings::class],
        'integrations' => ['label' => 'Integrations', 'description' => 'Slack, Postmark, Resend', 'settings' => IntegrationsSettings::class],
        'memory' => ['label' => 'AI Memory', 'description' => 'Embedding dimensions, similarity, recall', 'settings' => MemorySettings::class],
        'backup' => ['label' => 'Backups', 'description' => 'Backup retention settings', 'settings' => BackupSettings::class],
    ];

    protected $signature = 'app:configure
                            {section? : The section to configure (e.g. app, mail, ai, billing)}
                            {--list : List all available sections without interactive prompts}';

    protected $description = 'Reconfigure application settings after installation — app, mail, AI, billing, search, social, storage, broadcasting, SEO, monitoring, tenancy, theme, features, modules, infra, integrations, memory, backup';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listSections();
        }

        $section = $this->argument('section');

        if ($section !== null) {
            $section = (string) $section;

            if (! isset(self::SECTIONS[$section])) {
                $this->components->error(sprintf('Unknown section "%s". Run php artisan app:configure --list to see available sections.', $section));

                return self::FAILURE;
            }

            intro('  Configure: '.self::SECTIONS[$section]['label'].'  ');

            return $this->runSection($section);
        }

        // Interactive menu
        intro('  Application Configuration  ');

        $choices = [];
        foreach (self::SECTIONS as $key => $meta) {
            $choices[$key] = sprintf('%s  — %s', $meta['label'], $meta['description']);
        }

        $selected = select(
            '  Which section would you like to configure?',
            $choices,
        );

        return $this->runSection((string) $selected);
    }

    private function listSections(): int
    {
        $this->components->info('Available configuration sections:');
        $this->newLine();

        $rows = [];
        foreach (self::SECTIONS as $key => $meta) {
            $rows[] = [$key, $meta['label'], $meta['description']];
        }

        $this->table(['Section', 'Label', 'Description'], $rows);
        $this->newLine();
        $this->components->info('Usage: php artisan app:configure <section>');

        return self::SUCCESS;
    }

    private function runSection(string $section): int
    {
        match ($section) {
            'app' => $this->configureApp(),
            'mail' => $this->configureMail(),
            'ai' => $this->configureAi(),
            'billing' => $this->configureBilling(),
            'search' => $this->configureSearch(),
            'social' => $this->configureSocialAuth(),
            'storage' => $this->configureStorage(),
            'broadcasting' => $this->configureBroadcasting(),
            'seo' => $this->configureSeo(),
            'monitoring' => $this->configureMonitoring(),
            'tenancy' => $this->configureTenancy(),
            'theme' => $this->configureTheme(),
            'features' => $this->configureFeatures(),
            'modules' => $this->configureModules(),
            'infra' => $this->configureInfrastructure(),
            'integrations' => $this->configureIntegrations(),
            'memory' => $this->configureMemory(),
            'backup' => $this->configureBackup(),
            default => null,
        };

        $this->clearSettingsCache();

        SettingsOverlayServiceProvider::applyOverlay();

        outro('  Configuration updated.  ');

        return self::SUCCESS;
    }

    // ─── App Basics ──────────────────────────────────────────────────────────

    private function configureApp(): void
    {
        $app = resolve(AppSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Site name' => $app->site_name,
            'URL' => $app->url,
            'Timezone' => $app->timezone,
            'Locale' => $app->locale,
        ]);

        $app->site_name = text('  Site name', default: $app->site_name ?? 'My App', required: true);

        $app->url = text('  Application URL', default: $app->url ?? 'http://localhost', required: true);

        $app->timezone = (string) search(
            label: '  Timezone',
            options: fn (string $q): array => $this->searchTimezones($q),
            placeholder: $app->timezone ?? 'UTC',
            scroll: 10,
        );

        $app->locale = text('  Locale', default: $app->locale ?? 'en', required: true);

        $app->save();
        info('  App settings saved');
    }

    // ─── Mail ────────────────────────────────────────────────────────────────

    private function configureMail(): void
    {
        $mail = resolve(MailSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Mailer' => $mail->mailer,
            'From address' => $mail->from_address,
            'From name' => $mail->from_name,
            'SMTP host' => $mail->smtp_host,
            'SMTP port' => (string) $mail->smtp_port,
        ]);

        $mail->mailer = select(
            '  Mailer',
            [
                'log' => 'Log      — write to log file (local dev default)',
                'smtp' => 'SMTP     — custom SMTP server',
                'ses' => 'SES      — Amazon Simple Email Service',
                'postmark' => 'Postmark',
                'resend' => 'Resend',
                'sendmail' => 'Sendmail',
            ],
            default: $mail->mailer ?? 'log',
        );

        if ($mail->mailer === 'smtp') {
            $mail->smtp_host = text('  SMTP host', default: $mail->smtp_host ?? 'smtp.mailtrap.io');
            $mail->smtp_port = (int) text('  SMTP port', default: (string) ($mail->smtp_port ?? 587));
            $mail->smtp_username = text('  SMTP username', default: $mail->smtp_username ?? '') ?: null;
            $mail->smtp_password = password('  SMTP password') ?: null;
            $enc = select('  Encryption', ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'], default: $mail->smtp_encryption ?? 'tls');
            $mail->smtp_encryption = $enc === '' ? null : $enc;
        }

        $mail->from_address = text('  From address', default: $mail->from_address ?? 'hello@example.com', required: true);
        $mail->from_name = text('  From name', default: $mail->from_name ?? 'Example', required: true);

        $mail->save();

        if ($mail->mailer === 'smtp') {
            $this->verifySmtp($mail->smtp_host ?? '127.0.0.1', $mail->smtp_port ?? 587);
        }

        info('  Mail configured');
    }

    // ─── AI Providers ────────────────────────────────────────────────────────

    private function configureAi(): void
    {
        $prism = resolve(PrismSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Default provider' => $prism->default_provider,
        ]);

        $provider = select(
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
            default: $prism->default_provider ?? 'openrouter',
        );

        $prism->default_provider = $provider;

        if ($provider !== 'ollama') {
            $key = password('  API key for '.$provider, required: true);

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

        // Optional additional keys
        if (confirm('  Configure additional AI keys (Thesys, Cohere, Jina)?', default: false)) {
            $thesysKey = password('  Thesys C1 API key (leave blank to skip)');
            if ($thesysKey !== '') {
                $ai = resolve(AiSettings::class);
                $ai->thesys_api_key = $thesysKey;
                $ai->save();
                $this->setEnvValue('THESYS_API_KEY', $thesysKey);
                info('  Thesys API key saved');
            }

            $cohereKey = password('  Cohere API key (leave blank to skip)');
            if ($cohereKey !== '') {
                $ai = resolve(AiSettings::class);
                $ai->cohere_api_key = $cohereKey;
                $ai->save();
                $this->setEnvValue('COHERE_API_KEY', $cohereKey);
                info('  Cohere API key saved');
            }

            $jinaKey = password('  Jina API key (leave blank to skip)');
            if ($jinaKey !== '') {
                $ai = resolve(AiSettings::class);
                $ai->jina_api_key = $jinaKey;
                $ai->save();
                $this->setEnvValue('JINA_API_KEY', $jinaKey);
                info('  Jina API key saved');
            }
        }
    }

    // ─── Billing ─────────────────────────────────────────────────────────────

    private function configureBilling(): void
    {
        $billing = resolve(BillingSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Default gateway' => $billing->default_gateway,
            'Currency' => $billing->currency,
            'Trial days' => (string) $billing->trial_days,
        ]);

        $gateway = select(
            '  Default gateway',
            [
                'none' => 'None (free app)',
                'stripe' => 'Stripe',
                'paddle' => 'Paddle',
                'lemon_squeezy' => 'Lemon Squeezy',
            ],
            default: $billing->default_gateway ?? 'stripe',
        );

        if ($gateway === 'none') {
            $billing->default_gateway = 'none';
            $billing->save();
            info('  Billing set to none — no payment gateway.');

            return;
        }

        $billing->default_gateway = $gateway;
        $billing->currency = select('  Currency', ['usd' => 'USD', 'eur' => 'EUR', 'gbp' => 'GBP'], default: $billing->currency ?? 'usd');
        $billing->trial_days = (int) text('  Trial days', default: (string) ($billing->trial_days ?? 14), required: true);
        $billing->save();

        if (confirm('  Enter payment gateway API keys now?', default: false)) {
            if ($gateway === 'stripe') {
                $stripe = resolve(StripeSettings::class);
                $stripe->key = text('  Stripe publishable key (pk_...)', default: $stripe->key ?? '') ?: null;
                $stripe->secret = password('  Stripe secret key (sk_...)') ?: null;
                $stripe->webhook_secret = password('  Stripe webhook secret (whsec_...)') ?: null;
                $stripe->save();
                info('  Stripe keys saved');
            }

            if ($gateway === 'paddle') {
                $paddle = resolve(PaddleSettings::class);
                $paddle->vendor_id = text('  Paddle vendor ID', default: $paddle->vendor_id ?? '') ?: null;
                $paddle->vendor_auth_code = password('  Paddle vendor auth code') ?: null;
                $paddle->public_key = text('  Paddle public key', default: $paddle->public_key ?? '') ?: null;
                $paddle->webhook_secret = password('  Paddle webhook secret') ?: null;
                $paddle->sandbox = confirm('  Use Paddle sandbox?', default: $paddle->sandbox ?? true);
                $paddle->save();
                info('  Paddle keys saved');
            }

            if ($gateway === 'lemon_squeezy') {
                $lemon = resolve(LemonSqueezySettings::class);
                $lemon->api_key = password('  Lemon Squeezy API key') ?: null;
                $lemon->signing_secret = password('  Lemon Squeezy signing secret') ?: null;
                $lemon->store = text('  Store ID', default: $lemon->store ?? '') ?: null;
                $lemon->save();
                info('  Lemon Squeezy keys saved');
            }
        }

        info('  Billing configured — gateway: '.$gateway);
    }

    // ─── Search ──────────────────────────────────────────────────────────────

    private function configureSearch(): void
    {
        $scout = resolve(ScoutSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Driver' => $scout->driver,
            'Typesense host' => $scout->typesense_host,
            'Typesense port' => (string) ($scout->typesense_port ?? ''),
            'Prefix' => $scout->prefix ?? '(none)',
            'Queue' => $scout->queue ? 'yes' : 'no',
        ]);

        $driver = select(
            '  Search driver',
            [
                'collection' => 'Collection  — in-database search (no extra setup)',
                'typesense' => 'Typesense   — blazing-fast full-text search (recommended for production)',
            ],
            default: $scout->driver ?? 'typesense',
        );

        $scout->driver = $driver;

        if ($driver === 'typesense') {
            $scout->typesense_api_key = text('  Typesense API key', default: $scout->typesense_api_key ?? 'LARAVEL-HERD', required: true);
            $scout->typesense_host = text('  Typesense host', default: $scout->typesense_host ?? 'localhost');
            $scout->typesense_port = (int) text('  Typesense port', default: (string) ($scout->typesense_port ?? 8108));
            $scout->typesense_protocol = select('  Typesense protocol', ['http' => 'http', 'https' => 'https'], default: $scout->typesense_protocol ?? 'http');

            $this->setEnvValue('TYPESENSE_API_KEY', $scout->typesense_api_key);
            $this->setEnvValue('TYPESENSE_HOST', $scout->typesense_host);
            $this->setEnvValue('TYPESENSE_PORT', (string) $scout->typesense_port);

            info('  Typesense credentials written to .env and Scout settings');

            $this->verifyTypesense($scout->typesense_host, $scout->typesense_port, $scout->typesense_api_key);
        }

        if (confirm('  Set Scout index prefix?', default: ($scout->prefix ?? '') !== '')) {
            $scout->prefix = (string) (text('  Index prefix', default: $scout->prefix ?? '') ?? '');
        }

        $scout->queue = confirm('  Queue Scout indexing?', default: $scout->queue ?? false);
        $scout->identify = confirm('  Identify models when indexing?', default: $scout->identify ?? false);

        $scout->save();
        info('  Search configured — driver: '.$driver);
    }

    // ─── Social Auth ─────────────────────────────────────────────────────────

    private function configureSocialAuth(): void
    {
        $auth = resolve(AuthSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Google OAuth' => $auth->google_oauth_enabled ? 'enabled' : 'disabled',
            'GitHub OAuth' => $auth->github_oauth_enabled ? 'enabled' : 'disabled',
        ]);

        if (confirm('  Enable Google OAuth?', default: $auth->google_oauth_enabled ?? false)) {
            $auth->google_client_id = text('  Google Client ID', default: $auth->google_client_id ?? '', required: true);
            $auth->google_client_secret = password('  Google Client Secret', required: true);
            $auth->google_oauth_enabled = true;
        } else {
            $auth->google_oauth_enabled = false;
        }

        if (confirm('  Enable GitHub OAuth?', default: $auth->github_oauth_enabled ?? false)) {
            $auth->github_client_id = text('  GitHub Client ID', default: $auth->github_client_id ?? '', required: true);
            $auth->github_client_secret = password('  GitHub Client Secret', required: true);
            $auth->github_oauth_enabled = true;
        } else {
            $auth->github_oauth_enabled = false;
        }

        $auth->save();
        info('  Social auth configured');
    }

    // ─── Storage ─────────────────────────────────────────────────────────────

    private function configureStorage(): void
    {
        $fs = resolve(FilesystemSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Default disk' => $fs->default_disk,
            'S3 bucket' => $fs->s3_bucket ?? '(not set)',
            'S3 region' => $fs->s3_region ?? '(not set)',
        ]);

        $driver = select(
            '  Default storage disk',
            [
                'local' => 'Local — store files on this server (default)',
                's3' => 'S3     — Amazon S3 or any S3-compatible service',
            ],
            default: $fs->default_disk ?? 'local',
        );

        $fs->default_disk = $driver;

        if ($driver === 's3') {
            $fs->s3_key = text('  Access key ID', default: $fs->s3_key ?? '', required: true);
            $fs->s3_secret = password('  Secret access key', required: true);
            $fs->s3_region = text('  Region', default: $fs->s3_region ?? 'us-east-1', required: true);
            $fs->s3_bucket = text('  Bucket name', default: $fs->s3_bucket ?? '', required: true);
            $fs->s3_url = text('  Custom endpoint URL (leave blank for AWS)', default: $fs->s3_url ?? '') ?: null;
        }

        $fs->save();

        if ($driver === 's3') {
            $this->verifyS3($fs);
        }

        info('  Storage configured — disk: '.$driver);
    }

    // ─── Broadcasting ────────────────────────────────────────────────────────

    private function configureBroadcasting(): void
    {
        $bc = resolve(BroadcastingSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Connection' => $bc->default_connection,
            'Reverb host' => $bc->reverb_host,
            'Reverb port' => (string) ($bc->reverb_port ?? ''),
        ]);

        $bc->default_connection = 'reverb';
        $bc->reverb_app_id = text('  Reverb App ID', default: $bc->reverb_app_id ?? '', required: true);
        $bc->reverb_app_key = text('  Reverb App Key', default: $bc->reverb_app_key ?? '', required: true);
        $bc->reverb_app_secret = password('  Reverb App Secret', required: true);
        $bc->reverb_host = text('  Reverb host', default: $bc->reverb_host ?? 'localhost');
        $bc->reverb_port = (int) text('  Reverb port', default: (string) ($bc->reverb_port ?? 8080));
        $bc->reverb_scheme = select('  Scheme', ['http' => 'http', 'https' => 'https'], default: $bc->reverb_scheme ?? 'http');
        $bc->save();

        info('  Broadcasting configured — run php artisan reverb:start to start the WebSocket server.');
    }

    // ─── SEO ─────────────────────────────────────────────────────────────────

    private function configureSeo(): void
    {
        $seo = resolve(SeoSettings::class);
        $app = resolve(AppSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Meta title' => $seo->meta_title,
            'Meta description' => $seo->meta_description,
            'OG image' => $seo->og_image ?? '(not set)',
        ]);

        $seo->meta_title = text('  Default page title', default: $seo->meta_title ?? $app->site_name ?? 'My App', required: true);
        $seo->meta_description = text('  Default meta description (160 chars)', default: $seo->meta_description ?? '', required: true);
        $seo->og_image = text('  Open Graph image URL (optional)', default: $seo->og_image ?? '') ?: null;
        $seo->save();

        info('  SEO meta tags saved');
    }

    // ─── Monitoring ──────────────────────────────────────────────────────────

    private function configureMonitoring(): void
    {
        $monitoring = resolve(MonitoringSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Sentry DSN' => $monitoring->sentry_dsn ? '(set)' : '(not set)',
            'Sample rate' => (string) ($monitoring->sentry_sample_rate ?? 1.0),
        ]);

        $monitoring->sentry_dsn = text('  Sentry DSN', default: $monitoring->sentry_dsn ?? '', required: true);
        $monitoring->sentry_sample_rate = (float) text('  Error sample rate (0.0–1.0)', default: (string) ($monitoring->sentry_sample_rate ?? 1.0));
        $monitoring->save();

        info('  Sentry error tracking configured');
    }

    // ─── Tenancy ─────────────────────────────────────────────────────────────

    private function configureTenancy(): void
    {
        $tenancy = resolve(TenancySettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Mode' => $tenancy->enabled ? 'multi-tenant' : 'single-tenant',
            'Default org name' => $tenancy->default_org_name ?? '(not set)',
        ]);

        $mode = select(
            '  Operating mode',
            [
                'multi' => 'Multi-tenant SaaS  — users belong to organizations',
                'single' => 'Single-tenant tool — one organization, no switching',
            ],
            default: $tenancy->enabled ? 'multi' : 'single',
        );

        $tenancy->enabled = $mode === 'multi';

        if ($mode === 'single') {
            $app = resolve(AppSettings::class);
            $tenancy->default_org_name = text('  Organization name', default: $tenancy->default_org_name ?? $app->site_name ?? 'My Org', required: true);
            $tenancy->allow_user_org_creation = false;
        }

        $tenancy->save();
        info('  Mode configured — '.$mode);
    }

    // ─── Theme ───────────────────────────────────────────────────────────────

    private function configureTheme(): void
    {
        $theme = resolve(ThemeSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Preset' => $theme->preset ?? 'default',
            'Appearance' => $theme->default_appearance ?? 'system',
            'Font' => $theme->font ?? 'ibm-plex-sans',
        ]);

        $theme->preset = select('  Theme preset', ['default' => 'Default', 'saas' => 'SaaS', 'minimal' => 'Minimal'], default: $theme->preset ?? 'default');
        $theme->default_appearance = select('  Default appearance', ['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'], default: $theme->default_appearance ?? 'system');
        $theme->font = select(
            '  Font',
            [
                'ibm-plex-sans' => 'IBM Plex Sans',
                'instrument-sans' => 'Instrument Sans',
                'inter' => 'Inter',
                'geist' => 'Geist',
                'poppins' => 'Poppins',
                'outfit' => 'Outfit',
                'plus-jakarta-sans' => 'Plus Jakarta Sans',
            ],
            default: $theme->font ?? 'ibm-plex-sans',
        );
        $theme->save();
        info('  Theme settings saved');
    }

    // ─── Feature Flags ───────────────────────────────────────────────────────

    private function configureFeatures(): void
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
        $settings = resolve(FeatureFlagSettings::class);
        $currentlyDisabled = $settings->globally_disabled_modules ?? [];
        $currentlyEnabled = array_values(array_diff($allKeys, $currentlyDisabled));

        note('Current values');
        info(sprintf('  %d enabled, %d disabled', count($currentlyEnabled), count($currentlyDisabled)));

        $options = [];
        foreach ($allKeys as $key) {
            $options[$key] = $labels[$key] ?? str_replace('_', ' ', ucfirst((string) $key));
        }

        $enabled = multiselect(
            '  Features to enable (space to toggle, enter to confirm)',
            $options,
            default: $currentlyEnabled,
        );

        $disabled = array_values(array_diff($allKeys, $enabled));
        $settings->globally_disabled_modules = $disabled;
        $settings->save();

        info('  Feature flags saved — '.count($enabled).' enabled, '.count($disabled).' disabled.');
    }

    // ─── Modules ─────────────────────────────────────────────────────────────

    private function configureModules(): void
    {
        /** @var array<string, bool> $modules */
        $modules = ModuleLoader::all();

        if ($modules === []) {
            info('  No modules found.');

            return;
        }

        $choices = [];
        $defaults = [];

        foreach ($modules as $name => $enabled) {
            $manifest = ModuleLoader::readManifest($name);
            /** @var string $label */
            $label = $manifest['label'] ?? ucfirst($name);
            /** @var string $description */
            $description = $manifest['description'] ?? '';
            $choices[$name] = $description !== '' ? "{$label}  — {$description}" : $label;

            if ($enabled) {
                $defaults[] = $name;
            }
        }

        note('Current values');
        info(sprintf('  %d enabled, %d disabled', count($defaults), count($modules) - count($defaults)));

        $selected = multiselect(
            label: '  Which modules should be enabled?',
            options: $choices,
            default: $defaults,
            hint: 'Space to toggle, Enter to confirm',
        );

        $updatedModules = [];
        foreach ($modules as $name => $enabled) {
            $updatedModules[$name] = in_array($name, $selected, true);
        }

        ModuleLoader::writeConfig($updatedModules);

        Artisan::call('config:clear');

        $enabledCount = count(array_filter($updatedModules));
        info(sprintf('  %d module(s) enabled', $enabledCount));
    }

    // ─── Infrastructure ──────────────────────────────────────────────────────

    private function configureInfrastructure(): void
    {
        $infra = resolve(InfrastructureSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Cache driver' => $infra->cache_driver,
            'Session driver' => $infra->session_driver,
            'Queue connection' => $infra->queue_connection,
            'Redis host' => $infra->redis_host ?? '(not set)',
        ]);

        $this->line('  Default driver for cache, sessions, and queues is "database" (works out of the box).');
        $this->line('  Switching to Redis improves performance for high-traffic or real-time apps.');
        $this->newLine();
        $useRedis = confirm('  Use Redis for cache, session, and queue?', default: ($infra->cache_driver ?? 'database') === 'redis');

        $envPath = base_path('.env');
        $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        if ($useRedis) {
            $host = text('  Redis host', default: $infra->redis_host ?? '127.0.0.1');
            $port = text('  Redis port', default: (string) ($infra->redis_port ?? 6379));
            $pass = password('  Redis password (leave blank if none)');

            $env = $this->setEnvVar($env, 'REDIS_HOST', $host);
            $env = $this->setEnvVar($env, 'REDIS_PORT', $port);

            if ($pass !== '') {
                $env = $this->setEnvVar($env, 'REDIS_PASSWORD', $pass);
            }

            $env = $this->setEnvVar($env, 'CACHE_STORE', 'redis');
            $env = $this->setEnvVar($env, 'SESSION_DRIVER', 'redis');
            $env = $this->setEnvVar($env, 'QUEUE_CONNECTION', 'redis');
            file_put_contents($envPath, $env);

            config([
                'database.redis.default.host' => $host,
                'database.redis.default.port' => (int) $port,
                'database.redis.default.password' => $pass !== '' ? $pass : null,
            ]);

            $this->verifyRedis();

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

            $infra->cache_driver = 'database';
            $infra->session_driver = 'database';
            $infra->queue_connection = 'database';
            $infra->save();

            info('  Using database driver for cache, session, and queue');
        }
    }

    // ─── Integrations ────────────────────────────────────────────────────────

    private function configureIntegrations(): void
    {
        $integrations = resolve(IntegrationsSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Slack webhook' => $integrations->slack_webhook_url ? '(set)' : '(not set)',
            'Slack channel' => $integrations->slack_channel ?? '(not set)',
            'Postmark' => $integrations->postmark_token ? '(set)' : '(not set)',
            'Resend' => $integrations->resend_key ? '(set)' : '(not set)',
        ]);

        $integrations->slack_webhook_url = text('  Slack webhook URL (optional)', default: $integrations->slack_webhook_url ?? '') ?: null;
        $integrations->slack_bot_token = password('  Slack bot token (optional)') ?: null;
        $integrations->slack_channel = text('  Slack channel (optional)', default: $integrations->slack_channel ?? '') ?: null;
        $integrations->postmark_token = password('  Postmark token (optional)') ?: null;
        $integrations->resend_key = password('  Resend API key (optional)') ?: null;
        $integrations->save();

        info('  Integrations saved');
    }

    // ─── AI Memory ───────────────────────────────────────────────────────────

    private function configureMemory(): void
    {
        $memory = resolve(MemorySettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Dimensions' => (string) ($memory->dimensions ?? 1536),
            'Similarity threshold' => (string) ($memory->similarity_threshold ?? 0.5),
            'Recall limit' => (string) ($memory->recall_limit ?? 10),
        ]);

        $memory->dimensions = (int) text('  Embedding dimensions', default: (string) ($memory->dimensions ?? 1536));
        $memory->similarity_threshold = (float) text('  Similarity threshold (0–1)', default: (string) ($memory->similarity_threshold ?? 0.5));
        $memory->recall_limit = (int) text('  Recall limit', default: (string) ($memory->recall_limit ?? 10));
        $memory->save();

        info('  AI memory settings saved');
    }

    // ─── Backups ─────────────────────────────────────────────────────────────

    private function configureBackup(): void
    {
        $backup = resolve(BackupSettings::class);

        note('Current values');
        $this->showCurrentValues([
            'Keep daily backups (days)' => (string) ($backup->keep_daily_backups_for_days ?? 16),
            'Delete oldest when over (MB)' => (string) ($backup->delete_oldest_when_size_mb ?? 5000),
        ]);

        $backup->keep_daily_backups_for_days = (int) text('  Keep daily backups for (days)', default: (string) ($backup->keep_daily_backups_for_days ?? 16));
        $backup->delete_oldest_when_size_mb = (int) text('  Delete oldest when over (MB)', default: (string) ($backup->delete_oldest_when_size_mb ?? 5000));
        $backup->save();

        info('  Backup retention saved');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * @param  array<string, string|null>  $values
     */
    private function showCurrentValues(array $values): void
    {
        foreach ($values as $label => $value) {
            $this->line(sprintf('    %s: %s', $label, $value ?? '(not set)'));
        }

        $this->newLine();
    }

    private function clearSettingsCache(): void
    {
        try {
            Artisan::call('settings:clear-cache');
        } catch (Throwable) {
            // Settings cache may not exist yet
        }
    }

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

    private function setEnvVar(string $env, string $key, string $value): string
    {
        $escaped = preg_match('/\s/', $value) ? '"'.$value.'"' : $value;
        $line = sprintf('%s=%s', $key, $escaped);

        if (preg_match(sprintf('/^%s=.*/m', $key), $env)) {
            return (string) preg_replace(sprintf('/^%s=.*/m', $key), $line, $env);
        }

        return mb_rtrim($env).(PHP_EOL.$line.PHP_EOL);
    }

    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';
        $env = $this->setEnvVar($env, $key, $value);
        file_put_contents($envPath, $env);
    }

    // ─── Connection verification (mirrors AppInstallCommand) ─────────────────

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

    private function verifyTypesense(string $host, int $port, string $apiKey): void
    {
        try {
            $scheme = $port === 443 ? 'https' : 'http';
            $baseUrl = sprintf('%s://%s:%d', $scheme, $host, $port);
            $connector = new \App\Http\Integrations\Typesense\TypesenseConnector($baseUrl, $apiKey);
            $response = $connector->send(new \App\Http\Integrations\Typesense\Requests\HealthCheckRequest);

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

    private function verifyS3(FilesystemSettings $fs): void
    {
        try {
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

            if (mb_strlen($msg) > 120) {
                $msg = mb_substr($msg, 0, 120).'…';
            }

            warning('  ✗ Could not connect to S3: '.$msg);
            warning('  Credentials were saved — check your key, secret, region, and bucket name.');
        }
    }

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
}
