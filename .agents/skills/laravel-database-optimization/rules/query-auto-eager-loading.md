---
title: Use Automatic Eager Loading (Laravel 13+)
impact: CRITICAL
impactDescription: "Eliminates N+1 queries without manual with() calls"
tags: auto-eager-loading, n-plus-one, laravel-12
---

## Use Automatic Eager Loading (Laravel 13+)

**Impact: CRITICAL (Eliminates N+1 queries without manual with() calls)**

Laravel 13 introduces automatic eager loading, which detects when multiple models in a collection access the same relationship and batches those queries automatically. This eliminates the most common source of N+1 bugs without requiring developers to remember every `with()` call.

## Incorrect

```php
// ❌ Manually adding with() everywhere — easy to miss some
$posts = Post::with(['author', 'tags', 'comments'])->get();

// Elsewhere in the codebase, someone forgets:
$posts = Post::where('published', true)->get();
foreach ($posts as $post) {
    echo $post->author->name; // N+1 — forgot with('author')
}

// Or in a Blade template where with() can't be added:
@foreach ($posts as $post)
    {{ $post->category->name }} {{-- N+1 — no way to fix from the view --}}
@endforeach
```

**Problems:**
- Every query site must remember to include the right `with()` calls
- Blade templates and third-party packages cannot add `with()` themselves
- Missing a single eager load silently introduces N+1 problems

## Correct

```php
// ✅ Option 1: Enable globally in AppServiceProvider
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();
    }
}

// Now any collection access auto-batches relationship queries:
$posts = Post::where('published', true)->get();
foreach ($posts as $post) {
    echo $post->author->name; // Auto-batched — no N+1
}

// ✅ Option 2: Enable per-collection for targeted use
$posts = Post::where('published', true)->get();
$posts->withRelationshipAutoloading();

foreach ($posts as $post) {
    echo $post->author->name;       // Auto-batched
    echo $post->category->name;     // Auto-batched
}
```

**Benefits:**
- Eliminates N+1 queries globally without modifying existing query code
- Works in Blade templates, packages, and any code that iterates collections
- Per-collection option provides fine-grained control when global activation is too broad

Reference: [Laravel Automatic Eager Loading](https://laravel.com/docs/13.x/eloquent-relationships#automatic-eager-loading)
