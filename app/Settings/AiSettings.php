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

    /**
     * Thesys C1 API key (DataTable Visualize, etc.). When null, env THESYS_API_KEY is used until overlay runs.
     */
    public ?string $thesys_api_key = null;

    public string $thesys_model = 'c1-nightly';

    public static function group(): string
    {
        return 'ai';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['cohere_api_key', 'jina_api_key', 'thesys_api_key'];
    }
}
