---
title: Enable Slow Query Logging
impact: MEDIUM
impactDescription: "Catches slow queries in staging/production before they cause user-facing performance issues"
tags: slow-queries, monitoring, mysql, production
---

## Enable Slow Query Logging

**Impact: MEDIUM (Catches slow queries in staging/production before they cause user-facing performance issues)**

Slow queries that go unmonitored gradually degrade application performance as data grows. By logging queries that exceed a time threshold, teams can identify and fix performance regressions before they cause user-facing timeouts or cascading failures.

## Incorrect

```php
// ❌ No slow query monitoring — performance regressions go unnoticed
// A query that takes 50ms with 10,000 rows silently grows to 5s with 1 million rows
// No alerts, no logs, no visibility until users report the application is slow

// The team only discovers the problem through:
// - User complaints about slow pages
// - Database CPU alerts after the problem is severe
// - Incident postmortems after downtime
```

**Problems:**
- Slow queries accumulate silently as data grows over months
- No early warning system to catch regressions before they impact users
- Without logs, debugging production slowdowns requires real-time profiling under pressure

## Correct

```sql
-- ✅ MySQL slow query log — enable in my.cnf or at runtime
-- SET GLOBAL slow_query_log = 1;
-- SET GLOBAL long_query_time = 1;  -- Log queries taking more than 1 second
-- SET GLOBAL log_queries_not_using_indexes = 1;  -- Also log queries missing indexes
```

```php
// ✅ Laravel DB::listen() — programmatic slow query logging
// In AppServiceProvider::boot()
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

DB::listen(function (QueryExecuted $query): void {
    if ($query->time > 1000) { // time is in milliseconds
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time_ms' => $query->time,
            'connection' => $query->connectionName,
        ]);
    }
});

// ✅ Alert on cumulative query time per request
// In a middleware:
public function handle(Request $request, Closure $next): Response
{
    DB::enableQueryLog();

    $response = $next($request);

    $totalTime = collect(DB::getQueryLog())
        ->sum('time');

    if ($totalTime > 2000) {
        Log::warning('Request exceeded query time budget', [
            'url' => $request->fullUrl(),
            'total_query_time_ms' => $totalTime,
            'query_count' => count(DB::getQueryLog()),
        ]);
    }

    return $response;
}

// ✅ Use DB::whenQueryingForLongerThan() for cumulative threshold alerts
DB::whenQueryingForLongerThan(5000, function (Connection $connection, QueryExecuted $event): void {
    Log::warning('Cumulative query time exceeded 5s', [
        'connection' => $connection->getName(),
    ]);

    // Optionally notify the team
    // Notification::route('slack', '#alerts')->notify(new SlowQueryAlert($connection));
});
```

**Benefits:**
- Catches slow queries before they cause user-facing performance issues
- `DB::listen()` integrates with any logging/alerting system (Slack, PagerDuty, etc.)
- `whenQueryingForLongerThan()` detects death-by-a-thousand-cuts — many fast queries that add up

Reference: [Laravel Database Monitoring](https://laravel.com/docs/13.x/database#monitoring-cumulative-query-time)
