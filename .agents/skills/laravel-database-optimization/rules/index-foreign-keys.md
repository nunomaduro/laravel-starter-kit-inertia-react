---
title: Index All Foreign Key Columns
impact: CRITICAL
impactDescription: "Prevents full table scans on JOINs and relationship queries"
tags: indexes, foreign-keys, migrations, performance
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
