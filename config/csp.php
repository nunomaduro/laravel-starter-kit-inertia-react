<?php

declare(strict_types=1);

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Nonce\RandomString;
use Spatie\Csp\Presets\Basic;

return [

    /*
     * Presets will determine which CSP headers will be set. A valid CSP preset is
     * any class that implements `Spatie\Csp\Preset`
     */
    'presets' => [
        Basic::class,
    ],

    /**
     * Additional global CSP directives for Inertia + Vite compatibility.
     */
    'directives' => array_filter([
        [Directive::STYLE, array_filter([
            Keyword::SELF,
            Keyword::UNSAFE_INLINE,
            'https://fonts.bunny.net',
            'https://fonts.googleapis.com',
            'https://unpkg.com',
            env('APP_ENV') === 'local' ? 'http://localhost:5173' : null,
        ])],

        [Directive::IMG, [Keyword::SELF, 'data:', 'blob:', 'https:']],

        [Directive::FONT, [Keyword::SELF, 'data:', 'https://fonts.bunny.net', 'https://fonts.gstatic.com']],

        [Directive::CONNECT, array_filter([
            Keyword::SELF,
            env('APP_ENV') === 'local' ? 'ws://localhost:5173' : null,
            env('APP_ENV') === 'local' ? 'wss://localhost:5173' : null,
            env('APP_ENV') === 'local' ? 'http://localhost:5173' : null,
        ])],

        in_array(env('APP_ENV'), ['local', 'testing'], true)
            ? [Directive::SCRIPT, [Keyword::SELF, Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE, 'http://localhost:5173', 'https://unpkg.com']]
            : [Directive::SCRIPT, [Keyword::SELF, Keyword::UNSAFE_INLINE, 'https://unpkg.com']],
    ]),

    'report_only_presets' => [],

    'report_only_directives' => [],

    // Managed via Filament: Settings > Security
    'report_uri' => '',

    'enabled' => true,

    'enabled_while_hot_reloading' => true,

    'nonce_generator' => RandomString::class,

    /*
     * Disabled for Inertia/Vite compatibility (single script bundle, no per-script nonces).
     */
    'nonce_enabled' => false,
];
