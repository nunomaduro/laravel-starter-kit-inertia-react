---
title: Factory Relationship Helpers
impact: HIGH
impactDescription: Reduces setup boilerplate and correctly wires related models in tests
tags: factory, relationships, has, for, recycle, afterCreating, pest
---

## Factory Relationship Helpers

**Impact: HIGH (Reduces setup boilerplate and correctly wires related models in tests)**

Use `has()` for HasMany/MorphMany relationships, `for()` for BelongsTo, `recycle()` to reuse existing models instead of creating duplicates, and `afterCreating()` for post-creation side effects. These helpers wire foreign keys correctly and keep test setup concise.

## Bad Example

```php
<?php

// Manual relationship wiring — brittle and verbose
test('author post count', function () {
    $user  = User::factory()->create();
    $post1 = Post::factory()->create(['user_id' => $user->id]); // manual FK
    $post2 = Post::factory()->create(['user_id' => $user->id]);
    $post3 = Post::factory()->create(['user_id' => $user->id]);

    expect($user->posts()->count())->toBe(3);
});

// Creates a new user per post — unnecessary records, higher DB cost
test('posts belong to users', function () {
    $posts = Post::factory()->count(5)->create(); // each creates its own user
    // all 5 posts have different authors, which may not match the test intent
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// for() — BelongsTo parent relationship
test('post belongs to correct author', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    expect($post->user_id)->toBe($user->id);
});

// has() — HasMany child relationship
test('author has three posts', function () {
    $user = User::factory()
        ->has(Post::factory()->count(3))
        ->create();

    expect($user->posts()->count())->toBe(3);
});

// has() with named relationship method
test('user has pending orders', function () {
    $user = User::factory()
        ->has(Order::factory()->count(2)->pending(), 'orders')
        ->create();

    $this->actingAs($user)
        ->getJson('/api/orders')
        ->assertJsonCount(2, 'data');
});

// recycle() — reuse existing models, avoid duplicates
test('posts by same author share one user record', function () {
    $user  = User::factory()->create();
    $posts = Post::factory()
        ->count(5)
        ->recycle($user) // all 5 posts share this single user
        ->create();

    expect(User::count())->toBe(1); // no extra users created
});

// afterCreating() — run side effects after model is persisted
// (defined in the factory class)
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'  => fake()->sentence(),
            'body'   => fake()->paragraphs(3, true),
            'status' => 'draft',
        ];
    }

    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            $tags = Tag::factory()->count($count)->create();
            $post->tags()->attach($tags);
        });
    }
}
```

**Using afterCreating state in a test:**

```php
<?php

test('post tags appear in response', function () {
    $post = Post::factory()->withTags(2)->create();

    $this->getJson("/api/posts/{$post->id}")
        ->assertJsonCount(2, 'data.tags');
});
```

## Why It Matters

- **Correct FK wiring**: `for()` and `has()` use Eloquent relationships — no manual `user_id` setting
- **No duplicates**: `recycle()` prevents unnecessary records that slow down tests
- **Encapsulated setup**: `afterCreating()` keeps pivot/join setup inside the factory

Reference: [Laravel Eloquent Factories — Relationships](https://laravel.com/docs/13.x/eloquent-factories#factory-relationships)
