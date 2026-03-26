---
title: Use chunkById for Batch Processing
impact: HIGH
impactDescription: "Prevents skipped/duplicated rows during batch operations on mutable data"
tags: chunking, batch, memory, processing
---

## Use chunkById for Batch Processing

**Impact: HIGH (Prevents skipped/duplicated rows during batch operations on mutable data)**

When processing large datasets in batches, `chunk()` uses OFFSET-based queries which can skip or duplicate rows if data is modified during iteration. `chunkById()` uses `WHERE id > last_id` to guarantee every row is processed exactly once, even when rows are inserted, updated, or deleted mid-batch.

## Incorrect

```php
// ❌ chunk() uses OFFSET — rows can be skipped or duplicated if data changes
User::where('status', 'pending')->chunk(1000, function (Collection $users): void {
    foreach ($users as $user) {
        $user->update(['status' => 'processed']);
    }
});

// Chunk 1: SELECT * FROM users WHERE status = 'pending' LIMIT 1000 OFFSET 0
// After updating 1000 rows, they no longer match 'pending'
// Chunk 2: SELECT * FROM users WHERE status = 'pending' LIMIT 1000 OFFSET 1000
// OFFSET 1000 now skips the NEXT 1000 unprocessed rows — data loss
```

**Problems:**
- OFFSET shifts when rows are inserted or deleted, causing rows to be skipped or processed twice
- Updating the column used in the WHERE clause during iteration compounds the problem
- Silent data corruption — no error is raised, but rows are missed

## Correct

```php
// ✅ chunkById() uses WHERE id > last_id — consistent regardless of mutations
User::where('status', 'pending')
    ->chunkById(1000, function (Collection $users): void {
        foreach ($users as $user) {
            $user->update(['status' => 'processed']);
        }
    });

// Chunk 1: SELECT * FROM users WHERE status = 'pending' AND id > 0 ORDER BY id LIMIT 1000
// Chunk 2: SELECT * FROM users WHERE status = 'pending' AND id > 1000 ORDER BY id LIMIT 1000
// Each chunk starts after the last processed ID — no rows skipped

// ✅ Practical example: batch status update with logging
User::where('email_verified_at', null)
    ->chunkById(500, function (Collection $users): void {
        $ids = $users->pluck('id');

        User::whereIn('id', $ids)->update([
            'status' => 'unverified',
            'flagged_at' => now(),
        ]);

        Log::info('Flagged unverified users', ['count' => $ids->count()]);
    });
```

**Benefits:**
- Every row is processed exactly once, regardless of concurrent inserts, updates, or deletes
- Uses indexed primary key lookups instead of OFFSET scanning
- Safe for operations that modify the rows being iterated over

Reference: [Laravel Chunking Results](https://laravel.com/docs/13.x/eloquent#chunking-results)
