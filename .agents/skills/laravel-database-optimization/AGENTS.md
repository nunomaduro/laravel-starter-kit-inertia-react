# Laravel Database Optimization - Complete Reference

**Version:** 1.1.1
**Framework:** Laravel 13.x / PHP 8.3+
**Date:** March 2026
**License:** MIT

## Abstract

Comprehensive database optimization patterns for Laravel 13 applications. Contains 33 rules across 9 categories covering N+1 query prevention, indexing strategies, Eloquent optimization, Redis caching, pagination, transactions, migrations, naming conventions, and query debugging. Each rule includes incorrect and correct code examples with practical Laravel implementations.

## References

- [Laravel Eloquent Relationships](https://laravel.com/docs/13.x/eloquent-relationships)
- [Laravel Database Queries](https://laravel.com/docs/13.x/queries)
- [Laravel Cache](https://laravel.com/docs/13.x/cache)
- [Laravel Pagination](https://laravel.com/docs/13.x/pagination)
- [Laravel Migrations](https://laravel.com/docs/13.x/migrations)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Query Performance & N+1 (query)

**Impact:** CRITICAL
**Description:** Eliminating N+1 queries is the single most impactful database optimization in Laravel. Eager loading relationships, preventing lazy loading in development, and selecting only needed columns can reduce query counts from hundreds to single digits and improve response times by 10-100x.

## 2. Indexing Strategies (index)

**Impact:** CRITICAL
**Description:** Proper indexing is fundamental to database performance. Foreign key indexes, composite indexes for multi-column WHERE clauses, covering indexes for read-heavy queries, and full-text indexes for search functionality can turn multi-second queries into millisecond responses.

## 3. Eloquent Optimization (eloquent)

**Impact:** HIGH
**Description:** Advanced Eloquent patterns for performance-critical code paths. Using the query builder directly for hot paths, withCount for aggregate counts, subquery selects to avoid extra queries, and optimized whereHas queries reduce both query count and execution time.

## 4. Caching with Redis (cache)

**Impact:** HIGH
**Description:** Caching frequently accessed data with Redis eliminates repeated database queries entirely. Cache::remember patterns, proper cache invalidation on model changes, cache tags for group invalidation, and appropriate TTL values provide 10-100x performance improvements for read-heavy operations.

## 5. Pagination & Large Datasets (data)

**Impact:** HIGH
**Description:** Handling large datasets efficiently prevents memory exhaustion and slow queries. Cursor pagination for infinite scroll, chunkById for batch processing, lazy cursors for memory-efficient iteration, and avoiding unbounded queries on large tables are essential for production applications.

## 6. Transactions & Locking (lock)

**Impact:** HIGH
**Description:** Database transactions ensure data consistency for multi-step operations. Short focused transactions, deadlock retry logic, and pessimistic locking for critical updates prevent data corruption and race conditions in concurrent applications.

## 7. Migrations (migrate)

**Impact:** HIGH
**Description:** Production-safe migration patterns prevent downtime and data loss. Zero-downtime migrations, concurrent index creation, and safe column additions ensure schema changes do not lock tables or disrupt running applications.

## 8. Query Debugging (debug)

**Impact:** MEDIUM
**Description:** Effective query debugging identifies performance bottlenecks before they reach production. EXPLAIN ANALYZE for understanding query plans, Laravel Debugbar for development profiling, and slow query log monitoring for production surveillance provide visibility into database performance.

## 9. Naming & Structure (naming)

**Impact:** HIGH
**Description:** Laravel Eloquent relies heavily on naming conventions to auto-resolve models to tables, methods to foreign keys, and relationships to columns. Following consistent table, column, relationship, and migration naming conventions prevents silent bugs and enables Eloquent's convention-over-configuration approach.


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


---

## Enable preventLazyLoading in Development

**Impact: CRITICAL (Catches N+1 bugs before they reach production)**

Without lazy loading prevention, N+1 query bugs are invisible — code works correctly but fires hundreds of unnecessary queries. Enabling `preventLazyLoading` in non-production environments throws an exception the moment a relationship is lazily loaded, forcing developers to fix the problem immediately.

## Incorrect

```php
// ❌ No lazy loading prevention — N+1 bugs silently reach production
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Nothing here to catch lazy loading
    }
}

// This code works but fires 101 queries — nobody notices until production crawls
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Silent N+1, no error raised
}
```

**Problems:**
- N+1 queries go completely undetected during development and code review
- Performance regressions accumulate silently until production degrades noticeably
- Developers have no feedback loop to learn about missing eager loads

## Correct

```php
// ✅ Prevent lazy loading in non-production environments
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}

// Now this code throws LazyLoadingViolationException in dev/staging:
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // 💥 LazyLoadingViolationException thrown
}

// Developer is forced to fix it:
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // ✅ No exception, 2 queries total
}
```

**Benefits:**
- Throws `LazyLoadingViolationException` immediately when a relationship is lazily loaded
- Catches N+1 problems during development before they reach production
- Safe to enable — only active in non-production environments, so production is never affected

Reference: [Laravel Preventing Lazy Loading](https://laravel.com/docs/13.x/eloquent-relationships#preventing-lazy-loading)


---

## Use Automatic Eager Loading (Laravel 13+)

**Impact: CRITICAL (Eliminates N+1 queries without manual with() calls)**

Laravel 13 introduces automatic eager loading, which detects when multiple models in a collection access the same relationship and batches those queries automatically. This eliminates the most common source of N+1 bugs without requiring developers to remember every `with()` call.

## Incorrect

```php
// ❌ Manually adding with() everywhere — easy to miss some
$posts = Post::with(['author', 'tags', 'comments'])->get();

// Elsewhere in the codebase, someone forgets:
$posts = Post::where('published', true)->get();
foreach ($posts as $post) {
    echo $post->author->name; // N+1 — forgot with('author')
}

// Or in a Blade template where with() can't be added:
@foreach ($posts as $post)
    {{ $post->category->name }} {{-- N+1 — no way to fix from the view --}}
@endforeach
```

**Problems:**
- Every query site must remember to include the right `with()` calls
- Blade templates and third-party packages cannot add `with()` themselves
- Missing a single eager load silently introduces N+1 problems

## Correct

```php
// ✅ Option 1: Enable globally in AppServiceProvider
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::automaticallyEagerLoadRelationships();
    }
}

// Now any collection access auto-batches relationship queries:
$posts = Post::where('published', true)->get();
foreach ($posts as $post) {
    echo $post->author->name; // Auto-batched — no N+1
}

// ✅ Option 2: Enable per-collection for targeted use
$posts = Post::where('published', true)->get();
$posts->withRelationshipAutoloading();

foreach ($posts as $post) {
    echo $post->author->name;       // Auto-batched
    echo $post->category->name;     // Auto-batched
}
```

**Benefits:**
- Eliminates N+1 queries globally without modifying existing query code
- Works in Blade templates, packages, and any code that iterates collections
- Per-collection option provides fine-grained control when global activation is too broad

Reference: [Laravel Automatic Eager Loading](https://laravel.com/docs/13.x/eloquent-relationships#automatic-eager-loading)


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


---

## Index All Foreign Key Columns

**Impact: CRITICAL (Prevents full table scans on JOINs and relationship queries)**

Every Eloquent relationship query uses a WHERE clause on the foreign key column. Without an index, the database performs a full table scan for each relationship lookup. On tables with millions of rows, this turns millisecond queries into multi-second operations.

## Incorrect

```php
// ❌ Manual column without index — full table scan on every relationship query
Schema::create('posts', function (Blueprint $table): void {
    $table->id();
    $table->unsignedBigInteger('user_id'); // No index, no constraint
    $table->unsignedBigInteger('category_id'); // No index, no constraint
    $table->string('title');
    $table->text('body');
    $table->timestamps();
});

// Every query on this relationship triggers a full table scan:
// SELECT * FROM posts WHERE user_id = 42  — scans entire table
$user->posts;
```

**Problems:**
- `WHERE user_id = ?` scans every row in the table without an index
- JOIN operations between parent and child tables become prohibitively slow
- CASCADE deletes on the parent table must scan the entire child table to find related rows

## Correct

```php
// ✅ Use foreignId() — creates column, index, and constraint automatically
Schema::create('posts', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('body');
    $table->timestamps();
});
// foreignId() creates: unsignedBigInteger column + index + foreign key constraint

// ✅ For existing tables, add the index in a migration
Schema::table('posts', function (Blueprint $table): void {
    $table->index('user_id');
    $table->index('category_id');
});

// ✅ For polymorphic relationships, index both columns
Schema::create('comments', function (Blueprint $table): void {
    $table->id();
    $table->morphs('commentable'); // Creates commentable_type, commentable_id + composite index
    $table->text('body');
    $table->timestamps();
});
```

**Benefits:**
- `foreignId()->constrained()` creates the column, index, and foreign key constraint in one call
- Index-backed WHERE clauses use B-tree lookups instead of full table scans
- `morphs()` automatically creates a composite index on both polymorphic columns

Reference: [Laravel Foreign Key Constraints](https://laravel.com/docs/13.x/migrations#foreign-key-constraints)


---

## Create Composite Indexes Matching Query Patterns

**Impact: CRITICAL (Serves multi-column filters from a single index scan)**

When queries filter on multiple columns, separate single-column indexes force the database to choose one index and scan remaining rows. A composite index matching the query's column order lets the database satisfy the entire WHERE clause in a single index scan, often improving performance by 10-100x on large tables.

## Incorrect

```php
// ❌ Separate single-column indexes for a multi-column query
Schema::create('orders', function (Blueprint $table): void {
    $table->id();
    $table->string('status');
    $table->timestamp('created_at');
    $table->decimal('total', 10, 2);
    $table->foreignId('user_id')->constrained();

    $table->index('status');      // Single-column index
    $table->index('created_at');  // Single-column index
});

// This query can only use ONE of the two indexes:
Order::where('status', 'pending')
    ->where('created_at', '>', now()->subDays(30))
    ->get();
// Database picks one index, then scans remaining rows — slow on large tables
```

**Problems:**
- MySQL and PostgreSQL can only efficiently use one single-column index per table in a query
- The database must scan all rows matching the first index to apply the second filter
- Index merge operations are unreliable and often slower than a single composite index

## Correct

```php
// ✅ Composite index matching the query pattern
// Rule: equality columns first, range columns last
Schema::create('orders', function (Blueprint $table): void {
    $table->id();
    $table->string('status');
    $table->timestamp('created_at');
    $table->decimal('total', 10, 2);
    $table->foreignId('user_id')->constrained();

    // Equality column (status) first, range column (created_at) last
    $table->index(['status', 'created_at']);
});

// This query uses the full composite index efficiently:
$recentPending = Order::where('status', 'pending')
    ->where('created_at', '>', now()->subDays(30))
    ->get();

// ✅ Another example: user dashboard with status filter
Schema::table('orders', function (Blueprint $table): void {
    $table->index(['user_id', 'status', 'created_at']);
});

// Matches this common query pattern:
$userOrders = Order::where('user_id', $userId)
    ->where('status', 'completed')
    ->orderBy('created_at', 'desc')
    ->paginate(20);
```

**Benefits:**
- Single index scan satisfies the entire WHERE clause — no post-filtering required
- Equality-first, range-last ordering maximizes index selectivity
- The leftmost prefix rule means the composite index also serves queries using only the first column(s)

Reference: [Laravel Index Columns](https://laravel.com/docs/13.x/migrations#creating-indexes)


---

## Use Covering Indexes for Read-Heavy Queries

**Impact: HIGH (Eliminates table lookups — query served entirely from index)**

A covering index contains all columns a query needs, allowing the database to return results directly from the index without accessing the table data. This eliminates random I/O table lookups, which are the most expensive part of indexed queries on large tables.

## Incorrect

```php
// ❌ Index on status only — query needs name and email too
Schema::table('users', function (Blueprint $table): void {
    $table->index('status');
});

// Query uses the index to find matching rows, then performs a table lookup
// for EACH row to fetch name and email — slow on large result sets
$activeUsers = User::select('name', 'email')
    ->where('status', 'active')
    ->get();
// EXPLAIN shows "Using index condition" — table lookup required
```

**Problems:**
- Each matching row requires a random I/O table lookup to fetch non-indexed columns
- On SSDs this adds microseconds per row; on HDDs, milliseconds per row
- Large result sets (thousands of rows) multiply the lookup cost significantly

## Correct

```php
// ✅ MySQL: include all queried columns in the composite index
Schema::table('users', function (Blueprint $table): void {
    // Covering index — all selected/filtered columns are in the index
    $table->index(['status', 'name', 'email']);
});

$activeUsers = User::select('name', 'email')
    ->where('status', 'active')
    ->get();
// EXPLAIN shows "Using index" — no table lookup needed

// ✅ PostgreSQL: use raw SQL for INCLUDE clause (non-searchable payload columns)
// This keeps the index smaller while still covering the query
DB::statement('
    CREATE INDEX users_status_covering
    ON users (status)
    INCLUDE (name, email)
');

// ✅ Verify with EXPLAIN to confirm covering index is used
$plan = DB::select('EXPLAIN SELECT name, email FROM users WHERE status = ?', ['active']);
// MySQL: look for "Using index" (not "Using index condition")
// PostgreSQL: look for "Index Only Scan"
```

**Benefits:**
- Query returns results directly from the index B-tree — zero table lookups
- Dramatically faster for queries returning many rows from a filtered subset
- PostgreSQL INCLUDE clause keeps indexed columns minimal while covering SELECT columns

Reference: [Laravel Creating Indexes](https://laravel.com/docs/13.x/migrations#creating-indexes)


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


---

## Use Cache::remember() Pattern

**Impact: HIGH (Atomic cache population eliminates redundant database queries and race conditions)**

Manually checking the cache and repopulating it in separate steps creates a race condition where multiple concurrent requests can miss the cache simultaneously and all hit the database. `Cache::remember()` handles this atomically in a single call.

## Incorrect

```php
// ❌ Manual get/put — race condition on cache miss
$products = Cache::get('products:featured');

if ($products === null) {
    // Multiple concurrent requests can reach here simultaneously
    $products = Product::where('featured', true)
        ->with('category')
        ->get();

    Cache::put('products:featured', $products, 3600);
}

return $products;
```

**Problems:**
- Race condition: concurrent requests during cache miss all execute the expensive query
- Cache stampede on high-traffic endpoints — database gets hammered when cache expires
- More code to maintain and more opportunities for bugs (e.g., forgetting to set TTL)
- No guarantee the cached value matches what was just queried (another request may overwrite)

## Correct

```php
// ✅ Cache::remember() — atomic get-or-set with TTL
$products = Cache::remember('products:featured', 3600, function () {
    return Product::where('featured', true)
        ->with('category')
        ->get();
});

// ✅ Cache::rememberForever() — for rarely-changing reference data
$countries = Cache::rememberForever('reference:countries', function () {
    return Country::orderBy('name')->get();
});

// ✅ With dynamic keys for per-user or per-entity caching
$userStats = Cache::remember(
    "users:{$user->id}:stats",
    now()->addMinutes(15),
    function () use ($user) {
        return [
            'posts_count' => $user->posts()->count(),
            'followers_count' => $user->followers()->count(),
            'total_likes' => $user->posts()->withSum('reactions', 'count')->sum('reactions_sum_count'),
        ];
    }
);
```

**Benefits:**
- Atomic operation: only one request regenerates the cache on miss
- Eliminates cache stampede — concurrent requests wait for or get the freshly cached value
- Cleaner, less error-prone code with TTL and closure in a single call
- `rememberForever()` variant for data that changes only via explicit invalidation

Reference: [Laravel Cache Usage](https://laravel.com/docs/13.x/cache#retrieve-store)


---

## Automate Cache Invalidation with Model Observers

**Impact: HIGH (Prevents stale data by coupling cache lifecycle to model events)**

Relying on developers to manually call `Cache::forget()` after every model update is error-prone and leads to stale cached data. Automating invalidation through model observers or lifecycle hooks guarantees the cache stays consistent with the database.

## Incorrect

```php
// ❌ Manual cache invalidation scattered across controllers
class ProductController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $product->update($request->validated());

        // Developer must remember to clear cache everywhere products change
        Cache::forget('products:featured');
        Cache::forget("products:{$product->id}");
        Cache::forget("categories:{$product->category_id}:products");

        return redirect()->back();
    }

    public function destroy(Product $product)
    {
        $product->delete();

        // Easy to forget cache keys here — stale data served
        Cache::forget("products:{$product->id}");
        // Forgot to clear 'products:featured' — now stale
    }
}
```

**Problems:**
- Cache keys must be tracked and cleared manually in every controller method
- Missing a single `Cache::forget()` leads to stale data served to users
- Cache logic is duplicated across controllers, commands, jobs, and listeners
- New developers are unaware of which cache keys need clearing

## Correct

```php
// ✅ Option 1: Model observer — centralized cache invalidation
// app/Observers/ProductObserver.php
class ProductObserver
{
    public function saved(Product $product): void
    {
        Cache::forget("products:{$product->id}");
        Cache::forget('products:featured');
        Cache::forget("categories:{$product->category_id}:products");
    }

    public function deleted(Product $product): void
    {
        Cache::forget("products:{$product->id}");
        Cache::forget('products:featured');
        Cache::forget("categories:{$product->category_id}:products");
    }
}

// Register in AppServiceProvider or use the ObservedBy attribute
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    // ...
}

// ✅ Option 2: Inline booted() — for simpler models
class Product extends Model
{
    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            Cache::forget("products:{$product->id}");
            Cache::forget('products:featured');
        });

        static::deleted(function (Product $product) {
            Cache::forget("products:{$product->id}");
            Cache::forget('products:featured');
        });
    }
}
```

**Benefits:**
- Cache invalidation runs automatically on every save and delete, regardless of where the change originates
- Single source of truth for which cache keys relate to which model
- Works for changes made via controllers, commands, jobs, tinker, and queued processes
- `ObservedBy` attribute (Laravel 11+) makes the binding explicit and discoverable

Reference: [Laravel Model Observers](https://laravel.com/docs/13.x/eloquent#observers)


---

## Use Cache Tags for Group Invalidation

**Impact: HIGH (Flush entire cache groups in one call instead of tracking individual keys)**

When related cache entries need to be invalidated together, tracking individual keys is brittle and error-prone. Cache tags let you assign labels to cached items and flush all items sharing a tag in a single operation.

## Incorrect

```php
// ❌ Tracking and clearing individual cache keys manually
class ProductService
{
    public function cacheProduct(Product $product): void
    {
        Cache::put("products:{$product->id}", $product, 3600);
        Cache::put("products:{$product->id}:reviews", $product->reviews, 3600);
        Cache::put("products:{$product->id}:related", $product->related, 3600);
    }

    public function clearProductCache(Product $product): void
    {
        // Must remember every key pattern — easy to miss one
        Cache::forget("products:{$product->id}");
        Cache::forget("products:{$product->id}:reviews");
        Cache::forget("products:{$product->id}:related");
        // Forgot "products:{$product->id}:pricing" — stale data
    }

    public function clearAllProducts(): void
    {
        // Impossible without iterating all product IDs
        // or flushing the entire cache
        Cache::flush(); // Nuclear option — clears everything
    }
}
```

**Problems:**
- Every new cache key must be tracked and added to the clear method
- Flushing all entries for a group requires knowing every key or flushing the entire cache
- Forgetting a single key in the clear method results in stale data
- No way to selectively flush a category of cached items

## Correct

```php
// ✅ Cache tags — group related entries and flush by tag
class ProductService
{
    public function getProduct(int $productId): Product
    {
        return Cache::tags(['products', "product:{$productId}"])
            ->remember("products:{$productId}", 3600, function () use ($productId) {
                return Product::with('category')->findOrFail($productId);
            });
    }

    public function getProductReviews(int $productId): Collection
    {
        return Cache::tags(['products', "product:{$productId}", 'reviews'])
            ->remember("products:{$productId}:reviews", 1800, function () use ($productId) {
                return Review::where('product_id', $productId)->latest()->get();
            });
    }

    public function clearProductCache(int $productId): void
    {
        // Flush everything tagged with this specific product
        Cache::tags(["product:{$productId}"])->flush();
    }

    public function clearAllProductCaches(): void
    {
        // Flush everything tagged 'products' — one call
        Cache::tags(['products'])->flush();
    }

    public function clearAllReviewCaches(): void
    {
        // Flush all reviews across all products
        Cache::tags(['reviews'])->flush();
    }
}
```

**Benefits:**
- Flush an entire group of cache entries with a single `flush()` call
- No need to track individual cache keys — tags define the group
- Multiple tags per entry allow flexible invalidation strategies (by product, by type, etc.)
- New cache entries automatically inherit the tag-based invalidation without code changes

> **Note:** Cache tags are only supported by Redis and Memcached drivers. The `file`, `database`, and `array` drivers do not support tags.

Reference: [Laravel Cache Tags](https://laravel.com/docs/13.x/cache#cache-tags)


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


---

## Use Cursor Pagination for Large Datasets

**Impact: HIGH (Eliminates slow OFFSET on deep pages — constant query time regardless of page depth)**

Traditional offset-based pagination uses `OFFSET N` which forces the database to scan and discard N rows before returning results. On a table with millions of rows, navigating to page 10,000 means the database must skip 150,000 rows, making deep pages progressively slower.

## Incorrect

```php
// ❌ Offset pagination on a large table — OFFSET grows linearly with page depth
$users = User::paginate(15);

// On page 10,000 this generates:
// SELECT * FROM users LIMIT 15 OFFSET 149985
// The database must scan and discard 149,985 rows before returning 15
```

**Problems:**
- Query time degrades linearly as users navigate deeper pages
- Database performs a full index scan up to the OFFSET value on every request
- On tables with millions of rows, deep pages can take seconds or cause timeouts

## Correct

```php
// ✅ Cursor pagination — uses WHERE id > ? for constant-time lookups
$users = User::orderBy('id')->cursorPaginate(15);

// On any page this generates:
// SELECT * FROM users WHERE id > 149985 ORDER BY id LIMIT 15
// The database seeks directly to the cursor position via index

// ✅ Works with multiple order columns
$users = User::orderBy('created_at')
    ->orderBy('id')
    ->cursorPaginate(15);

// ✅ In API responses — return cursor links instead of page numbers
return UserResource::collection(
    User::orderBy('id')->cursorPaginate(15)
);
// Response includes: next_cursor, prev_cursor instead of page numbers
```

**Benefits:**
- Constant query time regardless of how deep the user navigates — O(1) vs O(n)
- Uses efficient index seeks (WHERE id > ?) instead of scanning and discarding rows
- Ideal for infinite scroll, API feeds, and any dataset where users don't need to jump to arbitrary pages

Reference: [Laravel Cursor Pagination](https://laravel.com/docs/13.x/pagination#cursor-pagination)


---

## Use chunkById for Batch Processing

**Impact: HIGH (Prevents skipped/duplicated rows during batch operations on mutable data)**

When processing large datasets in batches, `chunk()` uses OFFSET-based queries which can skip or duplicate rows if data is modified during iteration. `chunkById()` uses `WHERE id > last_id` to guarantee every row is processed exactly once, even when rows are inserted, updated, or deleted mid-batch.

## Incorrect

```php
// ❌ chunk() uses OFFSET — rows can be skipped or duplicated if data changes
User::where('status', 'pending')->chunk(1000, function (Collection $users): void {
    foreach ($users as $user) {
        $user->update(['status' => 'processed']);
    }
});

// Chunk 1: SELECT * FROM users WHERE status = 'pending' LIMIT 1000 OFFSET 0
// After updating 1000 rows, they no longer match 'pending'
// Chunk 2: SELECT * FROM users WHERE status = 'pending' LIMIT 1000 OFFSET 1000
// OFFSET 1000 now skips the NEXT 1000 unprocessed rows — data loss
```

**Problems:**
- OFFSET shifts when rows are inserted or deleted, causing rows to be skipped or processed twice
- Updating the column used in the WHERE clause during iteration compounds the problem
- Silent data corruption — no error is raised, but rows are missed

## Correct

```php
// ✅ chunkById() uses WHERE id > last_id — consistent regardless of mutations
User::where('status', 'pending')
    ->chunkById(1000, function (Collection $users): void {
        foreach ($users as $user) {
            $user->update(['status' => 'processed']);
        }
    });

// Chunk 1: SELECT * FROM users WHERE status = 'pending' AND id > 0 ORDER BY id LIMIT 1000
// Chunk 2: SELECT * FROM users WHERE status = 'pending' AND id > 1000 ORDER BY id LIMIT 1000
// Each chunk starts after the last processed ID — no rows skipped

// ✅ Practical example: batch status update with logging
User::where('email_verified_at', null)
    ->chunkById(500, function (Collection $users): void {
        $ids = $users->pluck('id');

        User::whereIn('id', $ids)->update([
            'status' => 'unverified',
            'flagged_at' => now(),
        ]);

        Log::info('Flagged unverified users', ['count' => $ids->count()]);
    });
```

**Benefits:**
- Every row is processed exactly once, regardless of concurrent inserts, updates, or deletes
- Uses indexed primary key lookups instead of OFFSET scanning
- Safe for operations that modify the rows being iterated over

Reference: [Laravel Chunking Results](https://laravel.com/docs/13.x/eloquent#chunking-results)


---

## Use cursor() for Memory-Efficient Iteration

**Impact: HIGH (Reduces memory from O(n) to O(1) — iterate millions of rows without exhausting RAM)**

Loading an entire result set with `all()` or `get()` hydrates every row into an Eloquent model simultaneously, consuming memory proportional to the result size. `cursor()` uses a PHP generator to fetch and hydrate one row at a time, keeping memory usage constant regardless of how many rows are processed.

## Incorrect

```php
// ❌ Loads all 500,000 users into memory at once
$users = User::all();

$users->each(function (User $user): void {
    dispatch(new SendWeeklyDigest($user));
});

// Peak memory: ~500,000 Eloquent models × ~2KB each ≈ 1GB+ RAM
```

**Problems:**
- Entire result set is hydrated into memory before iteration begins
- Memory usage grows linearly with row count — easily causes out-of-memory errors
- No benefit from loading all rows upfront when processing them sequentially

## Correct

```php
// ✅ Eloquent cursor — fetches one model at a time via PHP generator
User::where('subscribed', true)
    ->cursor()
    ->each(function (User $user): void {
        dispatch(new SendWeeklyDigest($user));
    });

// Peak memory: ~1 Eloquent model at a time, regardless of total rows

// ✅ Query Builder lazy() — same concept without Eloquent hydration overhead
DB::table('users')
    ->where('subscribed', true)
    ->lazy()
    ->each(function (object $user): void {
        dispatch(new SendWeeklyDigest($user));
    });

// ✅ lazy() with chunk size control
DB::table('users')
    ->lazyById(500, 'id')
    ->each(function (object $user): void {
        // Fetches 500 rows at a time internally, yields one at a time
        dispatch(new SendWeeklyDigest($user));
    });
```

**Benefits:**
- Constant memory usage regardless of result set size — safe for millions of rows
- `cursor()` returns a `LazyCollection` backed by a PHP generator
- `lazyById()` combines the memory efficiency of generators with the consistency of ID-based chunking

Reference: [Laravel Cursors](https://laravel.com/docs/13.x/eloquent#cursors)


---

## Never Use get() on Unbounded Queries

**Impact: HIGH (Prevents memory exhaustion and timeouts from loading unpredictable result sets)**

Calling `get()` on a query without a row limit loads every matching row into memory. A query that returns 100 rows today might return 10 million rows next year. Without explicit bounds, a single request can exhaust memory, saturate the database, and crash the application.

## Incorrect

```php
// ❌ Unbounded query — loads ALL active users into memory
$users = User::where('active', true)->get();

// Could be 100 rows in development, 5 million in production
// No limit, no pagination, no chunking — a ticking time bomb

// ❌ Unbounded query hidden inside a scope
public function scopeRecent(Builder $query): Builder
{
    return $query->where('created_at', '>', now()->subDays(30));
}

// Calling User::recent()->get() loads every user from the last 30 days
// with no upper bound
```

**Problems:**
- Memory usage is unpredictable and grows with data volume over time
- A query safe in development can cause out-of-memory crashes in production
- No early warning — the problem only surfaces when the dataset grows large enough

## Correct

```php
// ✅ Paginate for display — always bounded
$users = User::where('active', true)->paginate(25);

// ✅ Chunk for batch processing — bounded memory
User::where('active', true)->chunkById(1000, function (Collection $users): void {
    foreach ($users as $user) {
        $user->notify(new AccountReminder());
    }
});

// ✅ Cursor for sequential processing — O(1) memory
User::where('active', true)->cursor()->each(function (User $user): void {
    $user->recalculateScore();
});

// ✅ Add an explicit limit as a safety net when get() is necessary
$topUsers = User::where('active', true)
    ->orderByDesc('score')
    ->limit(100)
    ->get();

// ✅ Use DB::whenQueryingForLongerThan() to detect runaway queries
DB::whenQueryingForLongerThan(5000, function (Connection $connection, QueryExecuted $event): void {
    Log::warning('Long-running queries detected', [
        'connection' => $connection->getName(),
    ]);
});
```

**Benefits:**
- Predictable memory and time boundaries regardless of data growth
- Explicit limits make resource consumption visible and reviewable in code review
- Safety mechanisms like `whenQueryingForLongerThan()` catch issues before they escalate

Reference: [Laravel Query Builder](https://laravel.com/docs/13.x/queries)


---

## Keep Transactions as Short as Possible

**Impact: HIGH (Reduces lock contention and connection hold time — prevents cascading timeouts)**

Database transactions hold locks on affected rows until the transaction commits or rolls back. Including slow operations like API calls, file uploads, or queue dispatches inside a transaction extends lock duration, blocking other requests that need the same rows and potentially causing cascading timeouts.

## Incorrect

```php
// ❌ Transaction wraps external API call and job dispatch — holds locks for seconds
DB::transaction(function (): void {
    $order = Order::create([
        'user_id' => auth()->id(),
        'total' => $total,
        'status' => 'confirmed',
    ]);

    // External API call — 200ms-5s network latency while locks are held
    $payment = PaymentGateway::charge($order->total);

    $order->update(['payment_id' => $payment->id]);

    // Queue dispatch inside transaction — if queue is backed up, commit is delayed
    dispatch(new SendOrderConfirmation($order));

    // File upload inside transaction — holds locks during I/O
    Storage::put("invoices/{$order->id}.pdf", $pdf);
});
```

**Problems:**
- External API latency extends lock duration from milliseconds to seconds
- Other requests needing the same rows queue up, causing cascading timeouts
- If the API call fails, the entire transaction rolls back — but the side effects (uploaded files, dispatched jobs) cannot be undone

## Correct

```php
// ✅ Only database operations inside the transaction
$order = DB::transaction(function () use ($total): Order {
    $order = Order::create([
        'user_id' => auth()->id(),
        'total' => $total,
        'status' => 'pending',
    ]);

    OrderItem::insert($items);

    return $order;
});

// External calls and side effects AFTER the transaction commits
$payment = PaymentGateway::charge($order->total);

$order->update(['payment_id' => $payment->id, 'status' => 'confirmed']);

// ✅ Use afterCommit() to ensure side effects only run after successful commit
DB::afterCommit(function () use ($order): void {
    dispatch(new SendOrderConfirmation($order));
    Storage::put("invoices/{$order->id}.pdf", $pdf);
});

// ✅ Queue jobs with afterCommit to prevent processing before data is committed
dispatch(new SendOrderConfirmation($order))->afterCommit();
```

**Benefits:**
- Transaction holds locks only during fast database writes — milliseconds instead of seconds
- Side effects like jobs and notifications only execute after successful commit
- External service failures don't roll back valid database changes

Reference: [Laravel Database Transactions](https://laravel.com/docs/13.x/database#database-transactions)


---

## Use Transaction Retry for Deadlocks

**Impact: HIGH (Automatically recovers from transient deadlocks instead of crashing the request)**

Deadlocks occur when two or more transactions hold locks that the other needs, creating a circular wait. The database resolves this by killing one transaction. Without retry logic, that killed transaction throws an exception and fails the user's request — even though a simple retry would succeed immediately.

## Incorrect

```php
// ❌ No retry — a single deadlock crashes the entire request
DB::transaction(function (): void {
    $sender = Account::where('id', $senderId)->lockForUpdate()->first();
    $receiver = Account::where('id', $receiverId)->lockForUpdate()->first();

    $sender->decrement('balance', $amount);
    $receiver->increment('balance', $amount);
});

// If another request locks these accounts in reverse order, one transaction
// is killed by the database with: "Deadlock found when trying to get lock"
// The request returns a 500 error to the user
```

**Problems:**
- A transient deadlock causes a permanent request failure with no recovery
- Deadlocks are normal under concurrent load — they are not bugs, they are expected
- Users see intermittent 500 errors that disappear on retry, undermining trust

## Correct

```php
// ✅ Second argument to DB::transaction() sets the retry count
DB::transaction(function (): void {
    $sender = Account::where('id', $senderId)->lockForUpdate()->first();
    $receiver = Account::where('id', $receiverId)->lockForUpdate()->first();

    $sender->decrement('balance', $amount);
    $receiver->increment('balance', $amount);
}, attempts: 3);

// If a deadlock occurs, Laravel automatically retries the entire closure
// up to 3 times before throwing the exception

// ✅ Consistent lock ordering to minimize deadlocks in the first place
DB::transaction(function () use ($senderId, $receiverId, $amount): void {
    // Always lock in ascending ID order to prevent circular waits
    $ids = collect([$senderId, $receiverId])->sort()->values();

    $accounts = Account::whereIn('id', $ids)
        ->lockForUpdate()
        ->orderBy('id')
        ->get()
        ->keyBy('id');

    $accounts[$senderId]->decrement('balance', $amount);
    $accounts[$receiverId]->increment('balance', $amount);
}, attempts: 3);
```

**Benefits:**
- Automatic recovery from transient deadlocks without user-facing errors
- The `attempts` parameter is built into Laravel's `DB::transaction()` — no external packages needed
- Consistent lock ordering combined with retry makes deadlocks rare and survivable

Reference: [Laravel Database Transactions](https://laravel.com/docs/13.x/database#database-transactions)


---

## Use lockForUpdate for Concurrent Writes

**Impact: HIGH (Prevents race conditions — serializes access to rows under concurrent modification)**

When two requests read a row, compute a new value, and write it back, they can overwrite each other's changes. Pessimistic locking with `lockForUpdate()` acquires an exclusive lock on the selected rows, forcing concurrent transactions to wait their turn instead of racing.

## Incorrect

```php
// ❌ Race condition — two concurrent requests can both read the same balance
// Request A reads balance: 1000
$account = Account::find($id);

// Request B reads balance: 1000 (same value — A hasn't written yet)
// Both calculate: 1000 - 200 = 800

$account->update(['balance' => $account->balance - $amount]);

// Both write 800 — the account should be 600 but $200 was lost
// This is a classic "lost update" race condition
```

**Problems:**
- Two concurrent reads see the same stale value and both overwrite with their own calculation
- Money, inventory, or credits can be silently lost or duplicated
- The bug is intermittent — it only manifests under concurrent load, making it hard to reproduce

## Correct

```php
// ✅ lockForUpdate() inside a transaction — serializes concurrent access
DB::transaction(function () use ($id, $amount): void {
    // SELECT * FROM accounts WHERE id = ? FOR UPDATE
    // This blocks other transactions from reading or writing this row
    $account = Account::lockForUpdate()->find($id);

    if ($account->balance < $amount) {
        throw new InsufficientFundsException();
    }

    $account->decrement('balance', $amount);
}, attempts: 3);

// ✅ Balance transfer between two accounts with pessimistic locking
DB::transaction(function () use ($senderId, $receiverId, $amount): void {
    // Lock both accounts in consistent order to prevent deadlocks
    $accounts = Account::whereIn('id', [$senderId, $receiverId])
        ->lockForUpdate()
        ->orderBy('id')
        ->get()
        ->keyBy('id');

    $sender = $accounts[$senderId];
    $receiver = $accounts[$receiverId];

    if ($sender->balance < $amount) {
        throw new InsufficientFundsException();
    }

    $sender->decrement('balance', $amount);
    $receiver->increment('balance', $amount);
}, attempts: 3);

// ✅ Use sharedLock() when you only need to prevent writes, not reads
$account = Account::sharedLock()->find($id);
// SELECT * FROM accounts WHERE id = ? LOCK IN SHARE MODE
```

**Benefits:**
- Eliminates lost updates by serializing access to contested rows
- `lockForUpdate()` blocks other transactions until the current one commits or rolls back
- Combined with `attempts: 3`, handles transient deadlocks gracefully

Reference: [Laravel Pessimistic Locking](https://laravel.com/docs/13.x/queries#pessimistic-locking)


---

## Use Expand-Contract Pattern for Zero-Downtime Migrations

**Impact: HIGH (Prevents broken deploys — running code continues working while schema evolves)**

Renaming or dropping a column in a single migration breaks the currently running application during deployment. Between the moment the migration runs and the new code is fully deployed, requests hitting old application servers reference columns that no longer exist, causing 500 errors.

## Incorrect

```php
// ❌ Single migration renames column — breaks running code during deploy
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('name', 'full_name');
        });
    }
};

// Timeline:
// 1. Migration runs: column renamed to full_name
// 2. Old code still references 'name' → 500 errors for every request
// 3. New code deploys minutes later → errors stop
// The gap between steps 1 and 3 causes downtime
```

**Problems:**
- Running application servers reference the old column name after the migration executes
- In multi-server deployments, the gap between migration and full code rollout can be minutes
- Rollback is destructive — renaming back may lose data written to the new column by new code

## Correct

```php
// ✅ Step 1: EXPAND — Add new column alongside old one (Deploy #1)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('full_name')->nullable()->after('name');
        });
    }
};

// In the User model — write to both columns during transition
protected static function booted(): void
{
    static::saving(function (User $user): void {
        $user->full_name = $user->full_name ?? $user->name;
    });
}

// ✅ Step 2: MIGRATE DATA — Backfill and switch code to new column (Deploy #2)
// Migration to backfill:
return new class extends Migration
{
    public function up(): void
    {
        User::whereNull('full_name')
            ->chunkById(1000, function (Collection $users): void {
                foreach ($users as $user) {
                    $user->update(['full_name' => $user->name]);
                }
            });
    }
};
// Update all code to read/write 'full_name' instead of 'name'

// ✅ Step 3: CONTRACT — Drop old column after all code uses new one (Deploy #3)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('name');
        });
    }
};
```

**Benefits:**
- Zero downtime — old and new code both work at every step of the migration
- Each deploy is independently reversible without data loss
- Safe for multi-server rolling deployments where old and new code coexist

Reference: [Laravel Migrations](https://laravel.com/docs/13.x/migrations)


---

## Create Indexes Without Locking Tables

**Impact: HIGH (Prevents table locks during index creation on large production tables)**

Laravel's `$table->index()` runs a standard `CREATE INDEX` which acquires a table-level lock on large tables. On a table with millions of rows, index creation can take minutes, blocking all writes and potentially reads during that time. Both MySQL and PostgreSQL support non-locking index creation that allows normal operations to continue.

## Incorrect

```php
// ❌ Standard index creation — locks the table during the entire build
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index('customer_email');
        });
    }
};

// On a table with 50 million rows, this can lock the table for 5-10 minutes
// All INSERT, UPDATE, DELETE operations are blocked during this time
// Web requests that write to this table will timeout
```

**Problems:**
- Table-level lock blocks all write operations for the duration of index creation
- On large tables, index creation can take minutes, causing application downtime
- No way to cancel without rolling back the entire migration

## Correct

```php
// ✅ MySQL — ALGORITHM=INPLACE with LOCK=NONE allows concurrent DML
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE orders ADD INDEX idx_customer_email (customer_email), ALGORITHM=INPLACE, LOCK=NONE'
        );
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('idx_customer_email');
        });
    }
};

// ✅ PostgreSQL — CREATE INDEX CONCURRENTLY does not lock the table
return new class extends Migration
{
    public function up(): void
    {
        // CONCURRENTLY cannot run inside a transaction
        DB::statement(
            'CREATE INDEX CONCURRENTLY idx_orders_customer_email ON orders (customer_email)'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_orders_customer_email');
    }
};

// ✅ For PostgreSQL: disable migration transaction wrapping
// In the migration class, add:
public bool $withinTransaction = false;
// CREATE INDEX CONCURRENTLY cannot run inside a transaction block
```

**Benefits:**
- Tables remain fully readable and writable during index creation
- No application downtime or blocked requests during deployment
- Standard `$table->index()` is still fine for small tables and initial migrations — only use raw SQL for large production tables

Reference: [Laravel Migrations](https://laravel.com/docs/13.x/migrations)


---

## Never Add NOT NULL Columns Without Defaults in Production

**Impact: HIGH (Prevents table rewrites and lock-ups when adding columns to large tables)**

Adding a NOT NULL column without a default value forces the database to rewrite every existing row to populate the new column. On large tables this acquires a long-held lock, blocks all operations, and can take minutes to hours depending on table size.

## Incorrect

```php
// ❌ NOT NULL without default — rewrites every row in the table
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->after('email');
        });
    }
};

// On a table with 10 million rows:
// MySQL rewrites every row to add the column with an implicit '' default
// The table is locked during the entire rewrite
// All queries against this table block until the migration completes
```

**Problems:**
- Database must rewrite every existing row to add the NOT NULL column
- Table is locked during the rewrite, blocking all reads and writes
- On large tables, this can take minutes or hours, causing extended downtime

## Correct

```php
// ✅ Step 1: Add column as NULLABLE first — instant, no table rewrite
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable()->after('email');
        });
    }
};

// ✅ Step 2: Backfill existing rows in batches — no locks, no downtime
return new class extends Migration
{
    public function up(): void
    {
        User::whereNull('phone')
            ->chunkById(1000, function (Collection $users): void {
                User::whereIn('id', $users->pluck('id'))
                    ->update(['phone' => '']);
            });
    }
};

// ✅ Step 3: Add NOT NULL constraint after all rows have values
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable(false)->default('')->change();
        });
    }
};

// ✅ Alternative: Add with a default value — modern MySQL/PostgreSQL handle this instantly
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->default('')->after('email');
        });
    }
};
// MySQL 8.0+ and PostgreSQL 11+ store the default in metadata without rewriting rows
```

**Benefits:**
- Adding a nullable column is an instant metadata operation — no table rewrite
- Backfilling in batches keeps the table available throughout the process
- Modern databases handle NOT NULL with DEFAULT as instant operations, but nullable-first is universally safe

Reference: [Laravel Migrations](https://laravel.com/docs/13.x/migrations)


---

## Run EXPLAIN on Slow Queries

**Impact: MEDIUM (Identifies missing indexes, full table scans, and inefficient query plans)**

Guessing why a query is slow wastes time and leads to wrong fixes. Running EXPLAIN shows the actual execution plan the database uses — revealing missing indexes, full table scans, and inefficient join strategies that no amount of code reading can uncover.

## Incorrect

```php
// ❌ Guessing at performance issues without data
// "It's probably the JOIN that's slow" — adds an index on the wrong column
// "Let's add caching" — masks the real problem instead of fixing the query

// No visibility into:
// - Whether indexes are being used
// - How many rows the database is scanning
// - Whether the query plan uses sequential scans or index lookups
```

**Problems:**
- Optimizing without EXPLAIN often targets the wrong bottleneck
- Adding indexes blindly wastes storage and slows writes without improving reads
- Caching is a band-aid — the underlying query may grow slower as data increases

## Correct

```php
// ✅ Use Laravel's built-in explain() method
$explanation = DB::table('orders')
    ->where('customer_id', $customerId)
    ->where('status', 'pending')
    ->explain()
    ->dd();

// Output shows: type, possible_keys, key, rows, Extra
// Look for:
//   type: "ALL" = full table scan (bad)
//   type: "ref" or "const" = index used (good)
//   rows: high number = scanning too many rows
//   Extra: "Using filesort" = sorting without index

// ✅ EXPLAIN ANALYZE for actual execution times (MySQL 8.0+, PostgreSQL)
DB::statement('EXPLAIN ANALYZE SELECT * FROM orders WHERE customer_id = ? AND status = ?', [
    $customerId,
    'pending',
]);

// ✅ Programmatic explain in tests or debugging
$query = Order::where('customer_id', $customerId)
    ->where('status', 'pending');

// Log the raw SQL for manual EXPLAIN
Log::debug('Query', [
    'sql' => $query->toRawSql(),
    'explain' => $query->explain()->toArray(),
]);

// ✅ What to look for in EXPLAIN output:
// | Issue              | EXPLAIN Sign                    | Fix                        |
// |--------------------|---------------------------------|----------------------------|
// | Full table scan    | type: ALL                       | Add index on WHERE columns |
// | No index used      | key: NULL                       | Create composite index     |
// | Sorting without    | Extra: Using filesort           | Add index covering ORDER   |
// | index              |                                 | BY columns                 |
// | Scanning too many  | rows: 500000+                   | Add more selective index   |
// | rows               |                                 |                            |
```

**Benefits:**
- Data-driven optimization instead of guesswork — EXPLAIN shows exactly what the database does
- Quickly identifies whether an index exists but isn't being used vs. missing entirely
- EXPLAIN ANALYZE shows actual vs. estimated row counts, revealing stale statistics

Reference: [Laravel Query Builder](https://laravel.com/docs/13.x/queries)


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


---

## Table Naming Conventions

**Impact: HIGH (Eloquent relies on naming conventions to auto-resolve models to tables)**

Laravel's Eloquent ORM automatically maps a model to its table by converting the class name to plural snake_case. Always use English for all table names — Laravel's built-in tables (`users`, `password_reset_tokens`, `failed_jobs`) are English, and mixing languages creates inconsistency. Deviating from these conventions forces manual overrides across every model, breaks implicit route model binding, and confuses developers who expect standard Laravel behavior.

## Incorrect

```php
// ❌ Singular table name — Eloquent expects plural
Schema::create('user', function (Blueprint $table) {
    $table->id();
    $table->string('name');
});

// ❌ PascalCase — Eloquent converts "OrderItem" to "order_items", not "OrderItems"
Schema::create('OrderItems', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
});

// ❌ Wrong pivot table order — must be alphabetical
Schema::create('user_role', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained();
    $table->foreignId('role_id')->constrained();
});

// ❌ Prefixed tables — adds noise, breaks Eloquent auto-resolution
Schema::create('tbl_users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
});
```

**Problems:**
- Eloquent cannot auto-resolve `User` model to a `user` table (expects `users`)
- PascalCase tables require explicit `$table` property on every model
- Non-alphabetical pivot names break `belongsToMany` without manual table overrides
- Table prefixes like `tbl_` add noise and break all convention-based resolution

## Correct

```php
// ✅ Plural snake_case — matches Eloquent's automatic resolution
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
    $table->integer('quantity');
    $table->timestamps();
});

Schema::create('blog_posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body');
    $table->timestamps();
});

// ✅ Pivot table — alphabetical order of both model names (singular snake_case)
Schema::create('role_user', function (Blueprint $table) {
    $table->foreignId('role_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->primary(['role_id', 'user_id']);
});

// ✅ Override with $table property when a custom name is required
class Product extends Model
{
    protected $table = 'catalog_products';
}

// ✅ Custom pivot table name specified explicitly
class Category extends Model
{
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }
}
```

**Benefits:**
- Eloquent auto-resolves `User` to `users`, `OrderItem` to `order_items` with zero configuration
- Alphabetical pivot naming (`role_user`) works with `belongsToMany` without specifying the table
- Consistent naming makes migrations, queries, and team collaboration predictable
- Custom `$table` overrides are explicit and self-documenting when needed

Reference: [Laravel Eloquent Conventions](https://laravel.com/docs/13.x/eloquent#eloquent-model-conventions)


---

## Column Naming Conventions

**Impact: HIGH (Laravel auto-generates foreign keys, timestamps, and casts based on column names)**

Laravel's Eloquent relies on column naming conventions to auto-resolve foreign keys, handle timestamps, and generate accessors. Always use English for all column names — Laravel's built-in columns (`created_at`, `email_verified_at`, `remember_token`) are English, and mixing languages breaks convention. Using non-standard names forces manual configuration throughout models and breaks framework features like `$model->user_id` foreign key resolution, `$dates` casting, and `constrained()` migration helpers.

## Incorrect

```php
// ❌ camelCase columns — breaks Laravel's snake_case convention
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('firstName');
    $table->string('lastName');
    $table->string('emailAddress');
});

// ❌ Wrong foreign key naming — Eloquent expects {model}_id
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user');     // ambiguous, not a valid FK convention
    $table->unsignedBigInteger('userId');   // camelCase breaks auto-resolution
});

// ❌ Bad boolean naming — unclear intent, no is_/has_/can_ prefix
Schema::create('users', function (Blueprint $table) {
    $table->boolean('active');
    $table->boolean('verified');
    $table->boolean('admin');
});

// ❌ Wrong timestamp naming — breaks Eloquent's automatic handling
Schema::create('users', function (Blueprint $table) {
    $table->timestamp('createdDate');
    $table->timestamp('updatedDate');
});
```

**Problems:**
- camelCase columns break Eloquent attribute accessors and mass assignment conventions
- Non-standard FK names prevent `constrained()` and `belongsTo()` auto-resolution
- Booleans without `is_`/`has_`/`can_` prefix are ambiguous (is `active` a scope, column, or method?)
- Non-standard timestamp names break `$model->timestamps`, `useSoftDeletes()`, and date casting

## Correct

```php
// ✅ snake_case columns
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email');
    $table->timestamps(); // creates created_at, updated_at
});

// ✅ Foreign keys follow {model}_id convention
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();       // auto-resolves to users.id
    $table->foreignId('category_id')->constrained();   // auto-resolves to categories.id
    $table->timestamps();
});

// ✅ Booleans use is_, has_, can_ prefix
Schema::create('users', function (Blueprint $table) {
    $table->boolean('is_active')->default(false);
    $table->boolean('has_verified_email')->default(false);
    $table->boolean('can_edit')->default(false);
});

// ✅ Timestamps follow Laravel convention with _at suffix
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->timestamp('published_at')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->softDeletes(); // creates deleted_at
    $table->timestamps();  // creates created_at, updated_at
});

// ✅ Polymorphic columns match morphTo method name + _id/_type
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->morphs('commentable'); // creates commentable_id, commentable_type
    $table->text('body');
    $table->timestamps();
});

// In the Comment model:
class Comment extends Model
{
    public function commentable(): MorphTo
    {
        return $this->morphTo(); // auto-resolves commentable_id and commentable_type
    }
}
```

**Benefits:**
- `foreignId('user_id')->constrained()` auto-resolves table and column with zero configuration
- `is_active`, `has_verified_email` clearly communicate boolean intent in queries and conditions
- Standard `created_at`/`updated_at` work with Eloquent's `$timestamps` and date casting automatically
- Polymorphic `_id`/`_type` columns match `morphTo()` method name, enabling auto-resolution

Reference: [Laravel Eloquent Conventions](https://laravel.com/docs/13.x/eloquent#eloquent-model-conventions)


---

## Relationship Method Naming

**Impact: HIGH (Wrong method names break eager loading, `whereHas`, and Eloquent's convention-based FK resolution)**

Eloquent derives foreign key names from relationship method names. A `belongsTo` method named `user()` automatically looks for a `user_id` column. Incorrect naming breaks this auto-resolution, causes N+1 query issues with eager loading, and makes `whereHas` queries fail silently or target wrong columns.

## Incorrect

```php
// ❌ Plural for belongsTo — should be singular
class Post extends Model
{
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
        // Eloquent looks for "users_id" column, which doesn't exist
    }
}

// ❌ Singular for hasMany — should be plural
class User extends Model
{
    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class);
        // Misleading: returns a Collection, not a single model
    }
}

// ❌ Non-descriptive method name — unclear intent
class User extends Model
{
    public function data(): HasOne
    {
        return $this->hasOne(Profile::class);
        // "data" tells nothing about the related model
    }
}
```

**Problems:**
- `belongsTo` named `users()` makes Eloquent look for `users_id` instead of `user_id`
- Singular `comment()` on a hasMany is misleading — the method returns a Collection, not a model
- Vague names like `data()` make eager loading calls (`with('data')`) unreadable and unmaintainable
- Incorrect names break `whereHas('users')` queries and cause hard-to-debug runtime errors

## Correct

```php
// ✅ belongsTo → singular (returns one model, FK derived from method name)
class Post extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
        // Auto-resolves: looks for "user_id" column on posts table
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
        // Auto-resolves: looks for "category_id" column on posts table
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
        // Custom FK specified when method name differs from column prefix
    }
}

// ✅ hasMany → plural (returns a Collection)
class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
        // Auto-resolves: looks for "user_id" on posts table
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

// ✅ hasOne → singular (returns one model)
class User extends Model
{
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }
}

// ✅ belongsToMany → plural (returns a Collection via pivot)
class User extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
        // Auto-resolves: uses "role_user" pivot table (alphabetical)
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}

// ✅ morphMany → plural (returns a Collection)
class Post extends Model
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}

// ✅ morphTo → singular, describing the polymorphic relationship
class Comment extends Model
{
    public function commentable(): MorphTo
    {
        return $this->morphTo();
        // Auto-resolves: uses commentable_id and commentable_type columns
    }
}

class Image extends Model
{
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
```

**Benefits:**
- `user()` on belongsTo auto-resolves to `user_id` — no manual FK specification needed
- Plural `comments()` on hasMany clearly signals a Collection return type
- Eager loading reads naturally: `Post::with(['user', 'comments', 'tags'])->get()`
- `whereHas('comments')` and `withCount('orders')` work without additional configuration

Reference: [Laravel Eloquent Relationships](https://laravel.com/docs/13.x/eloquent-relationships)


---

## Migration and Index Naming

**Impact: HIGH (Consistent migration names enable rollbacks and team collaboration)**

Laravel migration filenames serve as a changelog for your database schema. Vague or inconsistent names make rollbacks unpredictable, obscure the migration history, and create merge conflicts when team members cannot understand what each migration does at a glance.

## Incorrect

```php
// ❌ Vague name: 2026_03_14_000000_update_users.php — update what?
// Impossible to know what changed without reading the file
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable();
    $table->boolean('is_active')->default(true);
});

// ❌ Too broad: one migration doing multiple unrelated changes
// File: 2026_03_14_000000_update_database.php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable();
});

Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::table('posts', function (Blueprint $table) {
    $table->dropColumn('legacy_field');
});

// ❌ Manual index names that don't follow convention
Schema::table('users', function (Blueprint $table) {
    $table->unique('email', 'idx_email');        // non-standard prefix
    $table->index('last_name', 'my_index');      // meaningless name
});
```

**Problems:**
- `update_users` gives no indication of what columns are added, removed, or modified
- Multi-concern migrations cannot be partially rolled back and create confusing diffs
- Non-standard index names make debugging and dropping indexes error-prone
- Vague names slow down team code reviews and make `migrate:status` output unhelpful

## Correct

```php
// ✅ Descriptive migration names that explain the change
// File: 2026_03_14_000000_create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});

// File: 2026_03_14_000001_add_phone_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->string('phone')->nullable()->after('email');
});

// File: 2026_03_14_000002_create_role_user_table.php
Schema::create('role_user', function (Blueprint $table) {
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->primary(['role_id', 'user_id']);
});

// ✅ Generate with artisan for consistent naming
// php artisan make:migration create_users_table
// php artisan make:migration add_phone_to_users_table --table=users
// php artisan make:migration create_role_user_table

// ✅ Let Laravel auto-generate index names: {table}_{column}_{type}
Schema::table('users', function (Blueprint $table) {
    $table->unique('email');
    // Auto-generated name: users_email_unique
});

Schema::table('posts', function (Blueprint $table) {
    $table->index('published_at');
    // Auto-generated name: posts_published_at_index
});

// ✅ Custom index name only when the auto-generated name would be too long
Schema::table('order_items', function (Blueprint $table) {
    $table->unique(['order_id', 'product_id', 'variant_id'], 'order_product_variant_unique');
});

// ✅ One migration per concern — easy to rollback and review
// File: 2026_03_14_000003_add_is_active_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_active')->default(true)->after('email');
});
```

**Benefits:**
- `add_phone_to_users_table` is immediately clear in `migrate:status` and git history
- One concern per migration enables clean rollbacks with `migrate:rollback --step=1`
- Laravel's auto-generated index names (`users_email_unique`) follow a predictable `{table}_{column}_{type}` pattern
- Artisan generators enforce consistent naming: `php artisan make:migration add_phone_to_users_table --table=users`

Reference: [Laravel Migrations](https://laravel.com/docs/13.x/migrations)


---

