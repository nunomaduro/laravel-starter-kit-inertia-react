<?php

declare(strict_types=1);

namespace Modules\BotStudio\Providers;

use App\Modules\Contracts\ProvidesAIContext;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\BotStudio\Contracts\ProvidesAgentTemplates;
use Modules\BotStudio\Features\BotStudioFeature;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Policies\AgentDefinitionPolicy;

final class BotStudioModuleServiceProvider extends ModuleProvider implements ProvidesAIContext
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'Bot Studio',
            version: '1.0.0',
            description: 'Create, customize, and deploy custom AI agents',
            models: [
                AgentDefinition::class,
            ],
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
        Gate::policy(AgentDefinition::class, AgentDefinitionPolicy::class);

        $this->syncTemplatesFromProviders();
    }

    /**
     * Collect templates from all loaded service providers that implement ProvidesAgentTemplates
     * and create any missing AgentDefinition template rows.
     */
    private function syncTemplatesFromProviders(): void
    {
        $this->app->booted(function (): void {
            /** @var array<int, \Illuminate\Support\ServiceProvider> $providers */
            $providers = $this->app->getLoadedProviders();

            foreach (array_keys($providers) as $providerClass) {
                $provider = $this->app->getProvider($providerClass);

                if (! $provider instanceof ProvidesAgentTemplates) {
                    continue;
                }

                foreach ($provider->agentTemplates() as $template) {
                    AgentDefinition::query()->firstOrCreate(
                        [
                            'is_template' => true,
                            'name' => $template['name'],
                        ],
                        [
                            'organization_id' => null,
                            'description' => $template['description'],
                            'system_prompt' => $template['system_prompt'],
                            'model' => $template['model'],
                            'temperature' => $template['temperature'],
                            'max_tokens' => 2048,
                            'enabled_tools' => $template['enabled_tools'],
                            'conversation_starters' => $template['conversation_starters'],
                            'wizard_answers' => $template['wizard_answers'],
                            'is_template' => true,
                            'is_published' => true,
                            'is_featured' => false,
                        ],
                    );
                }
            }
        });
    }
}
