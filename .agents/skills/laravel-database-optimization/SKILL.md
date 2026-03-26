---
name: laravel-database-optimization
description: Laravel database optimization patterns. Use when writing Eloquent queries, creating migrations, configuring caching, debugging slow queries, or optimizing database performance. Triggers on tasks involving N+1 queries, indexing, Redis caching, pagination, or database transactions.
license: MIT
metadata:
  author: agent-skills
  version: "1.1.1"
---

# Laravel Database Optimization

Comprehensive database optimization guide for Laravel 13 applications. Contains 33 rules across 9 categories for writing performant database queries, proper indexing, efficient caching, naming conventions, and debugging slow queries in Laravel 13.

## Metadata

- **Version:** 1.1.0
- **Framework:** Laravel 13.x
- **PHP:** 8.3+

## When to Apply

Reference these guidelines when:
- Writing Eloquent queries or using the query builder
- Diagnosing and fixing N+1 query problems
- Adding database indexes to migrations
- Implementing Redis or cache-based optimizations
- Paginating or processing large datasets
- Wrapping operations in database transactions
- Creating or modifying migrations for production databases
- Debugging slow queries with EXPLAIN or Laravel Debugbar

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Query Performance & N+1 | CRITICAL | `query-` |
| 2 | Indexing Strategies | CRITICAL | `index-` |
| 3 | Eloquent Optimization | HIGH | `eloquent-` |
| 4 | Caching with Redis | HIGH | `cache-` |
| 5 | Pagination & Large Datasets | HIGH | `data-` |
| 6 | Transactions & Locking | HIGH | `lock-` |
| 7 | Migrations | HIGH | `migrate-` |
| 8 | Query Debugging | MEDIUM | `debug-` |
| 9 | Naming & Structure | HIGH | `naming-` |

## Quick Reference

### 1. Query Performance & N+1 (CRITICAL)

- `query-eager-loading` - Use eager loading to eliminate N+1 queries
- `query-prevent-lazy-loading` - Prevent lazy loading in development
- `query-auto-eager-loading` - Configure automatic eager loading on models
- `query-select-columns` - Select only needed columns instead of SELECT *

### 2. Indexing Strategies (CRITICAL)

- `index-foreign-keys` - Index all foreign key columns
- `index-composite-indexes` - Create composite indexes for multi-column queries
- `index-covering-indexes` - Use covering indexes for read-heavy queries
- `index-full-text` - Use full-text indexes for search functionality

### 3. Eloquent Optimization (HIGH)

- `eloquent-query-builder-hot-paths` - Use query builder for performance-critical paths
- `eloquent-with-count-aggregates` - Use withCount instead of loading relations to count
- `eloquent-subquery-selects` - Use subquery selects to avoid extra queries
- `eloquent-where-has-optimization` - Optimize whereHas with whereIn subqueries

### 4. Caching with Redis (HIGH)

- `cache-remember` - Use Cache::remember for expensive queries
- `cache-invalidation` - Invalidate cache on model changes
- `cache-tags` - Use cache tags for group invalidation
- `cache-ttl` - Set appropriate TTL values for cached data

### 5. Pagination & Large Datasets (HIGH)

- `data-cursor-pagination` - Use cursor pagination for large datasets
- `data-chunk-by-id` - Process large datasets with chunkById
- `data-cursor-iteration` - Use lazy cursors for memory-efficient iteration
- `data-avoid-unbounded` - Never use unbounded queries on large tables

### 6. Transactions & Locking (HIGH)

- `lock-short-transactions` - Keep transactions short and focused
- `lock-deadlock-retry` - Implement deadlock retry logic
- `lock-pessimistic-locking` - Use pessimistic locking for critical updates

### 7. Migrations (HIGH)

- `migrate-zero-downtime` - Write zero-downtime migrations
- `migrate-concurrent-indexes` - Create indexes concurrently in production
- `migrate-safe-column-additions` - Add columns safely without locking tables

### 8. Query Debugging (MEDIUM)

- `debug-explain-analyze` - Use EXPLAIN ANALYZE to understand query plans
- `debug-laravel-debugbar` - Use Laravel Debugbar to find query bottlenecks
- `debug-slow-query-log` - Enable and monitor slow query logs

### 9. Naming & Structure (HIGH)

- `naming-tables` - Table naming conventions (plural snake_case, pivot alphabetical)
- `naming-columns` - Column naming conventions (FKs, booleans, timestamps, polymorphic)
- `naming-relationships` - Relationship method naming (singular/plural matching)
- `naming-migrations` - Migration and index naming conventions

## Essential Patterns

### Prevent Lazy Loading in Development

```php
<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
    }
}
```

### Cache Expensive Queries with Redis

```php
<?php

use Illuminate\Support\Facades\Cache;

// Cache a query result for 1 hour (3600 seconds)
$popularPosts = Cache::remember('posts:popular', 3600, fn () =>
    Post::query()
        ->withCount('comments')
        ->orderByDesc('comments_count')
        ->take(10)
        ->get()
);
```

### Cursor Pagination for Large Datasets

```php
<?php

// Cursor pagination — efficient for infinite scroll and large tables
$posts = Post::query()
    ->where('published_at', '<=', now())
    ->orderByDesc('published_at')
    ->cursorPaginate(15);
```

### Aggregate Counts Without Loading Relations

```php
<?php

// Instead of loading all posts just to count them
$users = User::withCount('posts')->get();

foreach ($users as $user) {
    echo "{$user->name} has {$user->posts_count} posts";
}
```

### Process Large Datasets with chunkById

```php
<?php

// Memory-efficient processing of large tables
User::query()
    ->where('last_login_at', '<', now()->subYear())
    ->chunkById(1000, function ($users) {
        foreach ($users as $user) {
            $user->update(['status' => 'inactive']);
        }
    });
```

### Short Database Transactions

```php
<?php

use Illuminate\Support\Facades\DB;

// Keep transactions short and focused
DB::transaction(function () {
    $order = Order::create([
        'user_id' => auth()->id(),
        'total' => $this->calculateTotal(),
    ]);

    $order->items()->createMany($this->cartItems());

    $order->user->decrement('credits', $order->total);
});
```

## How to Use

Read individual rule files for detailed explanations and code examples:

```
rules/query-eager-loading.md
rules/index-composite-indexes.md
rules/cache-remember.md
rules/_sections.md
```

Each rule file contains:
- YAML frontmatter with metadata (title, impact, tags)
- Brief explanation of why it matters
- Bad Example with explanation
- Good Example with explanation
- Laravel 13 and PHP 8.3 specific context and references

## References

- [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent)
- [Laravel Queries](https://laravel.com/docs/13.x/queries)
- [Laravel Cache](https://laravel.com/docs/13.x/cache)
- [Laravel Pagination](https://laravel.com/docs/13.x/pagination)
- [Laravel Migrations](https://laravel.com/docs/13.x/migrations)
- [Laravel Redis](https://laravel.com/docs/13.x/redis)

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
