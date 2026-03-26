---
title: Column Naming Conventions
impact: HIGH
impactDescription: "Laravel auto-generates foreign keys, timestamps, and casts based on column names"
tags: naming, columns, conventions, foreign-keys
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
