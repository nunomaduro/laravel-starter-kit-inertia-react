---
title: Authentication Testing with actingAs()
impact: HIGH
impactDescription: Enables correct testing of authenticated and role-restricted routes
tags: authentication, actingAs, session, guard, policy, pest
---

## Authentication Testing with actingAs()

**Impact: HIGH (Enables correct testing of authenticated and role-restricted routes)**

Use `actingAs($user)` to authenticate a user for the duration of a test. This sets session state correctly so middleware, policies, and gates evaluate as expected. Always test both authenticated and unauthenticated paths for protected routes.

## Bad Example

```php
<?php

// Manually sets session — bypasses auth middleware, incorrect for feature tests
test('user can access dashboard', function () {
    $user = User::factory()->create();

    session(['user_id' => $user->id]); // bypasses Laravel auth system
    $this->get('/dashboard')->assertOk();
});

// Forgets to test the unauthenticated case
test('posts can be created', function () {
    $this->postJson('/api/posts', ['title' => 'Hello'])
        ->assertStatus(201); // passes only if route has no auth middleware
});
```

## Good Example

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Authenticated request
test('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

// Always test the unauthenticated path
test('guest is redirected from dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});

// Test with specific guard (e.g., admin guard)
test('admin can access admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'web')
        ->get('/admin')
        ->assertOk();
});

// Test policy — owner can edit, others cannot
test('user can edit their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->patchJson("/api/posts/{$post->id}", ['title' => 'Updated'])
        ->assertOk();
});

test('user cannot edit another users post', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post  = Post::factory()->for($owner)->create();

    $this->actingAs($other)
        ->patchJson("/api/posts/{$post->id}", ['title' => 'Hijacked'])
        ->assertForbidden();
});

// Combine actingAs with withSession for additional state
test('authenticated user with session flash can see message', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['status' => 'verified'])
        ->get('/dashboard')
        ->assertOk()
        ->assertSessionHas('status', 'verified');
});
```

## Why It Matters

- **Correct auth flow**: `actingAs` uses real Laravel auth — middleware, gates, and policies evaluate properly
- **Comprehensive**: Test both authenticated and guest paths to catch missing middleware
- **Policy testing**: The only reliable way to test policies is through full HTTP requests with `actingAs`

Reference: [Laravel HTTP Tests — Authentication](https://laravel.com/docs/13.x/http-tests#authentication)
