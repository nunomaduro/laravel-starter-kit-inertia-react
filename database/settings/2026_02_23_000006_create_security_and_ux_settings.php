<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Security
        $this->migrator->add('security.csp_enabled', (bool) config('csp.enabled', true));
        $this->migrator->add('security.csp_nonce_enabled', (bool) config('csp.nonce_enabled', false));
        $this->migrator->add('security.csp_report_uri', config('csp.report_uri', ''));
        $this->migrator->add('security.honeypot_enabled', (bool) config('honeypot.enabled', true));
        $this->migrator->add('security.honeypot_seconds', (int) config('honeypot.amount_of_seconds', 1));
        $this->migrator->add('security.ip_whitelist', []);

        // Cookie Consent
        $this->migrator->add('cookie-consent.enabled', (bool) config('cookie-consent.enabled', true));

        // Performance
        $this->migrator->add('performance.cache_enabled', (bool) config('responsecache.enabled', false));
        $this->migrator->add('performance.cache_lifetime_seconds', (int) config('responsecache.cache_lifetime_in_seconds', 604800));
        $this->migrator->add('performance.cache_driver', config('responsecache.cache_store', 'file'));

        // Monitoring
        $this->migrator->addEncrypted('monitoring.sentry_dsn', config('sentry.dsn'));
        $this->migrator->add('monitoring.sentry_sample_rate', (float) config('sentry.sample_rate', 1.0));
        $this->migrator->add('monitoring.sentry_traces_sample_rate', config('sentry.traces_sample_rate'));
        $this->migrator->add('monitoring.telescope_enabled', (bool) config('telescope.enabled', true));

        // Feature Flags
        $this->migrator->add('feature-flags.globally_disabled_modules', config('feature-flags.globally_disabled', []));
    }
};
