<?php

declare(strict_types=1);

namespace App\Providers;

use App\Settings\ActivityLogSettings;
use App\Settings\AiSettings;
use App\Settings\AppSettings;
use App\Settings\BackupSettings;
use App\Settings\BillingSettings;
use App\Settings\BroadcastingSettings;
use App\Settings\CookieConsentSettings;
use App\Settings\FeatureFlagSettings;
use App\Settings\FilesystemSettings;
use App\Settings\ImpersonateSettings;
use App\Settings\IntegrationsSettings;
use App\Settings\LemonSqueezySettings;
use App\Settings\MailSettings;
use App\Settings\MediaSettings;
use App\Settings\MemorySettings;
use App\Settings\MonitoringSettings;
use App\Settings\PaddleSettings;
use App\Settings\PerformanceSettings;
use App\Settings\PermissionSettings;
use App\Settings\PrismSettings;
use App\Settings\ScoutSettings;
use App\Settings\SecuritySettings;
use App\Settings\StripeSettings;
use App\Settings\TenancySettings;
use App\Settings\ThemeSettings;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Writes Spatie settings values into config() at boot,
 * so all config() consumers transparently read DB-backed values.
 *
 * Each entry in OVERLAY_MAP defines:
 *   - settings class
 *   - property-to-config-key mapping
 *   - whether per-org overrides are allowed
 */
