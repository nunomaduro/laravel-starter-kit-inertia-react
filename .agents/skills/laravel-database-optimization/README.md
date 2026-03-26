# Laravel Database Optimization v1.1.1

Database optimization patterns and best practices for Laravel 13 applications.

## Overview

This skill provides 33 rules across 9 categories for optimizing database performance in Laravel 13 applications. Covers query optimization, indexing, caching, pagination, transactions, migrations, naming conventions, and debugging.

## Categories

### 1. Query Performance & N+1 (Critical)
Eager loading, preventing lazy loading, and selecting only needed columns.

### 2. Indexing Strategies (Critical)
Foreign key indexes, composite indexes, covering indexes, and full-text search.

### 3. Eloquent Optimization (High)
Query builder for hot paths, withCount aggregates, subquery selects, and whereHas optimization.

### 4. Caching with Redis (High)
Cache::remember patterns, cache invalidation, cache tags, and TTL strategies.

### 5. Pagination & Large Datasets (High)
Cursor pagination, chunkById processing, lazy cursors, and avoiding unbounded queries.

### 6. Transactions & Locking (High)
Short transactions, deadlock retry logic, and pessimistic locking.

### 7. Migrations (High)
Zero-downtime migrations, concurrent index creation, and safe column additions.

### 8. Query Debugging (Medium)
EXPLAIN ANALYZE, Laravel Debugbar, and slow query log monitoring.

### 9. Naming & Structure (High)
Table, column, relationship, and migration naming conventions for Eloquent.

## Quick Start

```php
// Prevent lazy loading in development
Model::preventLazyLoading(!app()->isProduction());

// Cache expensive queries
$posts = Cache::remember('posts:popular', 3600, fn () =>
    Post::withCount('comments')
        ->orderByDesc('comments_count')
        ->take(10)
        ->get()
);

// Cursor pagination for large datasets
$posts = Post::query()
    ->orderByDesc('published_at')
    ->cursorPaginate(15);

// Aggregate counts without loading relations
$users = User::withCount('posts')->get();
```

## Usage

This skill triggers automatically when:
- Writing Eloquent queries or using the query builder
- Adding database indexes to migrations
- Implementing caching strategies
- Paginating or processing large datasets
- Debugging slow queries

## References

- [Laravel Eloquent](https://laravel.com/docs/13.x/eloquent)
- [Laravel Queries](https://laravel.com/docs/13.x/queries)
- [Laravel Cache](https://laravel.com/docs/13.x/cache)
- [Laravel Pagination](https://laravel.com/docs/13.x/pagination)
- [Laravel Migrations](https://laravel.com/docs/13.x/migrations)
- [Laravel Redis](https://laravel.com/docs/13.x/redis)
