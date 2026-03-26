---
title: Use Cache::remember() Pattern
impact: HIGH
impactDescription: "Atomic cache population eliminates redundant database queries and race conditions"
tags: cache, redis, remember, performance
---

## Use Cache::remember() Pattern

**Impact: HIGH (Atomic cache population eliminates redundant database queries and race conditions)**

Manually checking the cache and repopulating it in separate steps creates a race condition where multiple concurrent requests can miss the cache simultaneously and all hit the database. `Cache::remember()` handles this atomically in a single call.

## Incorrect

```php
// ❌ Manual get/put — race condition on cache miss
$products = Cache::get('products:featured');

if ($products === null) {
    // Multiple concurrent requests can reach here simultaneously
    $products = Product::where('featured', true)
        ->with('category')
        ->get();

    Cache::put('products:featured', $products, 3600);
}

return $products;
```

**Problems:**
- Race condition: concurrent requests during cache miss all execute the expensive query
- Cache stampede on high-traffic endpoints — database gets hammered when cache expires
- More code to maintain and more opportunities for bugs (e.g., forgetting to set TTL)
- No guarantee the cached value matches what was just queried (another request may overwrite)

## Correct

```php
// ✅ Cache::remember() — atomic get-or-set with TTL
$products = Cache::remember('products:featured', 3600, function () {
    return Product::where('featured', true)
        ->with('category')
        ->get();
});

// ✅ Cache::rememberForever() — for rarely-changing reference data
$countries = Cache::rememberForever('reference:countries', function () {
    return Country::orderBy('name')->get();
});

// ✅ With dynamic keys for per-user or per-entity caching
$userStats = Cache::remember(
    "users:{$user->id}:stats",
    now()->addMinutes(15),
    function () use ($user) {
        return [
            'posts_count' => $user->posts()->count(),
            'followers_count' => $user->followers()->count(),
            'total_likes' => $user->posts()->withSum('reactions', 'count')->sum('reactions_sum_count'),
        ];
    }
);
```

**Benefits:**
- Atomic operation: only one request regenerates the cache on miss
- Eliminates cache stampede — concurrent requests wait for or get the freshly cached value
- Cleaner, less error-prone code with TTL and closure in a single call
- `rememberForever()` variant for data that changes only via explicit invalidation

Reference: [Laravel Cache Usage](https://laravel.com/docs/13.x/cache#retrieve-store)
