---
title: Optimize whereHas with Joins or whereIn Subqueries
impact: HIGH
impactDescription: "10-100x faster than correlated subqueries on large tables"
tags: where-has, joins, subquery, performance
---

## Optimize whereHas with Joins or whereIn Subqueries

**Impact: HIGH (10-100x faster than correlated subqueries on large tables)**

`whereHas` generates a correlated `WHERE EXISTS` subquery that the database evaluates once per row in the outer table. On large tables, this becomes a significant bottleneck. Replacing it with `whereIn` (uncorrelated subquery) or a `join` lets the database optimize the query plan far more effectively.

## Incorrect

```php
// ❌ whereHas generates a correlated EXISTS subquery
$products = Product::whereHas('reviews', function ($query) {
    $query->where('rating', '>=', 4);
})->get();

// Generated SQL:
// SELECT * FROM products
// WHERE EXISTS (
//     SELECT 1 FROM reviews
//     WHERE reviews.product_id = products.id  -- correlated: runs per row
//     AND reviews.rating >= 4
// )
```

**Problems:**
- Correlated subquery is re-evaluated for every row in the `products` table
- Performance degrades rapidly as the products table grows
- Database optimizer has limited ability to rewrite correlated subqueries
- On 100k+ products with 500k+ reviews, queries can take seconds instead of milliseconds

## Correct

```php
// ✅ Option 1: whereIn with subquery — uncorrelated, optimizer-friendly
use App\Models\Review;

$products = Product::whereIn(
    'id',
    Review::select('product_id')
        ->where('rating', '>=', 4)
        ->distinct()
)->get();

// Generated SQL:
// SELECT * FROM products
// WHERE id IN (
//     SELECT DISTINCT product_id FROM reviews WHERE rating >= 4
// )

// ✅ Option 2: Join — single pass, best for frequently-executed queries
$products = Product::query()
    ->select('products.*')
    ->join('reviews', 'reviews.product_id', '=', 'products.id')
    ->where('reviews.rating', '>=', 4)
    ->groupBy('products.id')
    ->get();

// ✅ Option 3: Laravel's whereRelation — cleaner syntax (still EXISTS under the hood)
// Use for simple conditions where performance is acceptable
$products = Product::whereRelation('reviews', 'rating', '>=', 4)->get();
```

**Benefits:**
- `whereIn` subquery runs once, not per row — database can optimize with indexes
- `join` approach processes both tables in a single pass with no subquery
- 10-100x improvement on large datasets compared to correlated `whereHas`
- Choose `whereIn` for readability, `join` for maximum performance on hot paths

Reference: [Laravel Eloquent whereHas](https://laravel.com/docs/13.x/eloquent-relationships#querying-relationship-existence)
