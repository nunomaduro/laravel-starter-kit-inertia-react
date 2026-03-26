---
title: Fluent JSON Assertions with AssertableJson
impact: HIGH
impactDescription: Enables precise, readable assertions on complex JSON structures
tags: http, assertJson, AssertableJson, fluent, json, pest
---

## Fluent JSON Assertions with AssertableJson

**Impact: HIGH (Enables precise, readable assertions on complex JSON structures)**

Pass a closure to `assertJson()` to receive an `AssertableJson` instance. This fluent API allows you to assert specific values, count nested arrays, check for missing keys, and drill into nested structures — all without array comparison fragility.

## Bad Example

```php
<?php

// Brittle array comparison — breaks if any extra key is added to response
test('post index returns posts', function () {
    $posts = Post::factory()->count(3)->create();

    $this->getJson('/api/posts')
        ->assertJson($posts->toArray()); // fails if response wraps in 'data' key
});

// Too vague — only checks top-level key exists
test('post show', function () {
    $post = Post::factory()->create(['title' => 'Hello']);

    $this->getJson("/api/posts/{$post->id}")
        ->assertJson(['title' => 'Hello']); // passes even if nested under 'data'
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Assert exact path values and structure
test('post show returns resource shape', function () {
    $user = User::factory()->create(['name' => 'Alice']);
    $post = Post::factory()->for($user)->create(['title' => 'Hello World']);

    $this->getJson("/api/posts/{$post->id}")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data')
            ->where('data.title', 'Hello World')
            ->where('data.author.name', 'Alice')
            ->has('data.id')
            ->missing('data.password') // sensitive field must not leak
        );
});

// Assert paginated list count and first item shape
test('post index returns paginated posts', function () {
    Post::factory()->count(5)->create();

    $this->getJson('/api/posts')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 5)               // exactly 5 items in data array
            ->has('data.0', fn ($item) =>  // inspect first item shape
                $item->hasAll(['id', 'title', 'created_at'])
                     ->missing('body')     // body not included in list view
            )
            ->has('meta')
            ->where('meta.total', 5)
        );
});

// Assert conditional value with closure
test('post marked as featured has featured flag', function () {
    $post = Post::factory()->create(['is_featured' => true]);

    $this->getJson("/api/posts/{$post->id}")
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.is_featured', true)
            ->etc() // ignore remaining keys
        );
});
```

## AssertableJson Key Methods

| Method | Description |
|--------|-------------|
| `has(key)` | Key exists |
| `has(key, count)` | Array key has exact count |
| `has(key, fn)` | Key exists and passes inner callback |
| `where(key, value)` | Key equals value |
| `whereNot(key, value)` | Key does not equal value |
| `missing(key)` | Key is absent |
| `missingAll([keys])` | All listed keys are absent |
| `hasAll([keys])` | All listed keys exist |
| `etc()` | Ignore any additional keys |

## Why It Matters

- **Precision**: Test exact field values and nested structure without brittle full-array comparisons
- **Security**: `missing('password')` explicitly asserts sensitive fields are never leaked
- **Readability**: Fluent chain reads like English — clear intent, no noise
- **Flexibility**: `etc()` prevents over-constraining when only a subset matters

Reference: [Laravel HTTP Tests — Fluent JSON Testing](https://laravel.com/docs/13.x/http-tests#fluent-json-testing)
