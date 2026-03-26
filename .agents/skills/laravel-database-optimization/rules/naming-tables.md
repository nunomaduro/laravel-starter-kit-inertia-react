---
title: Table Naming Conventions
impact: HIGH
impactDescription: "Eloquent relies on naming conventions to auto-resolve models to tables"
tags: naming, tables, conventions, eloquent
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
