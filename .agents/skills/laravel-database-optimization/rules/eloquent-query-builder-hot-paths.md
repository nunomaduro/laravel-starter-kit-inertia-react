---
title: Use Query Builder for Hot Paths
impact: HIGH
impactDescription: "30-50% faster on large result sets by skipping model hydration"
tags: query-builder, eloquent, performance, db-table
---

## Use Query Builder for Hot Paths

**Impact: HIGH (30-50% faster on large result sets by skipping model hydration)**

Eloquent models add overhead per row through model instantiation, attribute casting, and event dispatching. On hot API endpoints returning hundreds or thousands of rows, switching to the Query Builder eliminates this overhead entirely.

## Incorrect

```php
// ❌ Eloquent on a high-traffic endpoint returning thousands of rows
use App\Models\User;

// Each row instantiates a User model, runs casts, and fires events
$activeUsers = User::where('active', true)
    ->select('id', 'name', 'email')
    ->get();

// In a loop — model overhead multiplied across every iteration
foreach ($activeUsers as $user) {
    $result[] = [
        'id' => $user->id,
        'name' => $user->name,
    ];
}
```

**Problems:**
- Each row triggers model instantiation, attribute casting, and accessor resolution
- Model events (retrieved, etc.) fire for every single row
- Memory usage scales with model weight, not just data size
- On 10,000 rows, overhead can add 50-100ms compared to raw Query Builder

## Correct

```php
// ✅ Query Builder on hot paths — returns plain stdClass objects
use Illuminate\Support\Facades\DB;

$activeUsers = DB::table('users')
    ->where('active', true)
    ->select('id', 'name', 'email')
    ->get();

// stdClass objects — lightweight, no model overhead
foreach ($activeUsers as $user) {
    $result[] = [
        'id' => $user->id,
        'name' => $user->name,
    ];
}
```

**Benefits:**
- Skips model instantiation, casts, accessors, and event dispatching
- Returns lightweight stdClass objects with minimal memory footprint
- 30-50% faster on large result sets (benchmarked on 5,000+ rows)
- Keep Eloquent for CRUD operations where events, casts, and mutators add value

Reference: [Laravel Query Builder](https://laravel.com/docs/13.x/queries)
