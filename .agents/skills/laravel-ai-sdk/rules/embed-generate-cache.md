---
title: Generate and Cache Embeddings
impact: HIGH
impactDescription: Foundation for semantic search and RAG features
tags: embeddings, vector, generate, cache, pgvector, search
---

## Generate and Cache Embeddings

**Impact: HIGH (Foundation for semantic search and RAG features)**

Generate vector embeddings from text, store them in pgvector columns, and cache to avoid redundant API calls.

## Bad Example

```php
// Generating embeddings on every request — wasteful and slow
class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Calls embedding API every time, even for identical queries
        $embedding = Http::post('https://api.openai.com/v1/embeddings', [
            'input' => $request->input('query'),
            'model' => 'text-embedding-3-small',
        ])->json('data.0.embedding');

        // Manual similarity calculation
        // ...
    }
}
```

## Good Example

```php
// Generate embeddings with Stringable helper
use Illuminate\Support\Str;

$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings();
```

```php
// Batch generation for multiple inputs
use Laravel\Ai\Embeddings;

$response = Embeddings::for([
    'Napa Valley has great wine.',
    'Laravel is a PHP framework.',
])->generate();

$response->embeddings; // [[0.123, 0.456, ...], [0.789, 0.012, ...]]
```

```php
// Specify dimensions and provider
use Laravel\Ai\Enums\Lab;

$response = Embeddings::for(['Napa Valley has great wine.'])
    ->dimensions(1536)
    ->generate(Lab::OpenAI, 'text-embedding-3-small');
```

```php
// Cache embeddings to avoid redundant API calls
$response = Embeddings::for(['Napa Valley has great wine.'])
    ->cache()
    ->generate();

// Cache with custom duration
$response = Embeddings::for(['Napa Valley has great wine.'])
    ->cache(seconds: 3600)
    ->generate();

// Cache via Stringable
$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings(cache: true);
$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings(cache: 3600);
```

```php
// Enable global caching in config/ai.php
'caching' => [
    'embeddings' => [
        'cache' => true,
        'store' => env('CACHE_STORE', 'database'),
    ],
],
```

```php
// Store and query embeddings — see laravel-best-practices/eloquent-vector-search
use App\Models\Document;

Document::create([
    'title' => 'Wine Guide',
    'content' => $content,
    'embedding' => Str::of($content)->toEmbeddings(),
]);

$results = Document::query()
    ->whereVectorSimilarTo('embedding', 'best wineries in Napa Valley')
    ->limit(10)
    ->get();
```

## Why

- **Simple API**: `Str::of(...)->toEmbeddings()` — one line to generate
- **Caching**: Avoid paying for identical embedding requests
- **Batch support**: Generate multiple embeddings in a single API call
- **Provider-agnostic**: OpenAI, Gemini, Azure, Cohere, Mistral, Jina, VoyageAI

Reference: [Laravel AI SDK — Embeddings](https://laravel.com/docs/13.x/ai-sdk#embeddings)
