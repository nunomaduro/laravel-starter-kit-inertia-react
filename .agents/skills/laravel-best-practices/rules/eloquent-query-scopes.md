---
title: Query Scopes for Reusable Queries
impact: HIGH
impactDescription: DRY principle for database queries
tags: eloquent, scopes, queries, reusability
---

## Query Scopes for Reusable Queries

**Impact: HIGH (DRY principle for database queries)**

Encapsulate reusable query constraints in model scopes for cleaner, more maintainable code.

## Bad Example

```php
// Repeated query logic scattered across controllers
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::where('published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();

        return view('posts.index', compact('posts'));
    }

    public function featured()
    {
        // Same logic duplicated
        $posts = Post::where('published', true)
            ->where('published_at', '<=', now())
            ->where('is_featured', true)
            ->orderBy('published_at', 'desc')
            ->get();

        return view('posts.featured', compact('posts'));
    }
}

class ApiPostController extends Controller
{
    public function index()
    {
        // Again, duplicated logic
        $posts = Post::where('published', true)
            ->where('published_at', '<=', now())
            ->paginate(20);

        return PostResource::collection($posts);
    }
}
```

## Good Example

```php
// Model with reusable scopes
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Local scope - called as scopePublished, used as published()
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeByAuthor(Builder $query, User $author): Builder
    {
        return $query->where('author_id', $author->id);
    }

    public function scopeInCategory(Builder $query, int|Category $category): Builder
    {
        $categoryId = $category instanceof Category ? $category->id : $category;
        return $query->where('category_id', $categoryId);
    }

    // Dynamic scope with parameters
    public function scopeCreatedBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Scope with optional parameter
    public function scopePopular(Builder $query, int $minViews = 1000): Builder
    {
        return $query->where('views', '>=', $minViews);
    }
}
```

```php
// Clean controller using scopes
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()->recent()->get();

        return view('posts.index', compact('posts'));
    }

    public function featured()
    {
        $posts = Post::published()
            ->featured()
            ->recent()
            ->get();

        return view('posts.featured', compact('posts'));
    }

    public function byAuthor(User $author)
    {
        $posts = Post::published()
            ->byAuthor($author)
            ->recent()
            ->paginate(20);

        return view('posts.by-author', compact('posts', 'author'));
    }
}
```

```php
// Global scope - automatically applied to all queries
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PublishedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('published', true);
    }
}
```

```php
// Apply global scope in model
class Post extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new PublishedScope);

        // Or inline
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('deleted_at', null);
        });
    }
}

// Remove global scope when needed
Post::withoutGlobalScope(PublishedScope::class)->get();
Post::withoutGlobalScope('active')->get();
Post::withoutGlobalScopes()->get(); // Remove all
```

## Why

- **DRY**: Query logic defined once, used everywhere
- **Readable**: Expressive, chainable method names
- **Maintainable**: Changes to query logic happen in one place
- **Testable**: Scopes can be unit tested
- **Encapsulation**: Query details hidden in the model
- **Chainable**: Scopes can be combined fluently
