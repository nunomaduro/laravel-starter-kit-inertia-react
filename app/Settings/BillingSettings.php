<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class BillingSettings extends Settings
{
    public bool $enable_seat_based_billing = false;

    public bool $allow_multiple_subscriptions = false;

    public string $default_gateway = 'stripe';

    public string $currency = 'usd';

    public int $trial_days = 14;

    public int $credit_expiration_days = 365;

    /** @var array<int> */
    public array $dunning_intervals = [3, 7, 14];

    public bool $geo_restriction_enabled = false;

    /** @var array<string> */
    public array $geo_blocked_countries = [];

    /** @var array<string> */
    public array $geo_allowed_countries = [];

    public static function group(): string
    {
        return 'billing';
    }
}
