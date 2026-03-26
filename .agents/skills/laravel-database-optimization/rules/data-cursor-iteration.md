---
title: Use cursor() for Memory-Efficient Iteration
impact: HIGH
impactDescription: "Reduces memory from O(n) to O(1) — iterate millions of rows without exhausting RAM"
tags: cursor, lazy, memory, large-datasets
---

## Use cursor() for Memory-Efficient Iteration

**Impact: HIGH (Reduces memory from O(n) to O(1) — iterate millions of rows without exhausting RAM)**

Loading an entire result set with `all()` or `get()` hydrates every row into an Eloquent model simultaneously, consuming memory proportional to the result size. `cursor()` uses a PHP generator to fetch and hydrate one row at a time, keeping memory usage constant regardless of how many rows are processed.

## Incorrect

```php
// ❌ Loads all 500,000 users into memory at once
$users = User::all();

$users->each(function (User $user): void {
    dispatch(new SendWeeklyDigest($user));
});

// Peak memory: ~500,000 Eloquent models × ~2KB each ≈ 1GB+ RAM
```

**Problems:**
- Entire result set is hydrated into memory before iteration begins
- Memory usage grows linearly with row count — easily causes out-of-memory errors
- No benefit from loading all rows upfront when processing them sequentially

## Correct

```php
// ✅ Eloquent cursor — fetches one model at a time via PHP generator
User::where('subscribed', true)
    ->cursor()
    ->each(function (User $user): void {
        dispatch(new SendWeeklyDigest($user));
    });

// Peak memory: ~1 Eloquent model at a time, regardless of total rows

// ✅ Query Builder lazy() — same concept without Eloquent hydration overhead
DB::table('users')
    ->where('subscribed', true)
    ->lazy()
    ->each(function (object $user): void {
        dispatch(new SendWeeklyDigest($user));
    });

// ✅ lazy() with chunk size control
DB::table('users')
    ->lazyById(500, 'id')
    ->each(function (object $user): void {
        // Fetches 500 rows at a time internally, yields one at a time
        dispatch(new SendWeeklyDigest($user));
    });
```

**Benefits:**
- Constant memory usage regardless of result set size — safe for millions of rows
- `cursor()` returns a `LazyCollection` backed by a PHP generator
- `lazyById()` combines the memory efficiency of generators with the consistency of ID-based chunking

Reference: [Laravel Cursors](https://laravel.com/docs/13.x/eloquent#cursors)
