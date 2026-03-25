<?php

declare(strict_types=1);

namespace Modules\BotStudio\Providers;

use App\Modules\Contracts\ProvidesAIContext;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Modules\BotStudio\Features\BotStudioFeature;

final class BotStudioModuleServiceProvider extends ModuleProvider implements ProvidesAIContext
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'Bot Studio',
            version: '1.0.0',
            description: 'Create, customize, and deploy custom AI agents',
            models: [],
            pages: [
                'bot-studio.index' => 'bot-studio/index',
                'bot-studio.create' => 'bot-studio/create',
                'bot-studio.edit' => 'bot-studio/edit',
            ],
            navigation: [
                ['label' => 'Bot Studio', 'route' => 'bot-studio.index', 'icon' => 'bot', 'group' => 'AI'],
            ],
        );
    }

    public function systemPrompt(): string
    {
        return <<<'PROMPT'
        ## Bot Studio Module
        This module enables users to create, customize, and deploy custom AI agents (bots):
        - **Bots**: Custom AI agents with configurable system prompts, models, temperature, and behavior settings
        - **Knowledge Bases**: Document collections that bots can reference for RAG (Retrieval-Augmented Generation)
        - **Conversations**: Chat interactions between users and bots with full message history
        - **Templates**: Pre-built bot configurations for common use cases

        Key capabilities: prompt engineering, model selection, knowledge base attachment, conversation management.
        All data is scoped to the current organization (multi-tenant).
        PROMPT;
    }

    public function tools(): array
    {
        return [];
    }

    public function searchableModels(): array
    {
        return [];
    }

    protected function featureClass(): ?string
    {
        return BotStudioFeature::class;
    }

    protected function bootModule(): void
    {
        //
    }
}
