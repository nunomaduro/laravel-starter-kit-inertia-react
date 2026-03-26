---
title: Use Covering Indexes for Read-Heavy Queries
impact: HIGH
impactDescription: "Eliminates table lookups — query served entirely from index"
tags: indexes, covering-index, performance
---

## Use Covering Indexes for Read-Heavy Queries

**Impact: HIGH (Eliminates table lookups — query served entirely from index)**

A covering index contains all columns a query needs, allowing the database to return results directly from the index without accessing the table data. This eliminates random I/O table lookups, which are the most expensive part of indexed queries on large tables.

## Incorrect

```php
// ❌ Index on status only — query needs name and email too
Schema::table('users', function (Blueprint $table): void {
    $table->index('status');
});

// Query uses the index to find matching rows, then performs a table lookup
// for EACH row to fetch name and email — slow on large result sets
$activeUsers = User::select('name', 'email')
    ->where('status', 'active')
    ->get();
// EXPLAIN shows "Using index condition" — table lookup required
```

**Problems:**
- Each matching row requires a random I/O table lookup to fetch non-indexed columns
- On SSDs this adds microseconds per row; on HDDs, milliseconds per row
- Large result sets (thousands of rows) multiply the lookup cost significantly

## Correct

```php
// ✅ MySQL: include all queried columns in the composite index
Schema::table('users', function (Blueprint $table): void {
    // Covering index — all selected/filtered columns are in the index
    $table->index(['status', 'name', 'email']);
});

$activeUsers = User::select('name', 'email')
    ->where('status', 'active')
    ->get();
// EXPLAIN shows "Using index" — no table lookup needed

// ✅ PostgreSQL: use raw SQL for INCLUDE clause (non-searchable payload columns)
// This keeps the index smaller while still covering the query
DB::statement('
    CREATE INDEX users_status_covering
    ON users (status)
    INCLUDE (name, email)
');

// ✅ Verify with EXPLAIN to confirm covering index is used
$plan = DB::select('EXPLAIN SELECT name, email FROM users WHERE status = ?', ['active']);
// MySQL: look for "Using index" (not "Using index condition")
// PostgreSQL: look for "Index Only Scan"
```

**Benefits:**
- Query returns results directly from the index B-tree — zero table lookups
- Dramatically faster for queries returning many rows from a filtered subset
- PostgreSQL INCLUDE clause keeps indexed columns minimal while covering SELECT columns

Reference: [Laravel Creating Indexes](https://laravel.com/docs/13.x/migrations#creating-indexes)
