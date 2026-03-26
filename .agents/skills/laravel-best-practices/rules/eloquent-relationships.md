---
title: Eloquent Relationships
impact: CRITICAL
impactDescription: Expressive and efficient relationship management
tags: eloquent, relationships, associations
---

## Eloquent Relationships

**Impact: CRITICAL (Expressive and efficient relationship management)**

Define relationships properly and use them effectively for clean, efficient database queries.

## Bad Example

```php
// Manual joins instead of relationships
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Raw queries for related data
$user = User::find(1);
$posts = DB::table('posts')->where('user_id', $user->id)->get();

// Inefficient nested queries
$users = User::all();
foreach ($users as $user) {
    $user->posts_count = DB::table('posts')
        ->where('user_id', $user->id)
        ->count();
}
```

## Good Example

```php
// Model with well-defined relationships
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Model
{
    // One to Many
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // One to Many with default ordering
    public function latestPosts(): HasMany
    {
        return $this->hasMany(Post::class)->latest()->limit(5);
    }

    // One to One
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    // Many to Many
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps()
            ->withPivot('assigned_by');
    }

    // Has Many Through
    public function postComments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Post::class);
    }

    // Polymorphic
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // One of Many (get single record from hasMany)
    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function oldestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->oldestOfMany();
    }

    public function largestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->ofMany('total', 'max');
    }
}
```

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    // Inverse relationship
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // With default model (prevents null checks)
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault([
            'name' => 'Uncategorized',
        ]);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

```php
// Using relationships effectively
$user = User::with(['posts', 'profile'])->find(1);

// Access related models
$user->posts;           // Collection of posts
$user->posts->count();  // Count without extra query
$user->profile;         // Single profile or null

// Query relationships
$publishedPosts = $user->posts()->published()->get();

// Create through relationship
$user->posts()->create([
    'title' => 'New Post',
    'body' => 'Content...',
]);

// Attach/detach many-to-many
$user->roles()->attach($roleId);
$user->roles()->attach([1, 2, 3]);
$user->roles()->detach($roleId);
$user->roles()->sync([1, 2, 3]); // Sync exactly these

// With pivot data
$user->roles()->attach($roleId, ['assigned_by' => auth()->id()]);

// Query by relationship existence
$usersWithPosts = User::has('posts')->get();
$usersWithManyPosts = User::has('posts', '>=', 5)->get();

// WhereHas for conditional relationship queries
$users = User::whereHas('posts', function ($query) {
    $query->where('published', true);
})->get();

// Load count
$users = User::withCount('posts')->get();
// Access: $user->posts_count

// Conditional count
$users = User::withCount([
    'posts',
    'posts as published_posts_count' => fn($q) => $q->published(),
])->get();
```

## Why

- **Readability**: Relationships are self-documenting
- **Efficiency**: Eager loading prevents N+1 queries
- **Consistency**: Standard API for accessing related data
- **Integrity**: Framework handles foreign keys and cascades
- **Flexibility**: Easy to add constraints to relationship queries
- **Maintainability**: Changes to relationships in one place
