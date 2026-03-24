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

    'currency' => 'usd',

    'trial_days' => 14,

    'credit_expiration_days' => 365,

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

    /*
    |--------------------------------------------------------------------------
    | Plan → Feature Access Mapping
    |--------------------------------------------------------------------------
    |
    | Each plan slug maps to an array of feature keys it includes.
    | Features with plan_required = null are available to ALL plans (no gating).
    | Features listed here are ONLY available to the plans that include them.
    | Higher-tier plans should include all lower-tier features explicitly.
    |
    */
    'plan_features' => [
        'basic' => [],
        'pro' => [
            'api_access',
        ],
        'team' => [
            'api_access',
        ],
        'platform-solo' => [
            'api_access',
        ],
        'platform-agency' => [
            'api_access',
            'crm',
            'reports',
        ],
        'platform-enterprise' => [
            'api_access',
            'crm',
            'reports',
            'dashboards',
            'workflows',
        ],
    ],

];
