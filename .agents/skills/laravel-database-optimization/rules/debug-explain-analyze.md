---
title: Run EXPLAIN on Slow Queries
impact: MEDIUM
impactDescription: "Identifies missing indexes, full table scans, and inefficient query plans"
tags: explain, query-plan, debugging, slow-queries
---

## Run EXPLAIN on Slow Queries

**Impact: MEDIUM (Identifies missing indexes, full table scans, and inefficient query plans)**

Guessing why a query is slow wastes time and leads to wrong fixes. Running EXPLAIN shows the actual execution plan the database uses — revealing missing indexes, full table scans, and inefficient join strategies that no amount of code reading can uncover.

## Incorrect

```php
// ❌ Guessing at performance issues without data
// "It's probably the JOIN that's slow" — adds an index on the wrong column
// "Let's add caching" — masks the real problem instead of fixing the query

// No visibility into:
// - Whether indexes are being used
// - How many rows the database is scanning
// - Whether the query plan uses sequential scans or index lookups
```

**Problems:**
- Optimizing without EXPLAIN often targets the wrong bottleneck
- Adding indexes blindly wastes storage and slows writes without improving reads
- Caching is a band-aid — the underlying query may grow slower as data increases

## Correct

```php
// ✅ Use Laravel's built-in explain() method
$explanation = DB::table('orders')
    ->where('customer_id', $customerId)
    ->where('status', 'pending')
    ->explain()
    ->dd();

// Output shows: type, possible_keys, key, rows, Extra
// Look for:
//   type: "ALL" = full table scan (bad)
//   type: "ref" or "const" = index used (good)
//   rows: high number = scanning too many rows
//   Extra: "Using filesort" = sorting without index

// ✅ EXPLAIN ANALYZE for actual execution times (MySQL 8.0+, PostgreSQL)
DB::statement('EXPLAIN ANALYZE SELECT * FROM orders WHERE customer_id = ? AND status = ?', [
    $customerId,
    'pending',
]);

// ✅ Programmatic explain in tests or debugging
$query = Order::where('customer_id', $customerId)
    ->where('status', 'pending');

// Log the raw SQL for manual EXPLAIN
Log::debug('Query', [
    'sql' => $query->toRawSql(),
    'explain' => $query->explain()->toArray(),
]);

// ✅ What to look for in EXPLAIN output:
// | Issue              | EXPLAIN Sign                    | Fix                        |
// |--------------------|---------------------------------|----------------------------|
// | Full table scan    | type: ALL                       | Add index on WHERE columns |
// | No index used      | key: NULL                       | Create composite index     |
// | Sorting without    | Extra: Using filesort           | Add index covering ORDER   |
// | index              |                                 | BY columns                 |
// | Scanning too many  | rows: 500000+                   | Add more selective index   |
// | rows               |                                 |                            |
```

**Benefits:**
- Data-driven optimization instead of guesswork — EXPLAIN shows exactly what the database does
- Quickly identifies whether an index exists but isn't being used vs. missing entirely
- EXPLAIN ANALYZE shows actual vs. estimated row counts, revealing stale statistics

Reference: [Laravel Query Builder](https://laravel.com/docs/13.x/queries)
