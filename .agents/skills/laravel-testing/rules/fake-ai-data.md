---
title: Fake AI Embeddings, Reranking, Files, and Vector Stores
impact: HIGH
impactDescription: Test AI data operations without real API calls
tags: testing, ai, embeddings, reranking, files, vector-stores, fake, assert, laravel-ai-sdk
---

## Fake AI Embeddings, Reranking, Files, and Vector Stores

**Impact: HIGH (Test AI data operations without real API calls)**

Use `Embeddings::fake()`, `Reranking::fake()`, `Files::fake()`, and `Stores::fake()` to test data operations without provider connections.

## Bad Example — Pest

```php
<?php

use Illuminate\Support\Str;

test('stores document with embedding', function () {
    // Real API call to generate embedding — slow, costs money
    $embedding = Str::of($content)->toEmbeddings();
    Document::create(['content' => $content, 'embedding' => $embedding]);
});
```

## Good Example — Pest

```php
<?php

use Laravel\Ai\Embeddings;
use Laravel\Ai\Files;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Reranking;
use Laravel\Ai\Stores;
use Laravel\Ai\Contracts\Files\StorableFile;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Laravel\Ai\Prompts\RerankingPrompt;

test('generates document embeddings', function () {
    Embeddings::fake();

    Embeddings::for(['Laravel is great.'])->generate();

    Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
        return $prompt->contains('Laravel');
    });
});

test('reranks search results', function () {
    Reranking::fake();

    Reranking::of(['Doc A', 'Doc B'])->rerank('query');

    Reranking::assertReranked(function (RerankingPrompt $prompt) {
        return $prompt->contains('query');
    });
});

test('stores document with provider', function () {
    Files::fake();

    Document::fromString('Hello, Laravel!', mimeType: 'text/plain')
        ->as('hello.txt')
        ->put();

    Files::assertStored(fn (StorableFile $file) =>
        (string) $file === 'Hello, Laravel!'
    );
});

test('creates vector store and adds files', function () {
    Stores::fake(); // Also fakes file operations

    $store = Stores::create('Knowledge Base');
    Stores::assertCreated('Knowledge Base');

    $store->add(Document::fromString('Content', 'text/plain')->as('doc.txt'));
    $store->assertAdded(fn (StorableFile $file) => $file->name() === 'doc.txt');
});

test('no unexpected data operations', function () {
    Embeddings::fake()->preventStrayEmbeddings();
    Files::fake();
    Stores::fake();
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Tests\TestCase;

class EmbeddingTest extends TestCase
{
    public function test_generates_document_embeddings(): void
    {
        Embeddings::fake();

        Embeddings::for(['Laravel is great.'])->generate();

        Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
            return $prompt->contains('Laravel');
        });
    }
}
```

## Why It Matters

- **Fast**: No embedding API calls or provider connections
- **Deterministic**: `Embeddings::fake()` auto-generates vectors of proper dimensions
- **Comprehensive**: `Stores::fake()` also fakes file operations automatically
- **Assertable**: Verify what was generated, stored, added, and removed

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)
