---
title: Enable preventLazyLoading in Development
impact: CRITICAL
impactDescription: "Catches N+1 bugs before they reach production"
tags: lazy-loading, n-plus-one, development, debugging
---

## Enable preventLazyLoading in Development

**Impact: CRITICAL (Catches N+1 bugs before they reach production)**

Without lazy loading prevention, N+1 query bugs are invisible — code works correctly but fires hundreds of unnecessary queries. Enabling `preventLazyLoading` in non-production environments throws an exception the moment a relationship is lazily loaded, forcing developers to fix the problem immediately.

## Incorrect

```php
// ❌ No lazy loading prevention — N+1 bugs silently reach production
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Nothing here to catch lazy loading
    }
}

// This code works but fires 101 queries — nobody notices until production crawls
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Silent N+1, no error raised
}
```

**Problems:**
- N+1 queries go completely undetected during development and code review
- Performance regressions accumulate silently until production degrades noticeably
- Developers have no feedback loop to learn about missing eager loads

## Correct

```php
// ✅ Prevent lazy loading in non-production environments
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}

// Now this code throws LazyLoadingViolationException in dev/staging:
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // 💥 LazyLoadingViolationException thrown
}

// Developer is forced to fix it:
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // ✅ No exception, 2 queries total
}
```

**Benefits:**
- Throws `LazyLoadingViolationException` immediately when a relationship is lazily loaded
- Catches N+1 problems during development before they reach production
- Safe to enable — only active in non-production environments, so production is never affected

Reference: [Laravel Preventing Lazy Loading](https://laravel.com/docs/13.x/eloquent-relationships#preventing-lazy-loading)
