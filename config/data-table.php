<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Per Page
    |--------------------------------------------------------------------------
    |
    | The default number of rows displayed per page when the user hasn't
    | explicitly chosen a page size.
    |
    */
    'default_per_page' => 25,

    /*
    |--------------------------------------------------------------------------
    | Maximum Per Page
    |--------------------------------------------------------------------------
    |
    | The maximum number of rows a user can select to display per page.
    | This prevents excessive server load from large page sizes.
    |
    */
    'max_per_page' => 100,

    /*
    |--------------------------------------------------------------------------
    | Default Polling Interval
    |--------------------------------------------------------------------------
    |
    | The default auto-refresh interval in seconds. Set to 0 to disable
    | polling by default. Individual tables can override this value.
    |
    */
    'default_polling_interval' => 0,

    /*
    |--------------------------------------------------------------------------
    | LocalStorage Key Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for localStorage keys when persisting table state
    | like column visibility, column order, density, etc.
    |
    */
    'storage_prefix' => 'dt-',

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to all data table routes (export, inline-edit,
    | toggle, detail-row, etc).
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for all data table API routes.
    |
    */
    'route_prefix' => 'data-table',

    /*
    |--------------------------------------------------------------------------
    | Default Translations
    |--------------------------------------------------------------------------
    |
    | Override default English translation strings. These are passed to
    | the frontend via the config. Set to null to use built-in defaults.
    |
    */
    'translations' => null,

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Configure defaults for data table exports.
    |
    */
    'export' => [
        'queue' => false,
        'disk' => null,
        'max_rows' => 50000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Settings
    |--------------------------------------------------------------------------
    |
    | Configure defaults for data table imports.
    |
    */
    'import' => [
        'max_file_size' => 10240, // KB
        'allowed_extensions' => ['csv', 'xlsx', 'xls'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Maximum number of requests per minute for mutation endpoints.
    | Set to 0 to disable rate limiting for a specific endpoint.
    |
    */
    'rate_limit' => [
        'inline_edit' => 60,
        'toggle' => 60,
        'export' => 10,
        'import' => 5,
        'ai' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Features (Laravel AI SDK or Prism PHP)
    |--------------------------------------------------------------------------
    |
    | Configure AI-powered features for data tables. Thesys C1 (Visualize tab)
    | uses the app-level Thesys API key from config('services.thesys.api_key').
    |
    */
    'ai' => [
        'model' => env('DATA_TABLE_AI_MODEL'),
        'max_tokens' => null,
        'sample_size' => 50,
        'thesys_api_key' => config('services.thesys.api_key'),
        'thesys_model' => config('services.thesys.model'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Table
    |--------------------------------------------------------------------------
    |
    | The database table name used for storing audit log entries.
    | Used by the HasAuditLog trait and the data-table:audit-report command.
    |
    */
    'audit_table' => 'data_table_audit_log',

];
