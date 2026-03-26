---
title: Organise Tests with describe/it (Pest) or Test Classes (PHPUnit)
impact: MEDIUM
impactDescription: Groups related tests, reduces naming noise, and produces readable test output
tags: pest, phpunit, describe, it, organisation, readability, test-class
---

## Organise Tests with describe/it (Pest) or Test Classes (PHPUnit)

**Impact: MEDIUM (Groups related tests, reduces naming noise, and produces readable test output)**

**Pest:** Use `describe()` to group related tests under a named context. Use `it()` instead of `test()` when the description reads naturally with the word "it" prepended.

**PHPUnit:** Use one test class per controller/feature, with method names that describe the scenario. Group related scenarios in the same class.

## Bad Example

```php
<?php

// Flat list — repetitive prefixes, hard to scan (works in both frameworks)
test('PostController creates a post when authenticated', function () { /* ... */ });
test('PostController returns 422 when title is missing', function () { /* ... */ });
test('PostController returns 403 for other users post', function () { /* ... */ });
test('PostController deletes a post when authenticated', function () { /* ... */ });

// PHPUnit equivalent bad practice
class PostTest extends TestCase
{
    public function test_1(): void { /* ... */ }           // meaningless name
    public function testPost(): void { /* ... */ }         // too vague
    public function testPostCreation(): void { /* ... */ } // missing scenario context
}
```

## Good Example — Pest

```php
<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PostController', function () {

    describe('store', function () {

        it('creates a post when authenticated', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
                ->assertStatus(201);

            $this->assertDatabaseHas('posts', ['title' => 'Hello']);
        });

        it('returns 422 when title is missing', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->postJson('/api/posts', ['body' => 'No title'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('returns 401 for unauthenticated requests', function () {
            $this->postJson('/api/posts', ['title' => 'Hello'])
                ->assertUnauthorized();
        });
    });

    describe('destroy', function () {

        it('deletes own post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->for($user)->create();

            $this->actingAs($user)
                ->deleteJson("/api/posts/{$post->id}")
                ->assertNoContent();

            $this->assertModelMissing($post);
        });

        it('returns 403 when deleting another users post', function () {
            $owner = User::factory()->create();
            $other = User::factory()->create();
            $post  = Post::factory()->for($owner)->create();

            $this->actingAs($other)
                ->deleteJson("/api/posts/{$post->id}")
                ->assertForbidden();
        });
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
use Tests\TestCase;

class PostControllerStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_post_when_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
            ->assertStatus(201);

        $this->assertDatabaseHas('posts', ['title' => 'Hello']);
    }

    public function test_returns_422_when_title_is_missing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/posts', ['body' => 'No title'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_returns_401_for_unauthenticated_requests(): void
    {
        $this->postJson('/api/posts', ['title' => 'Hello'])
            ->assertUnauthorized();
    }
}

// Separate class for destroy — keeps each action focused
class PostControllerDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson("/api/posts/{$post->id}")
            ->assertNoContent();

        $this->assertModelMissing($post);
    }

    public function test_returns_403_when_deleting_another_users_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post  = Post::factory()->for($owner)->create();

        $this->actingAs($other)
            ->deleteJson("/api/posts/{$post->id}")
            ->assertForbidden();
    }
}
```

**Pest output:**
```
✓ PostController > store > it creates a post when authenticated
✓ PostController > store > it returns 422 when title is missing
✓ PostController > destroy > it deletes own post
```

**PHPUnit output:**
```
✓ PostControllerStoreTest::test_creates_a_post_when_authenticated
✓ PostControllerStoreTest::test_returns_422_when_title_is_missing
✓ PostControllerDestroyTest::test_deletes_own_post
```

## Why It Matters

- **Scannable output**: Grouped tests make it instant to locate failures by feature/action
- **DRY names**: Pest `describe` context eliminates prefix repetition; PHPUnit class names provide the same grouping
- **Readable**: Both approaches produce descriptive test output that reads like a specification

Reference: [Pest PHP — Writing Tests](https://pestphp.com/docs/writing-tests) · [PHPUnit — Test Classes](https://phpunit.de/documentation.html)
