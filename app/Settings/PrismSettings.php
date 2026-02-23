<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class PrismSettings extends Settings
{
    public bool $prism_server_enabled = false;

    public int $request_timeout = 30;

    public string $default_provider = 'openrouter';

    public string $default_model = 'deepseek/deepseek-r1-0528:free';

    public ?string $openai_api_key = null;

    public ?string $anthropic_api_key = null;

    public ?string $groq_api_key = null;

    public ?string $xai_api_key = null;

    public ?string $gemini_api_key = null;

    public ?string $deepseek_api_key = null;

    public ?string $mistral_api_key = null;

    public ?string $openrouter_api_key = null;

    public ?string $elevenlabs_api_key = null;

    public ?string $voyageai_api_key = null;

    public static function group(): string
    {
        return 'prism';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return [
            'openai_api_key', 'anthropic_api_key', 'groq_api_key', 'xai_api_key',
            'gemini_api_key', 'deepseek_api_key', 'mistral_api_key', 'openrouter_api_key',
            'elevenlabs_api_key', 'voyageai_api_key',
        ];
    }
}
