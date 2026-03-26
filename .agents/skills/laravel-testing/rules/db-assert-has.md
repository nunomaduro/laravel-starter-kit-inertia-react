---
title: assertDatabaseHas and assertModelExists
impact: HIGH
impactDescription: Verifies that operations correctly persist data to the database
tags: database, assertions, assertDatabaseHas, assertModelExists, pest
---

## assertDatabaseHas and assertModelExists

**Impact: HIGH (Verifies that operations correctly persist data to the database)**

Use `assertDatabaseHas()` to verify that a table row matching given key/value pairs exists after an operation. Use `assertModelExists()` when you have an Eloquent model instance. Avoid re-fetching the model just to check it exists.

## Bad Example

```php
<?php

// Re-fetches from DB and accesses attributes — noisy and fragile
test('post is created', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/api/posts', [
        'title' => 'Hello',
        'body'  => 'World',
    ]);

    $post = Post::first(); // assumes only one post in DB — brittle
    expect($post)->not->toBeNull();
    expect($post->title)->toBe('Hello');
    expect($post->user_id)->toBe($user->id);
});

// Checks only the response, not persistence
test('user is registered', function () {
    $this->postJson('/api/register', [
        'name'  => 'Alice',
        'email' => 'alice@example.com',
    ]);

    // Never checks the DB — response could be mocked or cached
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// assertDatabaseHas — verify row exists with matching columns
test('creating a post persists it to the database', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Hello World',
            'body'  => 'Some content.',
        ])
        ->assertStatus(201);

    $this->assertDatabaseHas('posts', [
        'title'   => 'Hello World',
        'user_id' => $user->id,
    ]);
});

// assertModelExists — cleaner when you have the model instance
test('profile is created when user registers', function () {
    $this->postJson('/api/register', [
        'name'                  => 'Alice',
        'email'                 => 'alice@example.com',
        'password'              => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $user = User::whereEmail('alice@example.com')->firstOrFail();
    $this->assertModelExists($user);
    $this->assertModelExists($user->profile); // related model also created
});

// Combine response and DB assertion
test('updating a post changes both response and database', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create(['title' => 'Old Title']);

    $this->actingAs($user)
        ->patchJson("/api/posts/{$post->id}", ['title' => 'New Title'])
        ->assertOk()
        ->assertJsonPath('data.title', 'New Title');

    $this->assertDatabaseHas('posts', [
        'id'    => $post->id,
        'title' => 'New Title',
    ]);
});
```

## Why It Matters

- **Persistence verified**: HTTP response alone doesn't confirm the DB was written — both must be checked
- **Targeted**: `assertDatabaseHas` checks only the relevant columns, ignoring others
- **Clear intent**: `assertModelExists($model)` reads naturally compared to manual `find()`

Reference: [Laravel Database Testing — Assertions](https://laravel.com/docs/13.x/database-testing#available-assertions)
