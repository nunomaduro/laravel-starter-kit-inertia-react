---
title: Create Indexes Without Locking Tables
impact: HIGH
impactDescription: "Prevents table locks during index creation on large production tables"
tags: migrations, indexes, locking, production
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
