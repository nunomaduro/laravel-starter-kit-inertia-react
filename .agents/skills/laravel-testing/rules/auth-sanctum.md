---
title: API Authentication Testing with Sanctum
impact: HIGH
impactDescription: Enables correct testing of token-authenticated API endpoints
tags: authentication, sanctum, api, token, actingAs, pest
---

## API Authentication Testing with Sanctum

**Impact: HIGH (Enables correct testing of token-authenticated API endpoints)**

Use `Sanctum::actingAs($user)` to authenticate API requests with a token in tests. Pass an array of abilities to test scope-restricted endpoints. Always test both valid and missing token paths for protected API routes.

## Bad Example

```php
<?php

// Uses session-based actingAs() for an API route protected by Sanctum
// — Sanctum middleware checks for Bearer token, not session
test('user can get their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user) // wrong guard for Sanctum API
        ->getJson('/api/user')
        ->assertOk(); // may work locally but fails with stateless middleware
});

// Hard-codes a token string — fragile and not how Sanctum works in tests
test('api request with token', function () {
    $user  = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user')
        ->assertOk();
    // Verbose — Sanctum::actingAs() is simpler and correct
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Basic Sanctum authentication
test('authenticated user can retrieve their profile', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

// Test with specific token abilities (scopes)
test('token with read ability can list posts', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['posts:read']);

    $this->getJson('/api/posts')->assertOk();
});

test('token without write ability cannot create posts', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['posts:read']); // no write ability

    $this->postJson('/api/posts', ['title' => 'Hello'])
        ->assertForbidden();
});

// Wildcard abilities — acts as if all abilities are granted
test('admin token with all abilities can delete any post', function () {
    $admin = User::factory()->admin()->create();
    $post  = Post::factory()->create();

    Sanctum::actingAs($admin, ['*']);

    $this->deleteJson("/api/posts/{$post->id}")
        ->assertNoContent();
});

// Unauthenticated request returns 401
test('unauthenticated request to api returns 401', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});

// Test ownership restriction on an API resource
test('user cannot update another users post via api', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post  = Post::factory()->for($owner)->create();

    Sanctum::actingAs($other, ['posts:write']);

    $this->patchJson("/api/posts/{$post->id}", ['title' => 'Hijacked'])
        ->assertForbidden();
});
```

## Why It Matters

- **Correct guard**: `Sanctum::actingAs()` targets the `sanctum` guard — no session pollution
- **Ability testing**: Pass specific abilities to test scope restrictions without creating real tokens
- **Stateless**: Works correctly with `EnsureFrontendRequestsAreStateful` excluded from API routes

Reference: [Laravel Sanctum — Testing](https://laravel.com/docs/13.x/sanctum#testing)
