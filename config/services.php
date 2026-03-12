<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    // Managed via Filament: Settings > Integrations
    'postmark' => [
        'token' => null,
    ],

    // SES uses infrastructure AWS credentials — keep env()
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Managed via Filament: Settings > Integrations
    'resend' => [
        'key' => null,
    ],

    // Managed via Filament: Settings > Integrations
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => null,
            'channel' => null,
        ],
        'webhook_url' => null,
    ],

    'example_api' => [
        'url' => env('EXAMPLE_API_URL', 'https://jsonplaceholder.typicode.com'),
    ],

    // Managed via Filament: Settings > Auth
    // Redirect URLs are computed at boot by SettingsOverlayServiceProvider from the DB-backed app.url.
    'google' => [
        'client_id' => null,
        'client_secret' => null,
        'redirect' => 'http://localhost/auth/google/callback',
    ],

    // Managed via Filament: Settings > Auth
    // Redirect URLs are computed at boot by SettingsOverlayServiceProvider from the DB-backed app.url.
    'github' => [
        'client_id' => null,
        'client_secret' => null,
        'redirect' => 'http://localhost/auth/github/callback',
    ],

    // Managed via Filament: Settings > Lemon Squeezy
    'lemon_squeezy' => [
        'api_key' => null,
        'store_id' => null,
        'signing_secret' => null,
        'generic_variant_id' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Thesys (C1 generative UI)
    |--------------------------------------------------------------------------
    |
    | API key from https://www.thesys.dev — used for DataTable AI Visualize and
    | any other Thesys C1 features. Set THESYS_API_KEY in .env (optional);
    | when empty, Thesys-dependent features are disabled.
    |
    */
    'thesys' => [
        // DB-backed via AiSettings overlay when set; env used until first save.
        'api_key' => env('THESYS_API_KEY'),
        'model' => env('THESYS_MODEL', 'c1-nightly'),
    ],

];
