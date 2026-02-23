<?php

declare(strict_types=1);

use Laravel\Ai\Provider;

// Managed via Filament: Settings > AI (org-overridable via SettingsOverlayServiceProvider)
return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the AI providers below should be the
    | default for AI operations when no explicit provider is provided
    | for the operation. This should be any provider defined below.
    |
    */

    'default' => 'openai',
    'default_for_images' => 'gemini',
    'default_for_audio' => 'openai',
    'default_for_transcription' => 'openai',
    'default_for_embeddings' => 'openai',
    'default_for_reranking' => 'cohere',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Below you may configure caching strategies for AI related operations
    | such as embedding generation. You are free to adjust these values
    | based on your application's available caching stores and needs.
    |
    */

    'caching' => [
        'embeddings' => [
            'cache' => false,
            'store' => 'database',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | Below are each of your AI providers defined for this application. Each
    | represents an AI provider and API key combination which can be used
    | to perform tasks like text, image, and audio creation via agents.
    |
    */

    'providers' => [
        'anthropic' => [
            'driver' => 'anthropic',
            'key' => null,
        ],

        'cohere' => [
            'driver' => 'cohere',
            'key' => null,
        ],

        'eleven' => [
            'driver' => 'eleven',
            'key' => null,
        ],

        'gemini' => [
            'driver' => 'gemini',
            'key' => null,
        ],

        'groq' => [
            'driver' => 'groq',
            'key' => null,
        ],

        'jina' => [
            'driver' => 'jina',
            'key' => null,
        ],

        'openai' => [
            'driver' => 'openai',
            'key' => null,
        ],

        'openrouter' => [
            'driver' => 'openrouter',
            'key' => null,
            'models' => [
                'text' => [
                    'default' => 'anthropic/claude-sonnet-4.5',
                ],
            ],
        ],

        'xai' => [
            'driver' => 'xai',
            'key' => null,
        ],
    ],

];
