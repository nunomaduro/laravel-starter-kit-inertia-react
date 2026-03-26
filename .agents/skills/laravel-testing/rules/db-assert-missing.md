---
title: assertDatabaseMissing and assertModelMissing
impact: HIGH
impactDescription: Confirms that delete operations actually remove data from the database
tags: database, assertions, assertDatabaseMissing, assertModelMissing, delete, pest
---

## assertDatabaseMissing and assertModelMissing

**Impact: HIGH (Confirms that delete operations actually remove data from the database)**

Use `assertDatabaseMissing()` to verify that no row matching given constraints exists in a table. Use `assertModelMissing()` when you have the model instance. Always pair a delete operation with a missing assertion — a 200/204 response alone does not confirm the record was removed.

## Bad Example

```php
<?php

// Only checks HTTP status — not whether the DB row was removed
test('post is deleted', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertNoContent(); // passes even if delete logic has a bug and does nothing
});

// Queries the DB wrong way — assumes first() is the deleted model
test('user is removed', function () {
    $user = User::factory()->create();
    $this->delete("/admin/users/{$user->id}");

    expect(User::first())->toBeNull(); // fails if other users exist in DB
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// assertModelMissing — clearest when you have the instance
test('deleting a post removes it from the database', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertNoContent();

    $this->assertModelMissing($post);
});

// assertDatabaseMissing — when checking by attribute value
test('removing a tag detaches it from posts', function () {
    $tag  = Tag::factory()->create(['name' => 'laravel']);
    $post = Post::factory()->create();
    $post->tags()->attach($tag);

    $this->deleteJson("/api/tags/{$tag->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('tags', ['name' => 'laravel']);
    $this->assertDatabaseMissing('post_tag', ['tag_id' => $tag->id]);
});

// Assert both the model and pivot records are gone
test('deleting user removes their posts', function () {
    $user  = User::factory()->create();
    $posts = Post::factory()->for($user)->count(3)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->deleteJson("/api/admin/users/{$user->id}")
        ->assertNoContent();

    $this->assertModelMissing($user);

    foreach ($posts as $post) {
        $this->assertModelMissing($post);
    }
});
```

## Why It Matters

- **Behaviour verified**: A `204 No Content` alone says nothing about what changed in the DB
- **Cascade check**: `assertDatabaseMissing` verifies pivot/related records are also cleaned up
- **Explicit intent**: `assertModelMissing` clearly communicates what the test is verifying

Reference: [Laravel Database Testing — assertDatabaseMissing](https://laravel.com/docs/13.x/database-testing#assert-database-missing)
