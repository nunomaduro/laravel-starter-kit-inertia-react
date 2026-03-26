---
title: Rerank Documents by Relevance
impact: MEDIUM
impactDescription: Improve search quality with semantic reranking
tags: rerank, search, relevance, collection, cohere, jina
---

## Rerank Documents by Relevance

**Impact: MEDIUM (Improve search quality with semantic reranking)**

Rerank search results using semantic understanding to surface the most relevant documents. Works with arrays, Eloquent collections, and closures.

## Bad Example

```php
// Relying solely on keyword matching — misses semantic relevance
$posts = Post::where('title', 'like', '%PHP%')
    ->orWhere('body', 'like', '%PHP%')
    ->orderBy('created_at', 'desc')
    ->get();
// "PHP frameworks" query returns all posts mentioning PHP, in wrong order
```

## Good Example

```php
// Rerank an array of strings
use Laravel\Ai\Reranking;

$response = Reranking::of([
    'Django is a Python web framework.',
    'Laravel is a PHP web application framework.',
    'React is a JavaScript library for building user interfaces.',
])->rerank('PHP frameworks');

$response->first()->document; // "Laravel is a PHP web application framework."
$response->first()->score;    // 0.95
$response->first()->index;    // 1 (original position)
```

```php
// Limit results
$response = Reranking::of($documents)
    ->limit(5)
    ->rerank('search query');
```

```php
// Rerank Eloquent collections — single field
$posts = Post::all()->rerank('body', 'Laravel tutorials');

// Multiple fields (sent as JSON)
$posts = Post::all()->rerank(['title', 'body'], 'Laravel tutorials');

// Custom closure to build the document string
$posts = Post::all()->rerank(
    fn ($post) => $post->title . ': ' . $post->body,
    'Laravel tutorials',
);
```

```php
// Specify provider and limit
use Laravel\Ai\Enums\Lab;

$reranked = $posts->rerank(
    by: 'content',
    query: 'Laravel tutorials',
    limit: 10,
    provider: Lab::Cohere,
);
```

## Why

- **Semantic relevance**: Reranking understands meaning, not just keywords
- **Collection macro**: Works directly on Eloquent collections — `$posts->rerank(...)`
- **Flexible input**: Single field, multiple fields, or custom closure
- **Provider support**: Cohere and Jina for reranking

Reference: [Laravel AI SDK — Reranking](https://laravel.com/docs/13.x/ai-sdk#reranking)
