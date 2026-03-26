---
title: Migration and Index Naming
impact: HIGH
impactDescription: "Consistent migration names enable rollbacks and team collaboration"
tags: naming, migrations, indexes, conventions
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
