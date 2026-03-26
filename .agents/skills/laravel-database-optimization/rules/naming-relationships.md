---
title: Relationship Method Naming
impact: HIGH
impactDescription: "Wrong method names break eager loading, `whereHas`, and Eloquent's convention-based FK resolution"
tags: naming, relationships, eloquent, conventions
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
