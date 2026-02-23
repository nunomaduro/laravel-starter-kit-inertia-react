<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Supported: "stripe", "paddle", "manual"
    | Managed via Filament: Settings > Billing
    |
    */
    'default_gateway' => 'stripe',

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => 'usd',

    /*
    |--------------------------------------------------------------------------
    | Trial Days
    |--------------------------------------------------------------------------
    */
    'trial_days' => 14,

    /*
    |--------------------------------------------------------------------------
    | Credit Expiration (days)
    |--------------------------------------------------------------------------
    */
    'credit_expiration_days' => 365,

    /*
    |--------------------------------------------------------------------------
    | Dunning Reminder Intervals (days after failure)
    |--------------------------------------------------------------------------
    */
    'dunning_intervals' => [
        3,
        7,
        14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy: Cents per Credit (fallback when custom_data.credits not set)
    |--------------------------------------------------------------------------
    | Used by AddCreditsFromLemonSqueezyOrder when deriving credits from order total.
    | Set to 0 to disable fallback (requires custom_data.credits in checkout).
    */
    'lemon_squeezy_cents_per_credit' => 10,

    /*
    |--------------------------------------------------------------------------
    | Geo-restriction (laravel-geo-genius)
    |--------------------------------------------------------------------------
    */
    'geo_restriction_enabled' => false,
    'geo_blocked_countries' => [],
    'geo_allowed_countries' => [],

];
