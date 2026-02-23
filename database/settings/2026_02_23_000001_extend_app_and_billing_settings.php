<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.locale', config('app.locale', 'en'));
        $this->migrator->add('app.fallback_locale', config('app.fallback_locale', 'en'));

        $this->migrator->add('billing.default_gateway', config('billing.default_gateway', 'stripe'));
        $this->migrator->add('billing.currency', config('billing.currency', 'usd'));
        $this->migrator->add('billing.trial_days', (int) config('billing.trial_days', 14));
        $this->migrator->add('billing.credit_expiration_days', (int) config('billing.credit_expiration_days', 365));
        $this->migrator->add('billing.dunning_intervals', config('billing.dunning_intervals', [3, 7, 14]));
        $this->migrator->add('billing.geo_restriction_enabled', (bool) config('billing.geo_restriction_enabled', false));
        $this->migrator->add('billing.geo_blocked_countries', config('billing.geo_blocked_countries', []));
        $this->migrator->add('billing.geo_allowed_countries', config('billing.geo_allowed_countries', []));
    }
};
