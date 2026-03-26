---
title: Set Appropriate TTLs by Data Volatility
impact: HIGH
impactDescription: "Prevents stale data and cache bloat by matching TTL to data change frequency"
tags: cache, ttl, strategy, stale-data
---

## Set Appropriate TTLs by Data Volatility

**Impact: HIGH (Prevents stale data and cache bloat by matching TTL to data change frequency)**

Using `Cache::forever()` for all data or applying arbitrary TTLs leads to either stale data being served or excessive cache misses. A deliberate TTL strategy based on how frequently data changes balances freshness against database load.

## Incorrect

```php
// ❌ No TTL strategy — everything cached forever or with arbitrary values
Cache::forever('user:profile:' . $user->id, $user);         // User data changes frequently
Cache::forever('dashboard:stats', $stats);                    // Stats go stale immediately
Cache::put('countries', $countries, 60);                      // Reference data evicted too soon
Cache::put('products:featured', $products);                   // No TTL — defaults vary by driver
```

**Problems:**
- `forever()` on volatile data means users see stale profiles, stats, and counts
- Short TTLs on static data cause unnecessary database queries on every expiry
- No TTL argument relies on driver defaults, which vary and may be infinite
- No documented strategy means each developer picks arbitrary values

## Correct

```php
// ✅ TTL strategy based on data volatility

// Reference data (countries, currencies, config) — changes rarely
// TTL: 24 hours or rememberForever with explicit invalidation
$countries = Cache::remember('reference:countries', now()->addHours(24), function () {
    return Country::orderBy('name')->get();
});

// Aggregate/dashboard data — changes with every transaction
// TTL: 5-15 minutes
$dashboardStats = Cache::remember('dashboard:stats', now()->addMinutes(10), function () {
    return [
        'total_revenue' => Order::sum('total'),
        'new_users_today' => User::whereDate('created_at', today())->count(),
        'pending_orders' => Order::where('status', 'pending')->count(),
    ];
});

// User-specific data — changes on user action
// TTL: 15-30 minutes
$userProfile = Cache::remember(
    "users:{$user->id}:profile",
    now()->addMinutes(15),
    fn () => $user->load('preferences', 'subscription'),
);

// Frequently changing data (feeds, notifications) — changes constantly
// TTL: 1-5 minutes or skip caching entirely
$notifications = Cache::remember(
    "users:{$user->id}:notifications",
    now()->addMinutes(2),
    fn () => $user->unreadNotifications()->limit(20)->get(),
);

// ✅ Set a sensible default TTL in config/cache.php
// 'ttl' => 3600, // 1 hour default — prevents accidental forever caching
```

| Data Type | Volatility | Recommended TTL |
|---|---|---|
| Reference data (countries, config) | Very low | 24 hours or `rememberForever` |
| Product catalog, categories | Low | 1-6 hours |
| User profiles, preferences | Medium | 15-30 minutes |
| Aggregations, dashboard stats | Medium-High | 5-15 minutes |
| Feeds, notifications, activity | High | 1-5 minutes |
| Real-time data (stock, bidding) | Very high | Do not cache |

**Benefits:**
- Data freshness matches user expectations for each data type
- Static data stays cached longer, reducing unnecessary database queries
- Volatile data expires quickly, preventing users from seeing stale information
- Documented TTL guidelines help teams make consistent caching decisions

Reference: [Laravel Cache Configuration](https://laravel.com/docs/13.x/cache#configuration)
