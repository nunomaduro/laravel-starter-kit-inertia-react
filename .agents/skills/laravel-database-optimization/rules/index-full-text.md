---
title: Use Full-Text Indexes for Search
impact: HIGH
impactDescription: "Replaces full table scans with indexed text search"
tags: indexes, full-text, search, like-query
---

## Use Full-Text Indexes for Search

**Impact: HIGH (Replaces full table scans with indexed text search)**

Using `LIKE '%term%'` with a leading wildcard forces a full table scan on every search query because no B-tree index can satisfy a leading-wildcard pattern. Full-text indexes use inverted index structures designed specifically for text search, providing orders-of-magnitude faster results on large text columns.

## Incorrect

```php
// ❌ LIKE with leading wildcard — cannot use any index, full table scan
$results = Article::where('title', 'LIKE', "%{$term}%")
    ->orWhere('body', 'LIKE', "%{$term}%")
    ->get();
// Scans every row in the articles table — O(n) on table size
// On a table with 1M rows, this can take seconds

// ❌ Multiple LIKE clauses compound the problem
$results = Article::where('title', 'LIKE', "%{$term}%")
    ->where('body', 'LIKE', "%{$term}%")
    ->where('summary', 'LIKE', "%{$term}%")
    ->get();
```

**Problems:**
- Leading wildcard `%term%` prevents the database from using any B-tree index
- Full table scan reads every row, making query time proportional to table size
- Multiple LIKE clauses on large TEXT columns consume excessive CPU and I/O

## Correct

```php
// ✅ Step 1: Add full-text index in migration
Schema::create('articles', function (Blueprint $table): void {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->timestamps();

    // Full-text index — works on MySQL and PostgreSQL
    $table->fullText(['title', 'body']);
});

// ✅ Step 2: Use whereFullText() in queries
$results = Article::whereFullText(['title', 'body'], $term)->get();
// MySQL: SELECT * FROM articles WHERE MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE)
// PostgreSQL: SELECT * FROM articles WHERE to_tsvector(title || ' ' || body) @@ plainto_tsquery(?)

// ✅ With boolean mode for advanced search (MySQL)
$results = Article::whereFullText(['title', 'body'], '+laravel -vue', [
    'mode' => 'boolean',
])->get();

// ✅ Combine with other conditions and ordering
$results = Article::whereFullText(['title', 'body'], $term)
    ->where('published', true)
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

**Benefits:**
- Inverted index structure provides sub-millisecond text search on millions of rows
- `whereFullText()` provides a clean, database-agnostic API for full-text queries
- Supports natural language mode and boolean mode for flexible search behavior

Reference: [Laravel Full Text Indexes](https://laravel.com/docs/13.x/migrations#available-index-types)
