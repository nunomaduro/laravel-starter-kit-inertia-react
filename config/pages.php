<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Page templates (Puck JSON)
    |--------------------------------------------------------------------------
    |
    | Predefined puck_json documents for "Create from template". Each entry
    | has 'name' (label) and 'data' (root + content for Puck).
    |
    */
    'templates' => [
        'blank' => [
            'name' => 'Blank',
            'data' => ['root' => (object) [], 'content' => []],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Puck JSON validation
    |--------------------------------------------------------------------------
    |
    | Max number of content items (blocks) and allowed component type names.
    | Used by StorePageRequest and UpdatePageRequest to prevent abuse.
    |
    */
    'puck' => [
        'max_content_items' => (int) env('PUCK_MAX_CONTENT_ITEMS', 200),
        'allowed_components' => [
            'Heading', 'Text', 'Hero', 'Features', 'Cta', 'CardBlock', 'DataListBlock',
        ],
    ],
];
