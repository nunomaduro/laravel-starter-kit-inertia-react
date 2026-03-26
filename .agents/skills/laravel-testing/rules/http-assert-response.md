---
title: HTTP Response Assertions
impact: CRITICAL
impactDescription: Catches regressions in status codes, redirects, and response shape
tags: http, assertions, assertStatus, assertJson, assertRedirect, pest
---

## HTTP Response Assertions

**Impact: CRITICAL (Catches regressions in status codes, redirects, and response shape)**

Use the full range of HTTP assertion methods on `TestResponse` to verify status codes, JSON content, redirects, headers, and validation errors. Always assert the specific status code — never rely on `assertOk()` alone when a `201` or `422` is expected.

## Bad Example

```php
<?php

// Only checks 200 — misses 201, 422, 403 distinctions
test('store post', function () {
    $response = $this->post('/posts', ['title' => 'x']);
    $response->assertOk(); // passes even if 200 instead of 201
});

// Checks too broadly — assertJson only checks subset, not structure
test('post list', function () {
    $response = $this->getJson('/api/posts');
    $response->assertJson([]); // always passes — empty array is a subset of anything
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Assert exact status and JSON keys
test('creating a post returns 201 with resource data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Hello',
            'body'  => 'World',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'title', 'body', 'created_at'],
        ]);
});

// Assert validation errors with assertJsonValidationErrors
test('creating a post without title returns 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/posts', ['body' => 'No title'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

// Assert redirect after web form submit
test('web form redirects after store', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', ['title' => 'Hello', 'body' => 'World'])
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success');
});

// Assert forbidden for unauthorized access
test('guest cannot create a post', function () {
    $this->postJson('/api/posts', ['title' => 'Hi'])
        ->assertUnauthorized(); // 401
});

// Assert specific JSON path value
test('post response contains correct author', function () {
    $user = User::factory()->create(['name' => 'Alice']);
    $post = Post::factory()->for($user)->create();

    $this->getJson("/api/posts/{$post->id}")
        ->assertOk()
        ->assertJsonPath('data.author.name', 'Alice');
});
```

## Key Assertion Methods

| Method | Use Case |
|--------|----------|
| `assertStatus(int)` | Exact HTTP status code |
| `assertOk()` | 200 |
| `assertCreated()` | 201 |
| `assertNoContent()` | 204 |
| `assertNotFound()` | 404 |
| `assertForbidden()` | 403 |
| `assertUnauthorized()` | 401 |
| `assertUnprocessable()` | 422 |
| `assertJson(array)` | JSON contains subset |
| `assertJsonPath(key, value)` | Specific JSON path value |
| `assertJsonStructure(array)` | JSON key structure exists |
| `assertJsonMissing(array)` | Keys/values absent from JSON |
| `assertJsonValidationErrors(array)` | Validation error fields |
| `assertRedirect(url)` | Redirect location |
| `assertSessionHas(key)` | Session value exists |

## Why It Matters

- **Precision**: Exact status codes distinguish `200 OK` from `201 Created` from `422 Unprocessable`
- **Regression safety**: Shape assertions catch API contract breakages before production
- **Validation coverage**: `assertJsonValidationErrors` ensures form rules are applied

Reference: [Laravel HTTP Tests — Available Assertions](https://laravel.com/docs/13.x/http-tests#available-assertions)
