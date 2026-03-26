---
title: Never Use get() on Unbounded Queries
impact: HIGH
impactDescription: "Prevents memory exhaustion and timeouts from loading unpredictable result sets"
tags: memory, unbounded, safety
---

## Never Use get() on Unbounded Queries

**Impact: HIGH (Prevents memory exhaustion and timeouts from loading unpredictable result sets)**

Calling `get()` on a query without a row limit loads every matching row into memory. A query that returns 100 rows today might return 10 million rows next year. Without explicit bounds, a single request can exhaust memory, saturate the database, and crash the application.

## Incorrect

```php
// ❌ Unbounded query — loads ALL active users into memory
$users = User::where('active', true)->get();

// Could be 100 rows in development, 5 million in production
// No limit, no pagination, no chunking — a ticking time bomb

// ❌ Unbounded query hidden inside a scope
public function scopeRecent(Builder $query): Builder
{
    return $query->where('created_at', '>', now()->subDays(30));
}

// Calling User::recent()->get() loads every user from the last 30 days
// with no upper bound
```

**Problems:**
- Memory usage is unpredictable and grows with data volume over time
- A query safe in development can cause out-of-memory crashes in production
- No early warning — the problem only surfaces when the dataset grows large enough

## Correct

```php
// ✅ Paginate for display — always bounded
$users = User::where('active', true)->paginate(25);

// ✅ Chunk for batch processing — bounded memory
User::where('active', true)->chunkById(1000, function (Collection $users): void {
    foreach ($users as $user) {
        $user->notify(new AccountReminder());
    }
});

// ✅ Cursor for sequential processing — O(1) memory
User::where('active', true)->cursor()->each(function (User $user): void {
    $user->recalculateScore();
});

// ✅ Add an explicit limit as a safety net when get() is necessary
$topUsers = User::where('active', true)
    ->orderByDesc('score')
    ->limit(100)
    ->get();

// ✅ Use DB::whenQueryingForLongerThan() to detect runaway queries
DB::whenQueryingForLongerThan(5000, function (Connection $connection, QueryExecuted $event): void {
    Log::warning('Long-running queries detected', [
        'connection' => $connection->getName(),
    ]);
});
```

**Benefits:**
- Predictable memory and time boundaries regardless of data growth
- Explicit limits make resource consumption visible and reviewable in code review
- Safety mechanisms like `whenQueryingForLongerThan()` catch issues before they escalate

Reference: [Laravel Query Builder](https://laravel.com/docs/13.x/queries)
