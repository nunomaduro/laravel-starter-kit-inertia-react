<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Prism
        $this->migrator->add('prism.prism_server_enabled', (bool) config('prism.prism_server.enabled', false));
        $this->migrator->add('prism.request_timeout', (int) config('prism.request_timeout', 30));
        $this->migrator->add('prism.default_provider', config('prism.defaults.provider', 'openrouter'));
        $this->migrator->add('prism.default_model', config('prism.defaults.model', 'deepseek/deepseek-r1-0528:free'));
        $this->migrator->addEncrypted('prism.openai_api_key', config('prism.providers.openai.api_key'));
        $this->migrator->addEncrypted('prism.anthropic_api_key', config('prism.providers.anthropic.api_key'));
        $this->migrator->addEncrypted('prism.groq_api_key', config('prism.providers.groq.api_key'));
        $this->migrator->addEncrypted('prism.xai_api_key', config('prism.providers.xai.api_key'));
        $this->migrator->addEncrypted('prism.gemini_api_key', config('prism.providers.gemini.api_key'));
        $this->migrator->addEncrypted('prism.deepseek_api_key', config('prism.providers.deepseek.api_key'));
        $this->migrator->addEncrypted('prism.mistral_api_key', config('prism.providers.mistral.api_key'));
        $this->migrator->addEncrypted('prism.openrouter_api_key', config('prism.providers.openrouter.api_key'));
        $this->migrator->addEncrypted('prism.elevenlabs_api_key', config('prism.providers.elevenlabs.api_key'));
        $this->migrator->addEncrypted('prism.voyageai_api_key', config('prism.providers.voyageai.api_key'));

        // AI
        $this->migrator->add('ai.default_provider', config('ai.default', 'openai'));
        $this->migrator->add('ai.default_for_images', config('ai.default_for_images', 'gemini'));
        $this->migrator->add('ai.default_for_audio', config('ai.default_for_audio', 'openai'));
        $this->migrator->add('ai.default_for_transcription', config('ai.default_for_transcription', 'openai'));
        $this->migrator->add('ai.default_for_embeddings', config('ai.default_for_embeddings', 'openai'));
        $this->migrator->add('ai.default_for_reranking', config('ai.default_for_reranking', 'cohere'));
        $this->migrator->add('ai.chat_model', config('ai.providers.openrouter.models.text.default'));

        // Scout
        $this->migrator->add('scout.driver', config('scout.driver', 'collection'));
        $this->migrator->add('scout.prefix', config('scout.prefix', ''));
        $this->migrator->add('scout.queue', (bool) config('scout.queue', false));
        $this->migrator->add('scout.identify', (bool) config('scout.identify', false));
        $this->migrator->addEncrypted('scout.typesense_api_key', config('scout.typesense.client-settings.api_key'));
        $this->migrator->add('scout.typesense_host', config('scout.typesense.client-settings.nodes.0.host', 'localhost'));
        $this->migrator->add('scout.typesense_port', (int) config('scout.typesense.client-settings.nodes.0.port', 8108));
        $this->migrator->add('scout.typesense_protocol', config('scout.typesense.client-settings.nodes.0.protocol', 'http'));

        // Memory
        $this->migrator->add('memory.dimensions', (int) config('memory.dimensions', 1536));
        $this->migrator->add('memory.similarity_threshold', (float) config('memory.similarity_threshold', 0.5));
        $this->migrator->add('memory.recall_limit', (int) config('memory.recall_limit', 10));
        $this->migrator->add('memory.middleware_recall_limit', (int) config('memory.middleware_recall_limit', 5));
        $this->migrator->add('memory.recall_oversample_factor', (int) config('memory.recall_oversample_factor', 2));
        $this->migrator->add('memory.table', config('memory.table', 'memories'));
    }
};
