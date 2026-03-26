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
