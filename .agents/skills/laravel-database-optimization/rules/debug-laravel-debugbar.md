---
title: Install Debugbar in Development
impact: MEDIUM
impactDescription: "Surfaces N+1 queries, duplicate queries, and slow queries automatically during development"
tags: debugbar, debugging, development, queries
---

## Install Debugbar in Development

**Impact: MEDIUM (Surfaces N+1 queries, duplicate queries, and slow queries automatically during development)**

Without query monitoring, N+1 problems and duplicate queries silently accumulate during development. Debugbar displays query count, execution time, memory usage, and duplicate queries on every page load, making performance regressions immediately visible before they reach production.

## Incorrect

```php
// ❌ No query monitoring — performance issues go unnoticed during development
// A page fires 200 queries and takes 800ms but looks fine to the developer
// N+1 problems only surface when production load exposes the slowdown
// Duplicate queries waste database resources but are invisible without tooling

// Common scenario:
// - Developer adds a relationship accessor in a Blade template
// - Page still loads in 200ms locally with 50 test records
// - In production with 10,000 records, the page takes 15 seconds
// - No one notices until users complain
```

**Problems:**
- N+1 queries are invisible during normal development — pages still load fast with small datasets
- Duplicate queries from multiple components rendering the same data go undetected
- Memory leaks from eager loading too much data are only caught in production

## Correct

```bash
# ✅ Install as a dev-only dependency
composer require barryvdh/laravel-debugbar --dev
```

```php
// ✅ Configure in .env — only enable in local/development
// DEBUGBAR_ENABLED=true

// ✅ Debugbar automatically shows on every page:
// - Queries tab: total count, time per query, duplicate detection
// - Timeline tab: request lifecycle breakdown
// - Memory tab: peak memory usage
// - Models tab: number of Eloquent models hydrated

// ✅ Programmatic usage for API debugging
use Barryvdh\Debugbar\Facades\Debugbar;

Debugbar::startMeasure('api-call', 'External API Call');
$response = Http::get('https://api.example.com/data');
Debugbar::stopMeasure('api-call');

// ✅ Disable in production — ensure it's in require-dev, not require
// config/debugbar.php
return [
    'enabled' => env('DEBUGBAR_ENABLED', false),
    'storage' => [
        'enabled' => false, // Don't store debugbar data in production
    ],
];
```

**Benefits:**
- Instant visibility into query count and execution time on every page load
- Highlights duplicate queries automatically — easy to spot missing eager loads
- Dev-only dependency with zero production overhead when properly configured

Reference: [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
