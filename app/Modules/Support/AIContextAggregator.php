<?php

declare(strict_types=1);

namespace App\Modules\Support;

use App\Modules\Contracts\ProvidesAIContext;
use Illuminate\Support\Facades\Cache;

/**
 * Aggregates AI context from all installed modules.
 *
 * Merges system prompts, tool definitions, and searchable models from every
 * module that implements ProvidesAIContext. Includes cross-module relationships
 * from the ModuleRelationshipRegistry.
 */
final class AIContextAggregator
{
    private const string CACHE_KEY = 'module_ai_context';

    /**
     * @param  array<int, ProvidesAIContext>  $providers
     */
    public function __construct(
        private readonly array $providers = [],
    ) {}

    /**
     * Invalidate the cached context (call on module install/uninstall).
     */
    public static function invalidate(): void
    {
        Cache::forget(self::CACHE_KEY.'_prompt');
    }

    /**
     * Get the aggregated system prompt for the AI assistant.
     */
    public function systemPrompt(): string
    {
        $cached = Cache::get(self::CACHE_KEY.'_prompt');

        if (is_string($cached)) {
            return $cached;
        }

        $prompt = $this->buildSystemPrompt();
        Cache::put(self::CACHE_KEY.'_prompt', $prompt, now()->addHour());

        return $prompt;
    }

    /**
     * Get all MCP tool definitions from installed modules.
     *
     * @return array<int, array{name: string, description: string, handler: class-string}>
     */
    public function tools(): array
    {
        $tools = [];

        foreach ($this->providers as $provider) {
            $tools = [...$tools, ...$provider->tools()];
        }

        return $tools;
    }

    /**
     * Get all models that should be indexed for RAG.
     *
     * @return array<int, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public function searchableModels(): array
    {
        $models = [];

        foreach ($this->providers as $provider) {
            $models = [...$models, ...$provider->searchableModels()];
        }

        return array_unique($models);
    }

    private function buildSystemPrompt(): string
    {
        $parts = ["You are an AI assistant for a corporate application.\n"];

        // Module-specific context
        foreach ($this->providers as $provider) {
            $modulePrompt = mb_trim($provider->systemPrompt());

            if ($modulePrompt !== '') {
                $parts[] = $modulePrompt;
            }
        }

        // Cross-module relationships
        $relationships = ModuleRelationshipRegistry::all();

        if ($relationships !== []) {
            $parts[] = "\n## Cross-Module Relationships";

            foreach ($relationships as $source => $rels) {
                foreach ($rels as $rel) {
                    $parts[] = "- {$source} {$rel->type} {$rel->targetModel} (via {$rel->foreignKey})";
                }
            }
        }

        return implode("\n", $parts);
    }
}
