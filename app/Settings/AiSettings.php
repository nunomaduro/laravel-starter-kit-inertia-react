<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class AiSettings extends Settings
{
    public string $default_provider = 'openai';

    public string $default_for_images = 'gemini';

    public string $default_for_audio = 'openai';

    public string $default_for_transcription = 'openai';

    public string $default_for_embeddings = 'openai';

    public string $default_for_reranking = 'cohere';

    public ?string $chat_model = null;

    public ?string $cohere_api_key = null;

    public ?string $jina_api_key = null;

    public static function group(): string
    {
        return 'ai';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['cohere_api_key', 'jina_api_key'];
    }
}
