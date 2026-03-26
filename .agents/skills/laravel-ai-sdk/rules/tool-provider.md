---
title: Provider Tools
impact: HIGH
impactDescription: Built-in web search, URL fetching, file search, and similarity search
tags: tool, provider, web-search, web-fetch, file-search, similarity-search, rag
---

## Provider Tools

**Impact: HIGH (Built-in web search, URL fetching, file search, and similarity search)**

Provider tools are executed by the AI provider itself — web searching, URL fetching, and file searching. SimilaritySearch runs locally against your database for RAG.

## Bad Example

```php
// Building custom web search tool from scratch
class WebSearchTool implements Tool
{
    public function handle(Request $request): string
    {
        // Manual Google API integration — unnecessary
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://www.googleapis.com/customsearch/v1', [
            'query' => ['key' => config('services.google.key'), 'q' => $request['query']],
        ]);

        return (string) $response->getBody();
    }
}
```

## Good Example

```php
// WebSearch — provider searches the web for real-time info
use Laravel\Ai\Providers\Tools\WebSearch;

public function tools(): iterable
{
    return [
        new WebSearch,
    ];
}

// With limits and domain restrictions
(new WebSearch)->max(5)->allow(['laravel.com', 'php.net']);

// With location context
(new WebSearch)->location(city: 'New York', region: 'NY', country: 'US');
```

```php
// WebFetch — provider reads web page contents
use Laravel\Ai\Providers\Tools\WebFetch;

public function tools(): iterable
{
    return [
        (new WebFetch)->max(3)->allow(['docs.laravel.com']),
    ];
}
```

```php
// FileSearch — search files in vector stores (RAG)
use Laravel\Ai\Providers\Tools\FileSearch;

public function tools(): iterable
{
    return [
        new FileSearch(stores: ['store_1', 'store_2']),
    ];
}

// With metadata filters
use Laravel\Ai\Providers\Tools\FileSearchQuery;

new FileSearch(stores: ['store_id'], where: fn (FileSearchQuery $query) =>
    $query->where('author', 'Taylor Otwell')
        ->whereNot('status', 'draft')
        ->whereIn('category', ['news', 'updates'])
);
```

```php
// SimilaritySearch — search your own database with vector embeddings
use App\Models\Document;
use Laravel\Ai\Tools\SimilaritySearch;

public function tools(): iterable
{
    return [
        SimilaritySearch::usingModel(Document::class, 'embedding'),
    ];
}

// With similarity threshold and query constraints
SimilaritySearch::usingModel(
    model: Document::class,
    column: 'embedding',
    minSimilarity: 0.7,
    limit: 10,
    query: fn ($query) => $query->where('published', true),
);

// Custom closure for full control
new SimilaritySearch(using: function (string $query) {
    return Document::query()
        ->where('user_id', $this->user->id)
        ->whereVectorSimilarTo('embedding', $query)
        ->limit(10)
        ->get();
});
```

## Why

- **Zero setup**: WebSearch and WebFetch are provider-native — no API keys or configuration
- **Domain safety**: Restrict web search and fetch to trusted domains
- **RAG-ready**: FileSearch + SimilaritySearch enable retrieval-augmented generation
- **Composable**: Combine provider tools with custom tools on the same agent

Reference: [Laravel AI SDK — Provider Tools](https://laravel.com/docs/13.x/ai-sdk#provider-tools)
