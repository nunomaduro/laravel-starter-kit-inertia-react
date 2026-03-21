<?php

declare(strict_types=1);

namespace App\Modules\Contracts;

/**
 * Modules implement this to feed the AI assistant with domain knowledge.
 *
 * Returns: (a) system prompt fragment for the module's domain,
 * (b) tool definitions for querying module data, (c) models to index for RAG.
 */
interface ProvidesAIContext
{
    /**
     * System prompt fragment describing this module's domain and terminology.
     */
    public function systemPrompt(): string;

    /**
     * MCP tool definitions for querying this module's data.
     *
     * @return array<int, array{name: string, description: string, handler: class-string}>
     */
    public function tools(): array;

    /**
     * Eloquent model classes to index for RAG/semantic search.
     *
     * @return array<int, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public function searchableModels(): array;
}
