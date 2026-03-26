---
name: laravel-ai-sdk
description: Laravel AI SDK for building AI-powered features. Use when creating agents, generating images or audio, working with embeddings, vector search, or testing AI features. Triggers on tasks involving laravel/ai, AI agents, tool-calling, structured output, streaming, embeddings, reranking, or AI faking in tests.
license: MIT
metadata:
  author: Laravel Community
  version: "1.0.0"
  laravelVersion: "13.x"
  phpVersion: "8.3+"
---

# Laravel AI SDK

Comprehensive guide for building AI-powered features with the Laravel AI SDK (`laravel/ai`). Contains 17 rules across 7 categories covering agents, tools, media generation, embeddings, vector stores, and testing.

## When to Apply

Reference these guidelines when:
- Creating AI agents with instructions, tools, and structured output
- Prompting agents with conversation context
- Streaming or queueing agent responses
- Generating images, audio, or transcriptions
- Creating and querying vector embeddings
- Building RAG (retrieval-augmented generation) features
- Testing AI features with fakes and assertions

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Agents | CRITICAL | `agent-` |
| 2 | Tools | HIGH | `tool-` |
| 3 | Embeddings & Search | HIGH | `embed-` |
| 4 | Media Generation | MEDIUM | `media-` |
| 5 | Files & Storage | MEDIUM | `files-` |
| 6 | Infrastructure | MEDIUM | `infra-` |
| 7 | Testing | HIGH | `test-` |

## Quick Reference

### 1. Agents (CRITICAL)

- `agent-create-configure` - Create agents with artisan, PHP attribute configuration
- `agent-prompting` - Prompt agents, conversation context, RemembersConversations
- `agent-structured-output` - Structured output with JSON schema
- `agent-streaming-async` - Streaming, broadcasting, and queueing responses
- `agent-middleware` - Agent middleware pipeline
- `agent-anonymous` - Anonymous agents for quick interactions

### 2. Tools (HIGH)

- `tool-create` - Create custom tools with schema and handle method
- `tool-provider` - Provider tools: WebSearch, WebFetch, FileSearch, SimilaritySearch

### 3. Embeddings & Search (HIGH)

- `embed-generate-cache` - Generate, store, and cache vector embeddings
- `embed-rerank` - Rerank documents and collections by relevance

### 4. Media Generation (MEDIUM)

- `media-images` - Generate, store, and queue images
- `media-audio-transcription` - Text-to-speech and speech-to-text

### 5. Files & Storage (MEDIUM)

- `files-vector-stores` - File storage and vector stores for RAG

### 6. Infrastructure (MEDIUM)

- `infra-failover` - Automatic provider failover for resilience

### 7. Testing (HIGH)

- `test-agents` - Fake agents, assert prompts, prevent stray prompts
- `test-media` - Fake images, audio, and transcriptions
- `test-data` - Fake embeddings, reranking, files, and vector stores

## Essential Patterns

### Creating an Agent

```php
<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach, analyzing transcripts and providing feedback.';
    }
}
```

### Prompting

```php
use App\Ai\Agents\SalesCoach;

$response = SalesCoach::make()->prompt('Analyze this sales transcript...');

return (string) $response;
```

### Generating Images

```php
use Laravel\Ai\Image;

$image = Image::of('A donut sitting on the kitchen counter')
    ->landscape()
    ->generate();

$path = $image->store();
```

### Generating Embeddings

```php
use Illuminate\Support\Str;

$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings();
```

### Testing

```php
use App\Ai\Agents\SalesCoach;

SalesCoach::fake(['First response', 'Second response']);

SalesCoach::make()->prompt('Analyze this...');

SalesCoach::assertPrompted('Analyze this...');
```

## How to Use

Read individual rule files for detailed explanations and code examples.

Each rule file contains:
- YAML frontmatter with metadata (title, impact, tags)
- Brief explanation of why it matters
- Bad Example with explanation
- Good Example with explanation
- Laravel 13 and PHP 8.3 specific context and references

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
