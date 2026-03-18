<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Settings\AppSettings;
use App\Settings\AuthSettings;
use App\Settings\LoggingSettings;
use App\Settings\MailSettings;
use App\Settings\PrismSettings;
use App\Settings\ScoutSettings;
use App\Settings\SeoSettings;
use App\Settings\SetupWizardSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

/**
 * Comprehensive health check for the application.
 *
 * Checks all critical subsystems and reports pass / warn / fail for each.
 * Exit code 0 = everything green; 1 = one or more failures detected.
 */
final class AppHealthCommand extends Command
{
    protected $signature = 'app:health
                            {--json : Output results as JSON (useful for CI / monitoring)}
                            {--fail-on-warn : Exit with code 1 when any warnings exist}';

    protected $description = 'Run a comprehensive health check on all application subsystems';

    /** @var list<array{check: string, status: string, detail: string}> */
    private array $results = [];

    private bool $hasFailed = false;

    private bool $hasWarned = false;

    public function handle(): int
    {
        $json = (bool) $this->option('json');

        if (! $json) {
            intro('  Application Health Check  ');
        }

        $this->checkPhp();
        $this->checkEnvironment();
        $this->checkDatabase();
        $this->checkMigrations();
        $this->checkSetupStatus();
        $this->checkStorage();
        $this->checkCache();
        $this->checkSession();
        $this->checkQueue();
        $this->checkMail();
        $this->checkSearch();
        $this->checkAi();
        $this->checkHorizon();
        $this->checkReverb();
        $this->checkScheduler();
        $this->checkTwoFactorPolicy();
        $this->checkSeoMeta();
        $this->checkLoggingConfig();

        if ($json) {
            $this->line(json_encode([
                'status' => $this->hasFailed ? 'fail' : ($this->hasWarned ? 'warn' : 'ok'),
                'checks' => $this->results,
            ], JSON_PRETTY_PRINT));

            return $this->hasFailed ? self::FAILURE : self::SUCCESS;
        }

        // ── Table output ──────────────────────────────────────────────────────
        note('Results');

        $rows = array_map(fn (array $r): array => [
            $this->statusIcon($r['status']).' '.$r['check'],
            $r['detail'],
        ], $this->results);

        table(['Check', 'Detail'], $rows);

        $passed = count(array_filter($this->results, fn (array $r): bool => $r['status'] === 'ok'));
        $warned = count(array_filter($this->results, fn (array $r): bool => $r['status'] === 'warn'));
        $failed = count(array_filter($this->results, fn (array $r): bool => $r['status'] === 'fail'));
        $total = count($this->results);

        if ($this->hasFailed) {
            error(sprintf('  %d check(s) failed, %d warning(s), %d/%d passed', $failed, $warned, $passed, $total));
        } elseif ($this->hasWarned) {
            warning(sprintf('  %d warning(s), %d/%d checks passed', $warned, $passed, $total));
        } else {
            outro(sprintf('  All %d checks passed ✓', $total));
        }

        $failOnWarn = (bool) $this->option('fail-on-warn');

        return ($this->hasFailed || ($failOnWarn && $this->hasWarned)) ? self::FAILURE : self::SUCCESS;
    }

    // ─── Individual checks ─────────────────────────────────────────────────────

    private function checkPhp(): void
    {
        $version = PHP_VERSION;

        $missing = array_filter(
            ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'],
            fn (string $ext): bool => ! extension_loaded($ext)
        );

        if ($missing !== []) {
            $this->reportFail('PHP extensions', 'Missing: '.implode(', ', $missing));

            return;
        }

        $this->reportOk('PHP', sprintf('PHP %s — all required extensions loaded', $version));
    }

