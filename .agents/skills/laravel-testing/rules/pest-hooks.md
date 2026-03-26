---
title: Lifecycle Hooks — beforeEach/afterEach (Pest) or setUp/tearDown (PHPUnit)
impact: MEDIUM
impactDescription: Eliminates repeated setup code and applies traits cleanly across test files
tags: pest, phpunit, beforeEach, afterEach, setUp, tearDown, uses, hooks, setup
---

## Lifecycle Hooks — beforeEach/afterEach (Pest) or setUp/tearDown (PHPUnit)

**Impact: MEDIUM (Eliminates repeated setup code and applies traits cleanly across test files)**

**Pest:** Use `beforeEach()` for per-test setup and `afterEach()` for teardown. Use `uses()` at the top of the file — or globally in `Pest.php` — to apply traits without repeating them.

**PHPUnit:** Use `setUp()` for per-test setup and `tearDown()` for teardown. Apply traits with `use` inside the class. Share base setup through a common `TestCase` subclass.

## Bad Example

```php
<?php

// Repeated setup in every test — verbose and fragile (bad in both frameworks)
test('user can view posts', function () {
    $user  = User::factory()->create(); // duplicated
    Mail::fake();                        // duplicated

    $this->actingAs($user)->getJson('/api/posts')->assertOk();
});

test('user can create a post', function () {
    $user = User::factory()->create(); // duplicated
    Mail::fake();                       // duplicated

    $this->actingAs($user)->postJson('/api/posts', ['title' => 'Hi'])->assertStatus(201);
});
```

## Good Example — Pest

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Mail::fake();
});

test('user can view posts', function () {
    Post::factory()->for($this->user)->count(3)->create();

    $this->actingAs($this->user)
        ->getJson('/api/posts')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('user can create a post', function () {
    $this->actingAs($this->user)
        ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
        ->assertStatus(201);
});

test('user can delete their post', function () {
    $post = Post::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertNoContent();

    $this->assertModelMissing($post);
});
```

**Apply traits globally in `tests/Pest.php`:**

```php
<?php

// tests/Pest.php — apply RefreshDatabase to all Feature tests globally
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

uses(Tests\TestCase::class)->in('Unit');
```

**`beforeEach` inside a `describe` block:**

```php
<?php

uses(RefreshDatabase::class);

describe('admin routes', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can list all users', function () {
        User::factory()->count(5)->create();

        $this->actingAs($this->admin)
            ->getJson('/api/admin/users')
            ->assertOk();
    });

    it('can delete any user', function () {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->deleteJson("/api/admin/users/{$user->id}")
            ->assertNoContent();
    });
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp(); // always call parent first

        $this->user = User::factory()->create();
        Mail::fake();
    }

    public function test_user_can_view_posts(): void
    {
        Post::factory()->for($this->user)->count(3)->create();

        $this->actingAs($this->user)
            ->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_a_post(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
            ->assertStatus(201);
    }

    public function test_user_can_delete_their_post(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->deleteJson("/api/posts/{$post->id}")
            ->assertNoContent();

        $this->assertModelMissing($post);
    }
}
```

**Shared base class for common setup (PHPUnit):**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Base class — apply once, inherit everywhere
abstract class AuthenticatedTestCase extends TestCase
{
    use RefreshDatabase;

    protected \App\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user);
    }
}

// Concrete test class — inherits user + auth
class PostControllerTest extends AuthenticatedTestCase
{
    public function test_user_can_create_a_post(): void
    {
        $this->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
            ->assertStatus(201);
    }
}
```

## Key Differences

| | Pest | PHPUnit |
|--|------|---------|
| Before each test | `beforeEach(fn() => ...)` | `protected function setUp(): void` |
| After each test | `afterEach(fn() => ...)` | `protected function tearDown(): void` |
| Global setup | `uses(...)->in('Feature')` in `Pest.php` | Extend a base `TestCase` class |
| Trait application | `uses(RefreshDatabase::class)` | `use RefreshDatabase;` in class body |
| Accessing state in tests | `$this->property` | `$this->property` |

## Why It Matters

- **DRY**: Shared setup belongs in `beforeEach`/`setUp`, not copy-pasted in every test
- **Consistency**: All tests in the block/class start from the same known state
- **Portability**: Logic is identical — only the wrapper syntax differs between frameworks

Reference: [Pest PHP — Setup & Teardown](https://pestphp.com/docs/writing-tests#setup-and-teardown) · [PHPUnit — setUp/tearDown](https://docs.phpunit.de/en/12.0/fixtures.html)
