---
title: Asserting Soft Deletes
impact: MEDIUM
impactDescription: Verifies soft-delete behaviour without accidentally treating records as hard-deleted
tags: database, soft-delete, assertSoftDeleted, assertNotSoftDeleted, trashed, pest
---

## Asserting Soft Deletes

**Impact: MEDIUM (Verifies soft-delete behaviour without accidentally treating records as hard-deleted)**

Use `assertSoftDeleted()` to confirm a record has a non-null `deleted_at` timestamp while still existing in the database. Use `assertNotSoftDeleted()` to assert a model is active. Use the built-in `trashed()` factory state to generate pre-soft-deleted records for restore tests.

## Bad Example

```php
<?php

// assertModelMissing is wrong for soft deletes — the row still exists
test('post is soft deleted', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)->deleteJson("/api/posts/{$post->id}");

    $this->assertModelMissing($post); // WRONG: the row is still there with deleted_at set
});

// Manually checking deleted_at is verbose and bypasses built-in helpers
test('post has deleted_at', function () {
    $post = Post::factory()->create();
    $post->delete();

    $fresh = Post::withTrashed()->find($post->id);
    expect($fresh->deleted_at)->not->toBeNull(); // verbose
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// assertSoftDeleted — confirms deleted_at is set, row still exists in DB
test('deleting a post soft-deletes it', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertNoContent();

    $this->assertSoftDeleted($post);
});

// assertNotSoftDeleted — verify active record is not accidentally trashed
test('restoring a post removes deleted_at', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->trashed()->create();

    $this->actingAs($user)
        ->postJson("/api/posts/{$post->id}/restore")
        ->assertOk();

    $this->assertNotSoftDeleted($post);
});

// trashed() factory state — create pre-soft-deleted records
test('trashed posts do not appear in feed', function () {
    $user = User::factory()->create();

    Post::factory()->for($user)->count(2)->create();           // active
    Post::factory()->for($user)->count(1)->trashed()->create(); // soft-deleted

    $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertJsonCount(2, 'data'); // trashed post excluded
});

// assertSoftDeleted with table name and attributes
test('membership is soft deleted on cancellation', function () {
    $membership = Membership::factory()->create();

    $this->postJson("/api/memberships/{$membership->id}/cancel")
        ->assertNoContent();

    $this->assertSoftDeleted('memberships', ['id' => $membership->id]);
});
```

## Why It Matters

- **Correctness**: `assertModelMissing` passes only if the row is gone — wrong for soft deletes
- **Built-in**: `trashed()` factory state is available on all models with `SoftDeletes` automatically
- **Completeness**: Tests both the delete (soft) and restore paths

Reference: [Laravel Database Testing — assertSoftDeleted](https://laravel.com/docs/13.x/database-testing#assert-soft-deleted)
