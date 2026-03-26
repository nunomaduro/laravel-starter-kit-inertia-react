---
title: Feature Test Structure with Arrange/Act/Assert
impact: CRITICAL
impactDescription: Produces readable, maintainable, and deterministic tests
tags: http, feature-test, arrange-act-assert, pest, structure
---

## Feature Test Structure with Arrange/Act/Assert

**Impact: CRITICAL (Produces readable, maintainable, and deterministic tests)**

Structure every feature test with three clear phases: Arrange (set up state with factories), Act (make the HTTP request), Assert (verify the response and side effects). Test behavior and outcomes — not implementation details.

## Bad Example

```php
<?php

// No factory — raw DB insert is fragile and bypasses model logic
test('create post', function () {
    DB::table('users')->insert(['name' => 'Alice', 'email' => 'a@a.com', 'password' => 'pass']);
    DB::table('posts')->insert(['title' => 'Test', 'user_id' => 1]);

    // Asserts internal implementation detail (method was called)
    $mock = Mockery::mock(PostRepository::class);
    $mock->shouldReceive('save')->once();
    app()->instance(PostRepository::class, $mock);

    $this->post('/posts', ['title' => 'Test']);
});
```

## Good Example — Pest

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create a post', function () {
    // Arrange — factories handle all setup
    $user = User::factory()->create();

    // Act — one HTTP call
    $response = $this->actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Hello World',
            'body'  => 'Some content.',
        ]);

    // Assert — behavior and outcomes, not internals
    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Hello World');

    $this->assertDatabaseHas('posts', [
        'title'   => 'Hello World',
        'user_id' => $user->id,
    ]);
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_post(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'title' => 'Hello World',
                'body'  => 'Some content.',
            ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Hello World');

        $this->assertDatabaseHas('posts', [
            'title'   => 'Hello World',
            'user_id' => $user->id,
        ]);
    }
}
```

**Testing a policy-based action (Pest):**

```php
<?php

uses(RefreshDatabase::class);

test('user cannot delete another users post', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post  = Post::factory()->for($owner)->create();

    $this->actingAs($other)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertForbidden();

    $this->assertModelExists($post);
});
```

**Testing a policy-based action (PHPUnit):**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_delete_another_users_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post  = Post::factory()->for($owner)->create();

        $this->actingAs($other)
            ->deleteJson("/api/posts/{$post->id}")
            ->assertForbidden();

        $this->assertModelExists($post);
    }
}
```

## Why It Matters

- **Readability**: Clear phases make intent obvious to every team member
- **Deterministic**: Factories + RefreshDatabase eliminate test pollution
- **Behavioral focus**: Testing outcomes rather than internals means refactoring is safe
- **Maintainability**: Changing implementation never breaks tests that only verify behavior

Reference: [Laravel HTTP Tests](https://laravel.com/docs/13.x/http-tests)
