---
title: Use Subquery Selects for Computed Columns
impact: HIGH
impactDescription: "Single query replaces N+1 relationship loads for scalar values"
tags: subquery, select, performance, computed
---

## Use Subquery Selects for Computed Columns

**Impact: HIGH (Single query replaces N+1 relationship loads for scalar values)**

When you need a single value from a related table (e.g., the most recent login date, the latest order total), loading the entire relationship is wasteful. Subquery selects embed a correlated subquery directly into your SELECT clause, returning the value as a column on the parent model.

## Incorrect

```php
// ❌ Loads entire relationship just to get one value per user
$users = User::with('logins')->get();

foreach ($users as $user) {
    // Loaded ALL logins into memory just for the latest one
    echo $user->logins->sortByDesc('created_at')->first()?->created_at;
}

// Or worse — N+1 without eager loading
$users = User::all();

foreach ($users as $user) {
    // Separate query per user
    echo $user->logins()->latest()->first()?->created_at;
}
```

**Problems:**
- Loads all login records into memory when only the latest is needed
- Without eager loading, triggers N+1 queries (one per user)
- Memory usage scales with total number of related records across all users
- Cannot sort or paginate the parent query by the computed value

## Correct

```php
// ✅ Subquery select — single query, one value per row
use App\Models\Login;
use App\Models\Order;

$users = User::query()
    ->addSelect([
        'last_login_at' => Login::select('created_at')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->limit(1),
        'latest_order_total' => Order::select('total')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->limit(1),
    ])
    ->orderByDesc('last_login_at')  // Can sort by computed column
    ->get();

foreach ($users as $user) {
    echo $user->last_login_at;         // Accessed like a normal attribute
    echo $user->latest_order_total;    // No extra queries
}
```

**Benefits:**
- Single SQL query with embedded subqueries — no additional round trips
- Only the needed scalar value is returned, not the entire related collection
- Computed columns can be used in `orderBy`, `having`, and other clauses
- Attributes are accessible directly on the model like regular columns

Reference: [Laravel Subquery Selects](https://laravel.com/docs/13.x/eloquent#subquery-selects)
