---
title: Vector / Semantic Search
impact: MEDIUM
impactDescription: AI-powered similarity search with pgvector
tags: eloquent, database, vector, embeddings, search, ai, pgvector
---

## Vector / Semantic Search

**Impact: MEDIUM (AI-powered similarity search with pgvector)**

Laravel 13 adds native vector column support and similarity query methods for PostgreSQL with pgvector. Use these to build semantic search, recommendation engines, and RAG (retrieval-augmented generation) features.

## Bad Example

```php
// Manual similarity calculation — slow, no index, error-prone
class DocumentController extends Controller
{
    public function search(Request $request)
    {
        $queryEmbedding = $this->generateEmbedding($request->input('query'));

        // Fetching ALL documents and computing similarity in PHP
        $documents = Document::all();

        $results = $documents->map(function ($doc) use ($queryEmbedding) {
            $similarity = $this->cosineSimilarity(
                json_decode($doc->embedding),
                $queryEmbedding
            );
            $doc->similarity = $similarity;
            return $doc;
        })->sortByDesc('similarity')->take(10);

        return $results;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        // Manual cosine similarity — reinventing the wheel
        $dot = array_sum(array_map(fn ($x, $y) => $x * $y, $a, $b));
        $magA = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $a)));
        $magB = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $b)));
        return $dot / ($magA * $magB);
    }
}
```

## Good Example

```php
// Migration — use vector column with pgvector extension
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::ensureVectorExtensionExists();

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->vector('embedding', dimensions: 1536)->index();
            $table->timestamps();
        });
    }
};
```

```php
// Model — cast vector column to array
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['title', 'content', 'embedding'];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }
}
```

```php
// Query — use whereVectorSimilarTo for similarity search
use App\Models\Document;

// Pass a string — Laravel auto-generates embeddings
$documents = Document::query()
    ->whereVectorSimilarTo('embedding', 'best wineries in Napa Valley')
    ->limit(10)
    ->get();

// Pass a pre-computed embedding array
$documents = Document::query()
    ->whereVectorSimilarTo('embedding', $queryEmbedding, minSimilarity: 0.4)
    ->limit(10)
    ->get();
```

```php
// Advanced — select distance, filter, and order independently
$documents = Document::query()
    ->select('*')
    ->selectVectorDistance('embedding', $queryEmbedding, as: 'distance')
    ->whereVectorDistanceLessThan('embedding', $queryEmbedding, maxDistance: 0.3)
    ->orderByVectorDistance('embedding', $queryEmbedding)
    ->limit(10)
    ->get();
```

```php
// Generate embeddings with Laravel AI SDK (Laravel 13+)
use Illuminate\Support\Str;

$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings();

// Store document with embedding
Document::create([
    'title' => 'Wine Guide',
    'content' => $content,
    'embedding' => Str::of($content)->toEmbeddings(),
]);
```

## Why

- **Database-level search**: pgvector handles similarity computation — orders of magnitude faster than PHP
- **Indexed**: HNSW index enables sub-millisecond similarity search on millions of rows
- **Native integration**: `whereVectorSimilarTo` works with Eloquent builder — chain with scopes, pagination, etc.
- **Auto-embedding**: Pass a string and Laravel generates embeddings automatically via AI SDK
- **PostgreSQL only**: Requires PostgreSQL with pgvector extension

Reference: [Laravel 13 Documentation — Queries](https://laravel.com/docs/13.x/queries#vector-similarity-clauses)
