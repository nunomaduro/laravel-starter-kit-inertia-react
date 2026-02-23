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

    // Managed via Filament: Settings > Lemon Squeezy
    'lemon_squeezy' => [
        'api_key' => null,
        'store_id' => null,
        'signing_secret' => null,
        'generic_variant_id' => null,
    ],

];
