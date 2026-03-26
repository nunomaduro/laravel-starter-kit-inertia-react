---
title: Create Composite Indexes Matching Query Patterns
impact: CRITICAL
impactDescription: "Serves multi-column filters from a single index scan"
tags: indexes, composite, query-optimization
---

## Create Composite Indexes Matching Query Patterns

**Impact: CRITICAL (Serves multi-column filters from a single index scan)**

When queries filter on multiple columns, separate single-column indexes force the database to choose one index and scan remaining rows. A composite index matching the query's column order lets the database satisfy the entire WHERE clause in a single index scan, often improving performance by 10-100x on large tables.

## Incorrect

```php
// ❌ Separate single-column indexes for a multi-column query
Schema::create('orders', function (Blueprint $table): void {
    $table->id();
    $table->string('status');
    $table->timestamp('created_at');
    $table->decimal('total', 10, 2);
    $table->foreignId('user_id')->constrained();

    $table->index('status');      // Single-column index
    $table->index('created_at');  // Single-column index
});

// This query can only use ONE of the two indexes:
Order::where('status', 'pending')
    ->where('created_at', '>', now()->subDays(30))
    ->get();
// Database picks one index, then scans remaining rows — slow on large tables
```

**Problems:**
- MySQL and PostgreSQL can only efficiently use one single-column index per table in a query
- The database must scan all rows matching the first index to apply the second filter
- Index merge operations are unreliable and often slower than a single composite index

## Correct

```php
// ✅ Composite index matching the query pattern
// Rule: equality columns first, range columns last
Schema::create('orders', function (Blueprint $table): void {
    $table->id();
    $table->string('status');
    $table->timestamp('created_at');
    $table->decimal('total', 10, 2);
    $table->foreignId('user_id')->constrained();

    // Equality column (status) first, range column (created_at) last
    $table->index(['status', 'created_at']);
});

// This query uses the full composite index efficiently:
$recentPending = Order::where('status', 'pending')
    ->where('created_at', '>', now()->subDays(30))
    ->get();

// ✅ Another example: user dashboard with status filter
Schema::table('orders', function (Blueprint $table): void {
    $table->index(['user_id', 'status', 'created_at']);
});

// Matches this common query pattern:
$userOrders = Order::where('user_id', $userId)
    ->where('status', 'completed')
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

**Benefits:**
- Single index scan satisfies the entire WHERE clause — no post-filtering required
- Equality-first, range-last ordering maximizes index selectivity
- The leftmost prefix rule means the composite index also serves queries using only the first column(s)

Reference: [Laravel Index Columns](https://laravel.com/docs/13.x/migrations#creating-indexes)
