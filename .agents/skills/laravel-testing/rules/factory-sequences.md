---
title: Factory Sequences for Varied Records
impact: HIGH
impactDescription: Creates realistic varied datasets without repetitive factory calls
tags: factory, sequence, count, varied-data, pest
---

## Factory Sequences for Varied Records

**Impact: HIGH (Creates realistic varied datasets without repetitive factory calls)**

Use `sequence()` to assign alternating or ordered attribute values when creating multiple records. This avoids creating multiple separate factory calls and makes the data pattern explicit in the test.

## Bad Example

```php
<?php

// Separate factory calls — verbose, hard to see the pattern
test('feed only shows published posts', function () {
    $user = User::factory()->create();

    Post::factory()->for($user)->create(['status' => 'published']);
    Post::factory()->for($user)->create(['status' => 'draft']);
    Post::factory()->for($user)->create(['status' => 'published']);
    Post::factory()->for($user)->create(['status' => 'archived']);

    $response = $this->actingAs($user)->getJson('/api/feed');
    $response->assertJsonCount(2, 'data');
});

// Using count() without variation — all records are identical
test('admin sees all statuses', function () {
    Post::factory()->count(5)->create(); // all have same default status
    // cannot test filtering without varied data
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Alternate between values using sequence()
test('feed only shows published posts', function () {
    $user = User::factory()->create();

    Post::factory()
        ->for($user)
        ->count(4)
        ->sequence(
            ['status' => 'published'],
            ['status' => 'draft'],
            ['status' => 'published'],
            ['status' => 'archived'],
        )
        ->create();

    $this->actingAs($user)
        ->getJson('/api/feed')
        ->assertJsonCount(2, 'data');
});

// Sequence with index for unique values
test('posts are ordered by creation date', function () {
    Post::factory()
        ->count(3)
        ->sequence(fn (Sequence $sequence) => [
            'title'      => "Post {$sequence->index}",
            'created_at' => now()->subDays($sequence->index),
        ])
        ->create();

    $response = $this->getJson('/api/posts?sort=newest');

    $response->assertJsonPath('data.0.title', 'Post 0'); // most recent first
});
```

**Combining sequence with states:**

```php
<?php

use App\Models\Order;

test('order stats show correct counts by status', function () {
    Order::factory()
        ->count(6)
        ->sequence(
            ['status' => 'pending'],
            ['status' => 'processing'],
            ['status' => 'shipped'],
            ['status' => 'delivered'],
            ['status' => 'pending'],
            ['status' => 'cancelled'],
        )
        ->create();

    $this->getJson('/api/admin/order-stats')
        ->assertOk()
        ->assertJsonPath('pending', 2)
        ->assertJsonPath('shipped', 1)
        ->assertJsonPath('cancelled', 1);
});
```

## Why It Matters

- **Concise**: One factory chain replaces multiple separate `create()` calls
- **Explicit**: The data pattern is visible in the test — the reader understands the scenario
- **Realistic**: Varied statuses, dates, and types create meaningful filtering/sorting tests

Reference: [Laravel Eloquent Factories — Sequences](https://laravel.com/docs/13.x/eloquent-factories#sequences)
