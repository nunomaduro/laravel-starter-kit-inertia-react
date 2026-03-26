---
title: Use Cursor Pagination for Large Datasets
impact: HIGH
impactDescription: "Eliminates slow OFFSET on deep pages — constant query time regardless of page depth"
tags: pagination, cursor, performance, large-datasets
---

## Use Cursor Pagination for Large Datasets

**Impact: HIGH (Eliminates slow OFFSET on deep pages — constant query time regardless of page depth)**

Traditional offset-based pagination uses `OFFSET N` which forces the database to scan and discard N rows before returning results. On a table with millions of rows, navigating to page 10,000 means the database must skip 150,000 rows, making deep pages progressively slower.

## Incorrect

```php
// ❌ Offset pagination on a large table — OFFSET grows linearly with page depth
$users = User::paginate(15);

// On page 10,000 this generates:
// SELECT * FROM users LIMIT 15 OFFSET 149985
// The database must scan and discard 149,985 rows before returning 15
```

**Problems:**
- Query time degrades linearly as users navigate deeper pages
- Database performs a full index scan up to the OFFSET value on every request
- On tables with millions of rows, deep pages can take seconds or cause timeouts

## Correct

```php
// ✅ Cursor pagination — uses WHERE id > ? for constant-time lookups
$users = User::orderBy('id')->cursorPaginate(15);

// On any page this generates:
// SELECT * FROM users WHERE id > 149985 ORDER BY id LIMIT 15
// The database seeks directly to the cursor position via index

// ✅ Works with multiple order columns
$users = User::orderBy('created_at')
    ->orderBy('id')
    ->cursorPaginate(15);

// ✅ In API responses — return cursor links instead of page numbers
return UserResource::collection(
    User::orderBy('id')->cursorPaginate(15)
);
// Response includes: next_cursor, prev_cursor instead of page numbers
```

**Benefits:**
- Constant query time regardless of how deep the user navigates — O(1) vs O(n)
- Uses efficient index seeks (WHERE id > ?) instead of scanning and discarding rows
- Ideal for infinite scroll, API feeds, and any dataset where users don't need to jump to arbitrary pages

Reference: [Laravel Cursor Pagination](https://laravel.com/docs/13.x/pagination#cursor-pagination)
