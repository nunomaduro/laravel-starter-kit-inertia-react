---
title: Select Only Needed Columns
impact: CRITICAL
impactDescription: "Reduces memory usage and query transfer size by 40-80%"
tags: select, columns, memory, performance
---

## Select Only Needed Columns

**Impact: CRITICAL (Reduces memory usage and query transfer size by 40-80%)**

Selecting all columns with `SELECT *` transfers unnecessary data from the database, wastes memory hydrating unused attributes, and prevents the query optimizer from using covering indexes. Selecting only the columns you need dramatically reduces both network transfer and PHP memory consumption.

## Incorrect

```php
// ❌ Loading all columns when only a few are needed
$users = User::all(); // SELECT * FROM users — loads 20+ columns

// ❌ Eager loading with all columns on both sides
$users = User::with('posts')->get();
// SELECT * FROM users
// SELECT * FROM posts WHERE user_id IN (1, 2, 3, ...)

// ❌ Even worse in an API response — serializes every column
return UserResource::collection(User::all());
```

**Problems:**
- `SELECT *` transfers large TEXT/BLOB columns even when unused, wasting bandwidth
- Every row hydrates all attributes into PHP objects, consuming unnecessary memory
- Prevents the database from using covering indexes, forcing table lookups

## Correct

```php
// ✅ Select only the columns you need
$users = User::select('id', 'name', 'email')->get();
// SELECT id, name, email FROM users

// ✅ Constrain eager loaded relationship columns (colon syntax)
$users = User::select('id', 'name', 'email')
    ->with('posts:id,user_id,title,published_at')
    ->get();
// SELECT id, name, email FROM users
// SELECT id, user_id, title, published_at FROM posts WHERE user_id IN (...)

// ✅ Use addSelect() to add computed columns alongside specific fields
$users = User::select('id', 'name')
    ->addSelect([
        'latest_post_title' => Post::select('title')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->limit(1),
    ])
    ->get();
```

**Benefits:**
- Reduces data transfer between database and application by 40-80% on wide tables
- Lower memory usage per model instance — only selected attributes are hydrated
- Enables covering index usage, eliminating table lookups for read-heavy queries

Reference: [Laravel Eloquent Retrieving Models](https://laravel.com/docs/13.x/eloquent#retrieving-models)
