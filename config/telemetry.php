<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Telemetry
    |--------------------------------------------------------------------------
    |
    | Opt-in usage tracking for product analytics. When enabled, events like
    | module installs, AI queries, and command usage are logged locally.
    | No data is sent to external services.
    |
    */

    'enabled' => env('TELEMETRY_ENABLED', false),

];
