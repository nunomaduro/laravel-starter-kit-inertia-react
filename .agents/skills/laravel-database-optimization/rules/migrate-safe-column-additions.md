---
title: Never Add NOT NULL Columns Without Defaults in Production
impact: HIGH
impactDescription: "Prevents table rewrites and lock-ups when adding columns to large tables"
tags: migrations, columns, production, safety
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
