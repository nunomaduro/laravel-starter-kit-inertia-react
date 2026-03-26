---
title: Use withCount and withSum Instead of Loading Relations
impact: HIGH
impactDescription: "Eliminates loading entire relations into memory for simple counts or sums"
tags: with-count, with-sum, aggregates, performance
---

## Use withCount and withSum Instead of Loading Relations

**Impact: HIGH (Eliminates loading entire relations into memory for simple counts or sums)**

Loading an entire relationship just to count or sum values wastes memory and adds unnecessary queries. Eloquent's `withCount` and `withSum` methods perform the aggregation as a subquery within the main query, returning only the computed value.

## Incorrect

```php
// ❌ Loads all posts and orders into memory just for counts and sums
$users = User::with(['posts', 'orders'])->get();

foreach ($users as $user) {
    echo $user->name;
    echo $user->posts->count();        // All posts loaded into memory
    echo $user->orders->sum('total');   // All orders loaded into memory
}
```

**Problems:**
- Loads every post and order record into memory even though only aggregate values are needed
- Two additional queries (posts, orders) returning potentially thousands of rows
- Memory usage grows linearly with the number of related records
- Serializing the response includes all related data unless manually excluded

## Correct

```php
// ✅ Aggregates computed as subqueries — no extra data loaded
$users = User::query()
    ->withCount('posts')
    ->withSum('orders', 'total')
    ->withAvg('reviews', 'rating')
    ->get();

foreach ($users as $user) {
    echo $user->name;
    echo $user->posts_count;           // int — from subquery
    echo $user->orders_sum_total;      // float — from subquery
    echo $user->reviews_avg_rating;    // float — from subquery
}

// Conditional aggregates
$users = User::query()
    ->withCount(['posts as published_posts_count' => function ($query) {
        $query->where('published', true);
    }])
    ->get();

echo $user->published_posts_count;
```

**Benefits:**
- Single query with subqueries — no extra round trips to the database
- Only aggregate values are returned, not entire related collections
- Dramatically lower memory usage when relations contain many records
- Attributes follow a predictable naming convention: `{relation}_{function}_{column}`

Reference: [Laravel Eloquent Aggregates](https://laravel.com/docs/13.x/eloquent-relationships#counting-related-models)