    private function checkEnvironment(): void
    {
        if (empty(config('app.key'))) {
            $this->reportFail('APP_KEY', 'Not set — run: php artisan key:generate');

            return;
        }

        $env = config('app.env', 'unknown');
        $debug = config('app.debug', false);

        if ($env === 'production' && $debug) {
            $this->reportWarn('Debug mode', 'APP_DEBUG=true in production — disable before going live');

            return;
        }

        $this->reportOk('Environment', sprintf('env=%s, debug=', $env).($debug ? 'true' : 'false'));
    }

    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $db = DB::connection()->getDatabaseName();
            $this->reportOk('Database', sprintf('%s — %s', $driver, $db));
        } catch (Throwable $throwable) {
            $this->reportFail('Database', $throwable->getMessage());
        }
    }

    private function checkMigrations(): void
    {
        try {
            Artisan::call('migrate:status', ['--no-ansi' => true]);
            $rawOutput = Artisan::output();
            $pendingCount = mb_substr_count($rawOutput, 'Pending');

            if ($pendingCount > 0) {
                $this->reportWarn('Migrations', $pendingCount.' pending migration(s) — run: php artisan migrate');
            } else {
                $this->reportOk('Migrations', 'All migrations have been run');
            }
        } catch (Throwable $throwable) {
            $this->reportFail('Migrations', 'Could not check: '.$throwable->getMessage());
        }
    }

    private function checkSetupStatus(): void
    {
        try {
            $wizard = resolve(SetupWizardSettings::class);
            $app = resolve(AppSettings::class);

            if (! $wizard->setup_completed) {
                $this->reportWarn('Setup Wizard', 'Not complete — visit /install or run: php artisan app:install');

                return;
            }

            $siteName = $app->site_name ?? '(not set)';
            $url = $app->url ?? '(not set)';
            $this->reportOk('Setup', sprintf('Complete — %s at %s', $siteName, $url));
        } catch (Throwable $throwable) {
            $this->reportFail('Setup', 'Settings unavailable: '.$throwable->getMessage());
        }
    }

    private function checkStorage(): void
    {
        $paths = [
            storage_path('app') => 'storage/app',
            storage_path('logs') => 'storage/logs',
            storage_path('framework/cache') => 'storage/framework/cache',
            storage_path('framework/sessions') => 'storage/framework/sessions',
            storage_path('framework/views') => 'storage/framework/views',
            base_path('bootstrap/cache') => 'bootstrap/cache',
        ];

        $notWritable = [];

        foreach ($paths as $path => $label) {
            if (! is_dir($path)) {
                @mkdir($path, 0755, true);
            }

            if (! is_writable($path)) {
                $notWritable[] = $label;
            }
        }

        if ($notWritable !== []) {
            $this->reportFail('Storage', 'Not writable: '.implode(', ', $notWritable));
        } else {
            $this->reportOk('Storage', 'All directories writable');
        }
    }

    private function checkCache(): void
    {
        $driver = (string) config('cache.default', 'unknown');

        try {
            $key = '_health_'.now()->timestamp;
            Cache::put($key, 'ok', 5);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val === 'ok') {
                $this->reportOk('Cache', 'Driver: '.$driver);
            } else {
                $this->reportFail('Cache', 'Read/write failed with driver: '.$driver);
            }
        } catch (Throwable $throwable) {
            $this->reportFail('Cache', sprintf('Driver: %s — %s', $driver, $throwable->getMessage()));
        }
    }

    private function checkSession(): void
    {
        $driver = (string) config('session.driver', 'unknown');
        $this->reportOk('Session', 'Driver: '.$driver);
    }

    private function checkQueue(): void
    {
        $connection = (string) config('queue.default', 'unknown');

        if ($connection === 'sync') {
            $this->reportWarn('Queue', '"sync" driver — jobs run synchronously. Switch to "database" or "redis" for background processing.');

            return;
        }

        try {
            Queue::size();
            $this->reportOk('Queue', 'Connection: '.$connection);
        } catch (Throwable) {
            $this->reportOk('Queue', 'Connection: '.$connection);
        }
    }

    private function checkMail(): void
    {
        try {
            $mail = resolve(MailSettings::class);
            $mailer = $mail->mailer ?? 'unknown';
            $from = $mail->from_address ?? '';

            if ($mailer === 'log') {
                $this->reportWarn('Mail', 'Mailer: log — emails go to log only. Change before going live.');

                return;
            }

            if (empty($from)) {
                $this->reportWarn('Mail', sprintf('Mailer: %s — from address not configured', $mailer));

                return;
            }

            $this->reportOk('Mail', sprintf('Mailer: %s, from: %s', $mailer, $from));
        } catch (Throwable $throwable) {
            $this->reportFail('Mail', 'Settings unavailable: '.$throwable->getMessage());
        }
    }

    private function checkSearch(): void
    {
        try {
            $scout = resolve(ScoutSettings::class);
            $driver = $scout->driver ?? (string) config('scout.driver', 'collection');

            if ($driver === 'typesense') {
                /** @var string $apiKey */
                $apiKey = config('scout.typesense.client-settings.api_key', '');
                /** @var string $host */
                $host = config('scout.typesense.client-settings.nodes.0.host', '');

                if ($apiKey === '' || $host === '') {
                    $this->reportWarn('Search', 'Typesense selected but credentials not configured in .env');

                    return;
                }

                $this->reportOk('Search', 'Typesense at '.$host);
            } else {
                $this->reportOk('Search', 'Driver: '.$driver);
            }
        } catch (Throwable $throwable) {
            $this->reportWarn('Search', 'Could not check: '.$throwable->getMessage());
        }
    }

    private function checkAi(): void
    {
        try {
            $prism = resolve(PrismSettings::class);
            $provider = $prism->default_provider ?? 'not set';

            $hasKey = match ($provider) {
                'openrouter' => ! empty($prism->openrouter_api_key),
                'openai' => ! empty($prism->openai_api_key),
                'anthropic' => ! empty($prism->anthropic_api_key),
                'gemini' => ! empty($prism->gemini_api_key),
                'groq' => ! empty($prism->groq_api_key),
                'xai' => ! empty($prism->xai_api_key),
                'deepseek' => ! empty($prism->deepseek_api_key),
                'mistral' => ! empty($prism->mistral_api_key),
                'ollama' => true,
                default => false,
            };

            if (! $hasKey) {
                $this->reportWarn('AI (Prism)', sprintf('Provider: %s — API key not configured', $provider));

                return;
            }

            $model = $prism->default_model ?? 'default';
            $this->reportOk('AI (Prism)', sprintf('Provider: %s, model: %s', $provider, $model));
        } catch (Throwable $throwable) {
            $this->reportWarn('AI (Prism)', 'Settings unavailable: '.$throwable->getMessage());
        }
    }

    private function checkHorizon(): void
    {
        if (config('queue.default') !== 'redis') {
            return;
        }

        if (! class_exists(\Laravel\Horizon\Horizon::class)) {
            $this->reportWarn('Horizon', 'Queue uses Redis but Laravel Horizon is not installed');

            return;
        }

        $this->reportOk('Horizon', 'Installed — run: php artisan horizon');
    }

    private function checkReverb(): void
    {
        if (! class_exists(\Laravel\Reverb\ReverbServiceProvider::class)) {
            return;
        }

        $appId = (string) config('reverb.apps.apps.0.app_id', '');

        if ($appId === '') {
            $this->reportWarn('Reverb (WebSockets)', 'Installed but REVERB_APP_ID not configured');

            return;
        }

        $this->reportOk('Reverb', 'App ID: '.$appId);
    }

    private function checkScheduler(): void
    {
        $lastRun = Cache::get('scheduler:last-run');

        if ($lastRun === null) {
            $this->reportWarn('Scheduler', 'No recent heartbeat — ensure cron is set: * * * * * php artisan schedule:run');
        } else {
            $this->reportOk('Scheduler', 'Last run: '.$lastRun);
        }
    }

    private function checkTwoFactorPolicy(): void
    {
        try {
            $auth = resolve(AuthSettings::class);
            $enforcement = $auth->two_factor_enforcement;

            if ($enforcement === 'optional' && app()->environment('production')) {
                $this->reportWarn(
                    '2FA Policy',
                    'Enforcement is "optional" in production — consider setting to "admins_only" or "required" in Settings > Auth.'
                );

                return;
            }

            $this->reportOk('2FA Policy', 'Enforcement: '.$enforcement);
        } catch (Throwable $throwable) {
            $this->reportWarn('2FA Policy', 'Could not check: '.$throwable->getMessage());
        }
    }

    private function checkSeoMeta(): void
    {
        try {
            $seo = resolve(SeoSettings::class);

            if (empty($seo->meta_title)) {
                $this->reportWarn('SEO', 'Meta title is empty — set it in Settings > SEO or during app:install.');

                return;
            }

            if (empty($seo->meta_description)) {
                $this->reportWarn('SEO', 'Meta title set but meta description is empty — set it in Settings > SEO.');

                return;
            }

            $descLen = mb_strlen($seo->meta_description);

            if ($descLen > 160) {
                $this->reportWarn('SEO', sprintf('Meta description is %d chars (recommended: ≤ 160).', $descLen));

                return;
            }

            $this->reportOk('SEO', sprintf('Meta title and description configured (%d chars).', $descLen));
        } catch (Throwable $throwable) {
            $this->reportWarn('SEO', 'Could not check: '.$throwable->getMessage());
        }
    }

    private function checkLoggingConfig(): void
    {
        try {
            $logging = resolve(LoggingSettings::class);
            $channel = $logging->default_channel;

            if ($channel === 'slack' && empty($logging->slack_webhook_url)) {
                $this->reportWarn(
                    'Logging',
                    'Default log channel is "slack" but no Slack webhook URL is configured — logs will be silently discarded. Set it in Settings > Logging.'
                );

                return;
            }

            $this->reportOk('Logging', sprintf('Channel: %s, level: %s', $channel, $logging->log_level));
        } catch (Throwable $throwable) {
            $this->reportWarn('Logging', 'Could not check: '.$throwable->getMessage());
        }
    }

    // ─── Result helpers ────────────────────────────────────────────────────────

    private function reportOk(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'ok', 'detail' => $detail];
    }

    private function reportWarn(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'warn', 'detail' => $detail];
        $this->hasWarned = true;
    }

    private function reportFail(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'fail', 'detail' => $detail];
        $this->hasFailed = true;
    }

    private function statusIcon(string $status): string
    {
        return match ($status) {
            'ok' => '✓',
            'warn' => '⚠',
            'fail' => '✗',
            default => '?',
        };
    }
}
