<?php

declare(strict_types=1);

// Managed via Filament: Settings > Prism (org-overridable via SettingsOverlayServiceProvider)
return [
    'prism_server' => [
        'middleware' => [],
        'enabled' => false,
    ],
    'request_timeout' => 30,
    'defaults' => [
        'provider' => 'openrouter',
        'model' => 'deepseek/deepseek-r1-0528:free',
        'models' => [
            'openrouter' => 'deepseek/deepseek-r1-0528:free',
            'openai' => 'gpt-4o-mini',
            'anthropic' => 'claude-3-5-sonnet-20241022',
            'ollama' => 'llama3.2',
            'mistral' => 'mistral-small-latest',
        ],
    ],
    'providers' => [
        'openai' => [
            'url' => 'https://api.openai.com/v1',
            'api_key' => env('OPENAI_API_KEY', ''),
            'organization' => null,
            'project' => null,
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'version' => '2023-06-01',
            'url' => 'https://api.anthropic.com/v1',
            'default_thinking_budget' => 1024,
            'anthropic_beta' => null,
        ],
        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        ],
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY', ''),
            'url' => 'https://api.mistral.ai/v1',
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY', ''),
            'url' => 'https://api.groq.com/openai/v1',
        ],
        'xai' => [
            'api_key' => env('XAI_API_KEY', ''),
            'url' => 'https://api.x.ai/v1',
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY', ''),
            'url' => 'https://generativelanguage.googleapis.com/v1beta/models',
        ],
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY', ''),
            'url' => 'https://api.deepseek.com/v1',
        ],
        'elevenlabs' => [
            'api_key' => env('ELEVENLABS_API_KEY', ''),
            'url' => 'https://api.elevenlabs.io/v1/',
        ],
        'voyageai' => [
            'api_key' => env('VOYAGEAI_API_KEY', ''),
            'url' => 'https://api.voyageai.com/v1',
        ],
        'openrouter' => [
            'api_key' => env('OPENROUTER_API_KEY', ''),
            'url' => 'https://openrouter.ai/api/v1',
            'site' => [
                'http_referer' => null,
                'x_title' => null,
            ],
        ],
    ],
];
