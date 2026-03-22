<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Theme presets (single source for ThemeSettings, Filament, org branding)
    |--------------------------------------------------------------------------
    */
    'preset' => env('THEME_PRESET', 'default'),

    'base_color' => env('THEME_BASE_COLOR', 'neutral'),

    'radius' => env('THEME_RADIUS', 'default'),

    'font' => env('THEME_FONT', 'ibm-plex-sans'),

    'default_appearance' => env('THEME_DEFAULT_APPEARANCE', 'system'), // light | dark | system

    /*
    |--------------------------------------------------------------------------
    | Preset list (keys used in ThemeSettings and org branding)
    |--------------------------------------------------------------------------
    */
    'presets' => [
        'default' => [
            'label' => 'Default',
        ],
        'vega' => [
            'label' => 'Vega',
        ],
        'nova' => [
            'label' => 'Nova',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subset of presets orgs can choose (org branding)
    |--------------------------------------------------------------------------
    */
    'org_allowed_presets' => ['default', 'vega', 'nova'],

    'base_colors' => [
        'neutral' => 'Neutral',
        'slate' => 'Slate',
        'gray' => 'Gray',
        'zinc' => 'Zinc',
        'stone' => 'Stone',
    ],

    'radii' => [
        'none' => 'None',
        'sm' => 'Small',
        'default' => 'Default',
        'md' => 'Medium',
        'lg' => 'Large',
        'full' => 'Full',
    ],

    'fonts' => [
        'ibm-plex-sans' => 'IBM Plex Sans',
        'instrument-sans' => 'Instrument Sans',
        'geist-sans' => 'Geist Sans',
        'inter' => 'Inter',
    ],

    'appearances' => [
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'System',
    ],
];