final class SettingsOverlayServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string<\Spatie\LaravelSettings\Settings>, array{map: array<string, string>, orgOverridable: bool}>
     */
    public const array OVERLAY_MAP = [

        // ─── Phase 1: Core App ────────────────────────────────────

        AppSettings::class => [
            'map' => [
                'site_name' => 'app.name',
                'url' => 'app.url',
                'timezone' => 'app.timezone',
                'locale' => 'app.locale',
                'fallback_locale' => 'app.fallback_locale',
            ],
            'orgOverridable' => false,
        ],

        ThemeSettings::class => [
            'map' => [
                'preset' => 'theme.preset',
                'base_color' => 'theme.base_color',
                'radius' => 'theme.radius',
                'font' => 'theme.font',
                'default_appearance' => 'theme.default_appearance',
            ],
            'orgOverridable' => false,
        ],

        BillingSettings::class => [
            'map' => [
                'enable_seat_based_billing' => 'billing.enable_seat_based_billing',
                'allow_multiple_subscriptions' => 'billing.allow_multiple_subscriptions',
                'default_gateway' => 'billing.default_gateway',
                'currency' => 'billing.currency',
                'trial_days' => 'billing.trial_days',
                'credit_expiration_days' => 'billing.credit_expiration_days',
                'dunning_intervals' => 'billing.dunning_intervals',
                'geo_restriction_enabled' => 'billing.geo_restriction_enabled',
                'geo_blocked_countries' => 'billing.geo_blocked_countries',
                'geo_allowed_countries' => 'billing.geo_allowed_countries',
            ],
            'orgOverridable' => true,
        ],

        MailSettings::class => [
            'map' => [
                'mailer' => 'mail.default',
                'smtp_host' => 'mail.mailers.smtp.host',
                'smtp_port' => 'mail.mailers.smtp.port',
                'smtp_username' => 'mail.mailers.smtp.username',
                'smtp_password' => 'mail.mailers.smtp.password',
                'smtp_encryption' => 'mail.mailers.smtp.scheme',
                'from_address' => 'mail.from.address',
                'from_name' => 'mail.from.name',
            ],
            'orgOverridable' => true,
        ],

        TenancySettings::class => [
            'map' => [
                'enabled' => 'tenancy.enabled',
                'domain' => 'tenancy.domain',
                'subdomain_resolution' => 'tenancy.subdomain_resolution',
                'term' => 'tenancy.term',
                'term_plural' => 'tenancy.term_plural',
                'allow_user_org_creation' => 'tenancy.allow_user_organization_creation',
                'default_org_name' => 'tenancy.default_organization_name',
                'auto_create_personal_org' => 'tenancy.auto_create_personal_organization',
                'invitation_expires_in_days' => 'tenancy.invitations.expires_in_days',
                'invitation_allow_registration' => 'tenancy.invitations.allow_registration',
                'sharing_restrict_to_connected' => 'tenancy.sharing.restrict_to_connected',
                'sharing_edit_ownership' => 'tenancy.sharing.edit_ownership',
                'super_admin_can_view_all' => 'tenancy.super_admin.can_view_all',
            ],
            'orgOverridable' => false,
        ],

        // ─── Phase 2: Payment Integrations ────────────────────────

        StripeSettings::class => [
            'map' => [
                'key' => 'stripe.key',
                'secret' => 'stripe.secret',
                'webhook_secret' => 'stripe.webhook_secret',
            ],
            'orgOverridable' => true,
        ],

        PaddleSettings::class => [
            'map' => [
                'vendor_id' => 'paddle.vendor_id',
                'vendor_auth_code' => 'paddle.vendor_auth_code',
                'public_key' => 'paddle.public_key',
                'webhook_secret' => 'paddle.webhook_secret',
                'sandbox' => 'paddle.sandbox',
            ],
            'orgOverridable' => true,
        ],

        LemonSqueezySettings::class => [
            'map' => [
                'api_key' => 'lemon-squeezy.api_key',
                'signing_secret' => 'lemon-squeezy.signing_secret',
                'store' => 'lemon-squeezy.store',
                'path' => 'lemon-squeezy.path',
                'currency_locale' => 'lemon-squeezy.currency_locale',
                'generic_variant_id' => 'services.lemon_squeezy.generic_variant_id',
            ],
            'orgOverridable' => true,
        ],

        IntegrationsSettings::class => [
            'map' => [
                'slack_webhook_url' => 'services.slack.webhook_url',
                'slack_bot_token' => 'services.slack.notifications.bot_user_oauth_token',
                'slack_channel' => 'services.slack.notifications.channel',
                'postmark_token' => 'services.postmark.token',
                'resend_key' => 'services.resend.key',
            ],
            'orgOverridable' => false,
        ],

        // ─── Phase 3: AI & Search ─────────────────────────────────

        PrismSettings::class => [
            'map' => [
                'prism_server_enabled' => 'prism.prism_server.enabled',
                'request_timeout' => 'prism.request_timeout',
                'default_provider' => 'prism.defaults.provider',
                'default_model' => 'prism.defaults.model',
                'openai_api_key' => 'prism.providers.openai.api_key',
                'anthropic_api_key' => 'prism.providers.anthropic.api_key',
                'groq_api_key' => 'prism.providers.groq.api_key',
                'xai_api_key' => 'prism.providers.xai.api_key',
                'gemini_api_key' => 'prism.providers.gemini.api_key',
                'deepseek_api_key' => 'prism.providers.deepseek.api_key',
                'mistral_api_key' => 'prism.providers.mistral.api_key',
                'openrouter_api_key' => 'prism.providers.openrouter.api_key',
                'elevenlabs_api_key' => 'prism.providers.elevenlabs.api_key',
                'voyageai_api_key' => 'prism.providers.voyageai.api_key',
            ],
            'orgOverridable' => true,
        ],

        AiSettings::class => [
            'map' => [
                'default_provider' => 'ai.default',
                'default_for_images' => 'ai.default_for_images',
                'default_for_audio' => 'ai.default_for_audio',
                'default_for_transcription' => 'ai.default_for_transcription',
                'default_for_embeddings' => 'ai.default_for_embeddings',
                'default_for_reranking' => 'ai.default_for_reranking',
                'chat_model' => 'ai.providers.openrouter.models.text.default',
                'cohere_api_key' => 'ai.providers.cohere.key',
                'jina_api_key' => 'ai.providers.jina.key',
            ],
            'orgOverridable' => true,
        ],

        ScoutSettings::class => [
            'map' => [
                'driver' => 'scout.driver',
                'prefix' => 'scout.prefix',
                'queue' => 'scout.queue',
                'identify' => 'scout.identify',
            ],
            'orgOverridable' => false,
        ],

        MemorySettings::class => [
            'map' => [
                'dimensions' => 'memory.dimensions',
                'similarity_threshold' => 'memory.similarity_threshold',
                'recall_limit' => 'memory.recall_limit',
                'middleware_recall_limit' => 'memory.middleware_recall_limit',
                'recall_oversample_factor' => 'memory.recall_oversample_factor',
                'table' => 'memory.table',
            ],
            'orgOverridable' => false,
        ],

        // ─── Phase 4: Security & UX ──────────────────────────────

        SecuritySettings::class => [
            'map' => [
                'csp_enabled' => 'csp.enabled',
                'csp_nonce_enabled' => 'csp.nonce_enabled',
                'csp_report_uri' => 'csp.report_uri',
                'honeypot_enabled' => 'honeypot.enabled',
                'honeypot_seconds' => 'honeypot.amount_of_seconds',
            ],
            'orgOverridable' => false,
        ],

        CookieConsentSettings::class => [
            'map' => [
                'enabled' => 'cookie-consent.enabled',
            ],
            'orgOverridable' => false,
        ],

        PerformanceSettings::class => [
            'map' => [
                'cache_enabled' => 'responsecache.enabled',
                'cache_lifetime_seconds' => 'responsecache.cache_lifetime_in_seconds',
                'cache_driver' => 'responsecache.cache_store',
            ],
            'orgOverridable' => false,
        ],

        MonitoringSettings::class => [
            'map' => [
                'sentry_dsn' => 'sentry.dsn',
                'sentry_sample_rate' => 'sentry.sample_rate',
                'sentry_traces_sample_rate' => 'sentry.traces_sample_rate',
                'telescope_enabled' => 'telescope.enabled',
            ],
            'orgOverridable' => false,
        ],

        FeatureFlagSettings::class => [
            'map' => [
                'globally_disabled_modules' => 'feature-flags.globally_disabled',
            ],
            'orgOverridable' => false,
        ],

        // ─── Phase 5: Remaining Groups ────────────────────────────

        FilesystemSettings::class => [
            'map' => [
                'default_disk' => 'filesystems.default',
                's3_key' => 'filesystems.disks.s3.key',
                's3_secret' => 'filesystems.disks.s3.secret',
                's3_region' => 'filesystems.disks.s3.region',
                's3_bucket' => 'filesystems.disks.s3.bucket',
                's3_url' => 'filesystems.disks.s3.url',
            ],
            'orgOverridable' => false,
        ],

        BroadcastingSettings::class => [
            'map' => [
                'default_connection' => 'broadcasting.default',
                'reverb_app_id' => 'broadcasting.connections.reverb.app_id',
                'reverb_app_key' => 'broadcasting.connections.reverb.key',
                'reverb_app_secret' => 'broadcasting.connections.reverb.secret',
            ],
            'orgOverridable' => false,
        ],

        PermissionSettings::class => [
            'map' => [
                'teams_enabled' => 'permission.teams',
                'team_foreign_key' => 'permission.team_foreign_key',
            ],
            'orgOverridable' => false,
        ],

        ActivityLogSettings::class => [
            'map' => [
                'enabled' => 'activitylog.enabled',
                'delete_records_older_than_days' => 'activitylog.delete_records_older_than_days',
            ],
            'orgOverridable' => false,
        ],

        ImpersonateSettings::class => [
            'map' => [
                'banner_style' => 'filament-impersonate.banner.style',
            ],
            'orgOverridable' => false,
        ],

        BackupSettings::class => [
            'map' => [
                'name' => 'backup.backup.name',
                'keep_all_backups_for_days' => 'backup.cleanup.default_strategy.keep_all_backups_for_days',
                'keep_daily_backups_for_days' => 'backup.cleanup.default_strategy.keep_daily_backups_for_days',
                'keep_weekly_backups_for_weeks' => 'backup.cleanup.default_strategy.keep_weekly_backups_for_weeks',
                'keep_monthly_backups_for_months' => 'backup.cleanup.default_strategy.keep_monthly_backups_for_months',
                'keep_yearly_backups_for_years' => 'backup.cleanup.default_strategy.keep_yearly_backups_for_years',
                'delete_oldest_when_size_mb' => 'backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than',
            ],
            'orgOverridable' => false,
        ],

        MediaSettings::class => [
            'map' => [
                'disk_name' => 'media-library.disk_name',
                'max_file_size' => 'media-library.max_file_size',
            ],
            'orgOverridable' => false,
        ],
    ];

    /**
     * Cross-map PrismSettings API keys → Laravel AI SDK config keys.
     *
     * PrismSettings already overlays to prism.providers.*.api_key via OVERLAY_MAP.
     * This copies those same keys into ai.providers.*.key so both SDKs share one source of truth.
     */
    private const array AI_KEY_CROSS_MAP = [
        'openai_api_key' => 'ai.providers.openai.key',
        'anthropic_api_key' => 'ai.providers.anthropic.key',
        'groq_api_key' => 'ai.providers.groq.key',
        'xai_api_key' => 'ai.providers.xai.key',
        'gemini_api_key' => 'ai.providers.gemini.key',
        'elevenlabs_api_key' => 'ai.providers.eleven.key',
        'openrouter_api_key' => 'ai.providers.openrouter.key',
    ];

    /**
     * Write all Spatie settings values into config().
     * Can be called from tests to re-apply after changing settings.
     */
    public static function applyOverlay(): void
    {
        foreach (self::OVERLAY_MAP as $settingsClass => $config) {
            try {
                /** @var \Spatie\LaravelSettings\Settings $settings */
                $settings = resolve($settingsClass);

                foreach ($config['map'] as $property => $configKey) {
                    config()->set($configKey, $settings->{$property});
                }
            } catch (Throwable) {
                // Settings table may not exist yet (fresh install, migrations pending).
                // Skip this group silently — config defaults from env remain in effect.
                continue;
            }
        }

        // Cross-map PrismSettings API keys to Laravel AI SDK config
        try {
            $prism = resolve(PrismSettings::class);

            foreach (self::AI_KEY_CROSS_MAP as $property => $configKey) {
                config()->set($configKey, $prism->{$property});
            }
        } catch (Throwable) {
            // PrismSettings not available yet — skip silently.
        }
    }

    /**
     * Get all config keys that are overridable per-organization.
     *
     * @return array<string, string> Map of "group.property" => "config.key"
     */
    public static function orgOverridableKeys(): array
    {
        $keys = [];

        foreach (self::OVERLAY_MAP as $settingsClass => $config) {
            if (! $config['orgOverridable']) {
                continue;
            }

            $group = $settingsClass::group();

            foreach ($config['map'] as $property => $configKey) {
                $keys["{$group}.{$property}"] = $configKey;
            }
        }

        return $keys;
    }

    public function boot(): void
    {
        self::applyOverlay();
    }
}
