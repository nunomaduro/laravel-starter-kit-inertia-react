---
title: Testing Embeddings, Reranking, Files, and Vector Stores
impact: HIGH
impactDescription: Fake data operations in tests
tags: testing, embeddings, reranking, files, vector-stores, fake, assert
---

## Testing Embeddings, Reranking, Files, and Vector Stores

**Impact: HIGH (Fake data operations in tests)**

Fake embeddings, reranking, file storage, and vector store operations to avoid real API calls and provider dependencies in tests.

## Bad Example

```php
// Real embedding generation in tests — slow, costs money, needs API key
test('stores document with embedding', function () {
    $embedding = Str::of($content)->toEmbeddings(); // Real API call
    Document::create(['content' => $content, 'embedding' => $embedding]);
});
```

## Good Example

```php
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

// Fake embeddings — auto-generates vectors of proper dimensions
test('generates document embeddings', function () {
    Embeddings::fake();

    $response = Embeddings::for(['Laravel is great.'])->generate();

    Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
        return $prompt->contains('Laravel');
    });
});
```

```php
// Fake with specific vectors
test('generates specific embeddings', function () {
    Embeddings::fake([
        [$firstEmbeddingVector],
        [$secondEmbeddingVector],
    ]);

    $response = Embeddings::for(['Input text.'])->generate();
});
```

```php
// Prevent stray embedding calls
test('no unexpected embeddings', function () {
    Embeddings::fake()->preventStrayEmbeddings();
});
```

```php
use Laravel\Ai\Reranking;
use Laravel\Ai\Prompts\RerankingPrompt;
use Laravel\Ai\Responses\Data\RankedDocument;

// Fake reranking
test('reranks search results', function () {
    Reranking::fake();

    $response = Reranking::of(['Doc A', 'Doc B'])->rerank('query');

    Reranking::assertReranked(function (RerankingPrompt $prompt) {
        return $prompt->contains('query');
    });
});

// Fake with specific ranked results
test('returns expected ranking', function () {
    Reranking::fake([
        [
            new RankedDocument(index: 0, document: 'First', score: 0.95),
            new RankedDocument(index: 1, document: 'Second', score: 0.80),
        ],
    ]);
});
```

```php
use Laravel\Ai\Files;
use Laravel\Ai\Contracts\Files\StorableFile;
use Laravel\Ai\Files\Document;

// Fake file operations
test('stores document with provider', function () {
    Files::fake();

    Document::fromString('Hello, Laravel!', mimeType: 'text/plain')
        ->as('hello.txt')
        ->put();

    Files::assertStored(fn (StorableFile $file) =>
        (string) $file === 'Hello, Laravel!' &&
        $file->mimeType() === 'text/plain'
    );

    Files::assertNothingDeleted();
});
```

```php
use Laravel\Ai\Stores;

// Fake vector store operations (also fakes file operations)
test('creates knowledge base store', function () {
    Stores::fake();

    $store = Stores::create('Knowledge Base');

    Stores::assertCreated('Knowledge Base');

    // Assert files added to store
    $store->add(Document::fromString('Content', 'text/plain')->as('doc.txt'));
    $store->assertAdded(fn (StorableFile $file) => $file->name() === 'doc.txt');
});
```

## Why

- **Fast**: No embedding API calls or provider connections
- **Deterministic**: Controlled fake vectors and rankings
- **Comprehensive**: Fake files, stores, and all data operations in one test
- **Assertable**: Verify what was generated, stored, added, and removed

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)
