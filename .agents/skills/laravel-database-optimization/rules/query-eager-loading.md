---
title: Always Eager Load Known Relationships
impact: CRITICAL
impactDescription: "Reduces N+1 queries — 101 queries down to 2"
tags: n-plus-one, eager-loading, with, performance
---

## Always Eager Load Known Relationships

**Impact: CRITICAL (Reduces N+1 queries — 101 queries down to 2)**

Accessing relationships inside loops without eager loading triggers a separate query for every iteration. A page listing 100 posts with their authors fires 101 queries (1 for posts + 100 for each author), degrading response time linearly with dataset size.

## Incorrect

```php
// ❌ N+1 problem — 101 queries for 100 posts
$posts = Post::all(); // 1 query

foreach ($posts as $post) {
    echo $post->author->name; // 1 query per post (100 queries)
    echo $post->tags->pluck('name')->join(', '); // 1 query per post (100 more)
}
// Total: 201 queries
```

**Problems:**
- Each loop iteration fires a separate SQL query for every accessed relationship
- Query count grows linearly with the number of records, causing severe slowdowns
- Database connection overhead multiplied across hundreds of round-trips

## Correct

```php
// ✅ Eager load all known relationships — 3 queries total
$posts = Post::with(['author', 'tags'])->get(); // 3 queries

foreach ($posts as $post) {
    echo $post->author->name;       // No additional query
    echo $post->tags->pluck('name')->join(', '); // No additional query
}

// ✅ Constrained eager loading — load only what you need
$posts = Post::with([
    'author:id,name,avatar',
    'comments' => fn ($q) => $q->latest()->limit(5),
])->get();
```

**Benefits:**
- Constant query count regardless of result set size (2-3 queries instead of 201)
- Constrained eager loads reduce memory usage by loading only relevant related records
- Predictable, measurable database load that scales with relationship count, not row count

Reference: [Laravel Eager Loading](https://laravel.com/docs/13.x/eloquent-relationships#eager-loading)
