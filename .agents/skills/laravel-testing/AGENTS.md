# Laravel 13 Testing — Pest PHP 4 & PHPUnit 12 — Complete Guide

**Version:** 1.1.0
**Laravel Version:** 13.x
**PHP Version:** 8.3+
**Pest Version:** 4.x | **PHPUnit Version:** 12.x (Laravel 13 default: `^12.5.12`; PHPUnit 11 and 13 also compatible)
**Organization:** Laravel Community
**Date:** March 2026

## Overview

Comprehensive testing guide for Laravel 13 applications using Pest PHP 4. Contains 24 rules across 6 categories covering HTTP feature tests, model factories, database assertions, facade faking (including AI SDK), authentication testing, and Pest-specific patterns. All examples use PHP 8.3 syntax and Laravel 13 APIs.

### Key Features

- Pest PHP 4 syntax throughout (test(), it(), describe())
- RefreshDatabase and DatabaseTransactions guidance
- Model factory states, sequences, and relationship helpers
- Full facade faking: Mail, Queue, Notification, Event, Storage
- actingAs() and Sanctum::actingAs() for authentication
- assertJson with fluent AssertableJson closures
- Parameterised testing with Pest datasets
- Lifecycle hooks: beforeEach/afterEach/uses()

## Categories

1. **HTTP & Feature Tests (CRITICAL)** — Feature test structure, HTTP assertions, and response validation
2. **Model Factories (CRITICAL)** — Creating test data with factories, states, sequences, and relationships
3. **Database Assertions (HIGH)** — Asserting database state after operations
4. **Faking Services (HIGH)** — Faking Mail, Queue, Notification, and Event facades
5. **Authentication Testing (HIGH)** — Testing authenticated routes with actingAs and Sanctum
6. **Test Organisation Patterns (MEDIUM)** — Pest (describe/it/datasets/hooks) and PHPUnit (test classes/dataProvider/setUp) patterns

## Framework Detection

Before writing or reviewing any test code, detect which framework the project uses:

1. Check `composer.json` `require-dev`:
   - `pestphp/pest` found → **use Pest syntax**
   - Only `phpunit/phpunit` → **use PHPUnit syntax**
   - Both present → **Pest takes priority** (Pest runs on PHPUnit)
2. `tests/Pest.php` exists → **Pest is configured**
3. Neither found → **ask the user**: *"Does this project use Pest PHP or PHPUnit?"*

### Syntax Comparison

| | Pest | PHPUnit |
|--|------|---------|
| Test function | `test('...', fn() => ...)` | `public function test_...(): void` |
| Readable name | `it('...', fn() => ...)` | `#[Test] public function it_...()` |
| Grouping | `describe('...', fn() => ...)` | Test class / nested class |
| Trait application | `uses(RefreshDatabase::class)` | `use RefreshDatabase;` in class |
| Before each | `beforeEach(fn() => ...)` | `protected function setUp(): void` |
| After each | `afterEach(fn() => ...)` | `protected function tearDown(): void` |
| Parameterised | `->with([...])` | `#[DataProvider]` static method |
| Global setup | `uses(...)->in('Feature')` in `Pest.php` | Extend base `TestCase` class |

> **Note:** All HTTP assertions (`assertStatus`, `assertJson`, `assertDatabaseHas`, `actingAs`, `Mail::fake()`, etc.) work identically in both frameworks — only the test wrapper syntax differs.

### References

- [Laravel 13 Testing](https://laravel.com/docs/13.x/testing)
- [Laravel HTTP Tests](https://laravel.com/docs/13.x/http-tests)
- [Laravel Database Testing](https://laravel.com/docs/13.x/database-testing)
- [Laravel Eloquent Factories](https://laravel.com/docs/13.x/eloquent-factories)
- [Laravel Mocking](https://laravel.com/docs/13.x/mocking)
- [Pest PHP Docs](https://pestphp.com/docs/writing-tests)

---

## 1. HTTP & Feature Tests (CRITICAL)

**Impact:** CRITICAL
**Description:** Feature test structure, HTTP assertions, and response validation. These patterns determine whether tests are reliable, readable, and capable of catching real regressions.

**Rules in this category:** 4

---

## Feature Test Structure with Arrange/Act/Assert

**Impact: CRITICAL (Produces readable, maintainable, and deterministic tests)**

Structure every feature test with three clear phases: Arrange (set up state with factories), Act (make the HTTP request), Assert (verify the response and side effects). Test behavior and outcomes — not implementation details.

### Bad Example

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

### Good Example

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

### Why It Matters

- **Readability**: Clear phases make intent obvious to every team member
- **Deterministic**: Factories + RefreshDatabase eliminate test pollution
- **Behavioral focus**: Testing outcomes rather than internals means refactoring is safe

---

## HTTP Response Assertions

**Impact: CRITICAL (Catches regressions in status codes, redirects, and response shape)**

Use the full range of HTTP assertion methods to verify status codes, JSON content, redirects, and validation errors. Always assert the specific status code.

### Bad Example

```php
<?php

// Only checks 200 — misses 201, 422, 403 distinctions
test('store post', function () {
    $response = $this->post('/posts', ['title' => 'x']);
    $response->assertOk(); // passes even when 201 is expected
});
```

### Good Example

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creating a post returns 201 with resource data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'title', 'body', 'created_at'],
        ]);
});

test('creating a post without title returns 422', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/posts', ['body' => 'No title'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('web form redirects after store', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', ['title' => 'Hello', 'body' => 'World'])
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success');
});

test('guest cannot create a post', function () {
    $this->postJson('/api/posts', ['title' => 'Hi'])
        ->assertUnauthorized();
});
```

### Key Assertion Methods

| Method | Status |
|--------|--------|
| `assertOk()` | 200 |
| `assertCreated()` | 201 |
| `assertNoContent()` | 204 |
| `assertUnauthorized()` | 401 |
| `assertForbidden()` | 403 |
| `assertNotFound()` | 404 |
| `assertUnprocessable()` | 422 |
| `assertJsonPath(key, value)` | Specific JSON path |
| `assertJsonValidationErrors(array)` | Validation errors |
| `assertRedirect(url)` | Redirect location |

---

## Fluent JSON Assertions with AssertableJson

**Impact: HIGH (Enables precise, readable assertions on complex JSON structures)**

Pass a closure to `assertJson()` to receive an `AssertableJson` instance for fluent, precise assertions on nested JSON.

### Bad Example

```php
<?php

// Brittle — breaks if any extra key is added
test('post list', function () {
    $posts = Post::factory()->count(3)->create();
    $this->getJson('/api/posts')
        ->assertJson($posts->toArray()); // fails if response wraps in 'data'
});
```

### Good Example

```php
<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
            ->missing('data.password') // sensitive field must never leak
        );
});

test('post index returns paginated posts', function () {
    Post::factory()->count(5)->create();

    $this->getJson('/api/posts')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 5)
            ->has('data.0', fn ($item) =>
                $item->hasAll(['id', 'title', 'created_at'])
                     ->missing('body')
            )
            ->has('meta')
            ->where('meta.total', 5)
        );
});
```

---

## RefreshDatabase vs DatabaseTransactions

**Impact: HIGH (Prevents test pollution and ensures a clean database state)**

Use `RefreshDatabase` for most feature tests. Apply it via `uses()` per file or globally in `Pest.php`.

### Good Example

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('basic feature test', function () {
    $user = User::factory()->create();
    expect(User::count())->toBe(1);
});
```

**Apply globally in `Pest.php`:**

```php
<?php

// tests/Pest.php
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');
```

---

## 2. Model Factories (CRITICAL)

**Impact:** CRITICAL
**Description:** Creating test data with factories, states, sequences, and relationships. Factories are the foundation of reliable, readable test data setup.

**Rules in this category:** 4

---

## Define Factories with Typed Fake Data

**Impact: CRITICAL (Provides clean, realistic test data without manual setup)**

Define model factories using Faker. Use PHP 8.3 typed return types. Every model used in tests must have a factory.

### Good Example

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }
}
```

**Usage:**

```php
$user  = User::factory()->create();       // persisted
$user  = User::factory()->make();         // not persisted
$users = User::factory()->count(5)->create(); // 5 records
```

---

## Factory States for Test Scenarios

**Impact: HIGH (Eliminates repetitive attribute overrides and makes test scenarios self-documenting)**

Define named states for distinct model configurations. Chain states to build complex scenarios.

### Good Example

```php
<?php

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [/* ... */];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
```

**Usage:**

```php
User::factory()->suspended()->create();
User::factory()->admin()->unverified()->create();
```

---

## Factory Sequences for Varied Records

**Impact: HIGH (Creates realistic varied datasets without repetitive factory calls)**

Use `sequence()` to assign alternating values when creating multiple records.

### Good Example

```php
<?php

Post::factory()
    ->count(4)
    ->sequence(
        ['status' => 'published'],
        ['status' => 'draft'],
        ['status' => 'published'],
        ['status' => 'archived'],
    )
    ->create();

// With index for dynamic values
Post::factory()
    ->count(3)
    ->sequence(fn (Sequence $sequence) => [
        'title'      => "Post {$sequence->index}",
        'created_at' => now()->subDays($sequence->index),
    ])
    ->create();
```

---

## Factory Relationship Helpers

**Impact: HIGH (Reduces setup boilerplate and correctly wires related models)**

Use `has()`, `for()`, `recycle()`, and `afterCreating()` to set up relationships cleanly.

### Good Example

```php
<?php

// BelongsTo
$post = Post::factory()->for($user)->create();

// HasMany
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();

// Reuse existing model — no extra records
$posts = Post::factory()
    ->count(5)
    ->recycle($user)
    ->create();

// afterCreating() in factory
public function withTags(int $count = 3): static
{
    return $this->afterCreating(function (Post $post) use ($count) {
        $tags = Tag::factory()->count($count)->create();
        $post->tags()->attach($tags);
    });
}
```

---

## 3. Database Assertions (HIGH)

**Impact:** HIGH
**Description:** Asserting database state after operations to verify persistence and deletion.

**Rules in this category:** 3

---

## assertDatabaseHas and assertModelExists

**Impact: HIGH (Verifies that operations correctly persist data to the database)**

```php
<?php

// assertDatabaseHas — verify row exists
$this->assertDatabaseHas('posts', [
    'title'   => 'Hello World',
    'user_id' => $user->id,
]);

// assertModelExists — cleaner when you have the instance
$this->assertModelExists($user);
$this->assertModelExists($user->profile);
```

---

## assertDatabaseMissing and assertModelMissing

**Impact: HIGH (Confirms that delete operations actually remove data)**

```php
<?php

// assertModelMissing — after hard delete
$this->actingAs($user)
    ->deleteJson("/api/posts/{$post->id}")
    ->assertNoContent();

$this->assertModelMissing($post);

// assertDatabaseMissing — check by attribute
$this->assertDatabaseMissing('tags', ['name' => 'laravel']);
$this->assertDatabaseMissing('post_tag', ['tag_id' => $tag->id]);
```

---

## Asserting Soft Deletes

**Impact: MEDIUM (Verifies soft-delete behaviour)**

```php
<?php

// assertSoftDeleted — row exists but deleted_at is set
$this->actingAs($user)
    ->deleteJson("/api/posts/{$post->id}")
    ->assertNoContent();

$this->assertSoftDeleted($post);

// assertNotSoftDeleted — after restore
$this->assertNotSoftDeleted($post);

// trashed() factory state — pre-soft-deleted record
$post = Post::factory()->trashed()->create();

// Verify trashed posts excluded from feed
Post::factory()->count(2)->create();
Post::factory()->trashed()->create();

$this->getJson('/api/feed')
    ->assertJsonCount(2, 'data');
```

---

## 4. Faking Services (HIGH)

**Impact:** HIGH
**Description:** Faking Mail, Queue, Notification, and Event facades to prevent real side effects and assert dispatch behaviour.

**Rules in this category:** 4

---

## Faking Mail with Mail::fake()

**Impact: HIGH (Prevents real emails from being sent and enables assertion on mail behaviour)**

```php
<?php

use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail;

test('order confirmation is emailed to buyer', function () {
    Mail::fake();

    $user = User::factory()->create();
    $this->actingAs($user)->postJson('/api/orders', ['product_id' => 1]);

    Mail::assertSent(OrderShipped::class);
    Mail::assertSent(OrderShipped::class, $user->email);
    // Mail::assertNothingSent(); — use on non-triggering actions
});

// Inspect mailable attributes
Mail::assertSent(OrderShipped::class, fn (OrderShipped $mail) =>
    $mail->order->number === 'ORD-999' &&
    $mail->hasTo($user->email)
);
```

**Key methods:** `assertSent`, `assertNotSent`, `assertNothingSent`, `assertSentTimes`, `assertSentCount`

---

## Faking Queues with Queue::fake()

**Impact: HIGH (Prevents real job execution and enables assertion on dispatch behaviour)**

```php
<?php

use App\Jobs\ShipOrder;
use Illuminate\Support\Facades\Queue;

test('placing order dispatches ship job', function () {
    Queue::fake();

    $this->actingAs($user)->postJson('/api/orders', ['product_id' => 1]);

    Queue::assertPushed(ShipOrder::class);
    Queue::assertPushedOn('billing', SendInvoice::class);
    // Queue::assertNothingPushed(); — use on read operations
});

// Inspect job properties
Queue::assertPushed(ShipOrder::class, fn (ShipOrder $job) =>
    $job->order->id === $order->id
);
```

**Key methods:** `assertPushed`, `assertPushedOn`, `assertPushedTimes`, `assertNotPushed`, `assertNothingPushed`, `assertCount`

---

## Faking Notifications with Notification::fake()

**Impact: HIGH (Prevents real notifications from being sent)**

```php
<?php

use App\Notifications\OrderShipped;
use Illuminate\Support\Facades\Notification;

test('user is notified when order ships', function () {
    Notification::fake();

    $order->ship();

    Notification::assertSentTo($user, OrderShipped::class);
    Notification::assertNotSentTo($otherUser, OrderShipped::class);
    // Notification::assertNothingSent(); — use on non-triggering actions
});

// Inspect notification payload
Notification::assertSentTo(
    $user,
    OrderShipped::class,
    fn (OrderShipped $notification) => $notification->order->number === 'ORD-001'
);
```

**Key methods:** `assertSentTo`, `assertNotSentTo`, `assertNothingSent`, `assertSentTimes`, `assertCount`

---

## Faking Events with Event::fake()

**Impact: HIGH (Isolates event dispatch from listener side effects)**

```php
<?php

use App\Events\OrderShipped;
use Illuminate\Support\Facades\Event;

test('shipping dispatches OrderShipped event', function () {
    Event::fake();

    $order->ship();

    Event::assertDispatched(OrderShipped::class);
    Event::assertDispatchedOnce(OrderShipped::class);
    // Event::assertNothingDispatched(); — use on read operations
});

// Inspect event payload
Event::assertDispatched(
    OrderShipped::class,
    fn (OrderShipped $event) => $event->order->number === 'ORD-123'
);
```

**Key methods:** `assertDispatched`, `assertDispatchedOnce`, `assertNotDispatched`, `assertNothingDispatched`

---

## Faking Storage with Storage::fake()

**Impact: HIGH (Prevents real file writes during tests and enables assertion on file upload behaviour)**

```php
<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('user can upload an avatar', function () {
    Storage::fake('avatars');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $this->actingAs($user)
        ->postJson('/api/avatar', ['avatar' => $file])
        ->assertOk();

    Storage::disk('avatars')->assertExists("users/{$user->id}/avatar.jpg");
});

// Assert file is not stored when upload is rejected
test('oversized image is rejected', function () {
    Storage::fake('avatars');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('big.jpg')->size(5001); // exceeds limit

    $this->actingAs($user)
        ->postJson('/api/avatar', ['avatar' => $file])
        ->assertUnprocessable();

    Storage::disk('avatars')->assertMissing("users/{$user->id}/big.jpg");
});
```

**UploadedFile helpers:**

```php
UploadedFile::fake()->image('photo.jpg')                        // JPEG image
UploadedFile::fake()->image('photo.png', 400, 300)              // with dimensions
UploadedFile::fake()->create('doc.pdf', 1024)                   // 1024 KB file
UploadedFile::fake()->create('doc.pdf', 1024, 'application/pdf') // with MIME
```

**Key assertions:** `assertExists($path)`, `assertMissing($path)`, `assertDirectoryEmpty($dir)`

---

## 5. Authentication Testing (HIGH)

**Impact:** HIGH
**Description:** Testing authenticated routes with actingAs() and Sanctum::actingAs().

**Rules in this category:** 2

---

## Authentication Testing with actingAs()

**Impact: HIGH (Enables correct testing of authenticated and role-restricted routes)**

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Authenticated
test('user can access dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/dashboard')->assertOk();
});

// Always test the guest path too
test('guest is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

// Policy: owner can, others cannot
test('user cannot edit another users post', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post  = Post::factory()->for($owner)->create();

    $this->actingAs($other)
        ->patchJson("/api/posts/{$post->id}", ['title' => 'Hijacked'])
        ->assertForbidden();
});
```

---

## API Authentication Testing with Sanctum

**Impact: HIGH (Enables correct testing of token-authenticated API endpoints)**

```php
<?php

use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Basic Sanctum auth
test('authenticated user can get profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/user')->assertOk();
});

// With specific abilities
test('read-only token cannot create', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['posts:read']);

    $this->postJson('/api/posts', ['title' => 'Hello'])
        ->assertForbidden();
});

// Unauthenticated
test('missing token returns 401', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});
```

---

## 6. Test Organisation Patterns (MEDIUM)

**Impact:** MEDIUM
**Description:** Pest (describe/it/datasets/beforeEach) and PHPUnit (test classes/dataProvider/setUp) patterns for readable, organised, and maintainable test files.

**Rules in this category:** 3

---

## Organise Tests with describe() and it()

**Impact: MEDIUM (Groups related tests and produces readable test output)**

```php
<?php

uses(RefreshDatabase::class);

describe('PostController', function () {

    describe('store', function () {
        it('creates a post when authenticated', function () {
            $user = User::factory()->create();
            $this->actingAs($user)
                ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
                ->assertStatus(201);
        });

        it('returns 422 when title is missing', function () {
            $user = User::factory()->create();
            $this->actingAs($user)
                ->postJson('/api/posts', ['body' => 'No title'])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['title']);
        });

        it('returns 401 for guests', function () {
            $this->postJson('/api/posts', ['title' => 'Hello'])
                ->assertUnauthorized();
        });
    });

    describe('destroy', function () {
        it('deletes own post', function () {
            $user = User::factory()->create();
            $post = Post::factory()->for($user)->create();
            $this->actingAs($user)->deleteJson("/api/posts/{$post->id}")->assertNoContent();
            $this->assertModelMissing($post);
        });

        it('returns 403 for others posts', function () {
            $owner = User::factory()->create();
            $other = User::factory()->create();
            $post  = Post::factory()->for($owner)->create();
            $this->actingAs($other)->deleteJson("/api/posts/{$post->id}")->assertForbidden();
        });
    });
});
```

---

## Parameterised Testing with Pest Datasets

**Impact: MEDIUM (Replaces repeated tests with a single parameterised test)**

```php
<?php

uses(RefreshDatabase::class);

it('rejects invalid post titles', function (mixed $title) {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->postJson('/api/posts', ['title' => $title, 'body' => 'Content'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
})->with([
    'empty string'         => [''],
    'null value'           => [null],
    'too long (256 chars)' => [str_repeat('a', 256)],
]);

// Named dataset — reusable across tests
dataset('invalid_statuses', [
    'draft'    => ['draft'],
    'archived' => ['archived'],
]);

it('rejects invalid status', function (string $status) {
    $this->patchJson('/api/posts/1', ['status' => $status])
        ->assertUnprocessable();
})->with('invalid_statuses');
```

---

## Lifecycle Hooks with beforeEach, afterEach, and uses()

**Impact: MEDIUM (Eliminates repeated setup code)**

```php
<?php

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Mail::fake();
});

test('user can create post', function () {
    $this->actingAs($this->user)
        ->postJson('/api/posts', ['title' => 'Hello', 'body' => 'World'])
        ->assertStatus(201);
});

test('user can delete post', function () {
    $post = Post::factory()->for($this->user)->create();
    $this->actingAs($this->user)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertNoContent();
    $this->assertModelMissing($post);
});
```

**Global setup in `Pest.php`:**

```php
<?php

// tests/Pest.php
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

uses(Tests\TestCase::class)->in('Unit');
```

---

title: Fake AI Agent Responses
impact: HIGH
impactDescription: Test agent interactions without real API calls
tags: testing, ai, agent, fake, assert, laravel-ai-sdk
---

## Fake AI Agent Responses

**Impact: HIGH (Test agent interactions without real API calls)**

Use `Agent::fake()` to prevent real AI provider calls in tests. Fake responses can be static strings, arrays, or dynamic closures. Assert that agents were prompted with expected inputs.

## Bad Example — Pest

```php
<?php

use App\Ai\Agents\SalesCoach;

test('coach analyzes transcript', function () {
    // Real API call — slow, expensive, non-deterministic
    $response = SalesCoach::make()->prompt('Analyze this...');

    // Different response every run, costs money, requires API key in CI
    expect((string) $response)->toContain('feedback');
});
```

## Good Example — Pest

```php
<?php

use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Prompts\AgentPrompt;

test('coach analyzes transcript', function () {
    SalesCoach::fake(['Great job on the opening.']);

    $response = SalesCoach::make()->prompt('Analyze this transcript...');

    expect((string) $response)->toBe('Great job on the opening.');

    SalesCoach::assertPrompted('Analyze this transcript...');
});

test('coach responds based on prompt content', function () {
    SalesCoach::fake(function (AgentPrompt $prompt) {
        return 'Response for: ' . $prompt->prompt;
    });

    $response = SalesCoach::make()->prompt('Q4 sales data');

    SalesCoach::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Q4'));
    SalesCoach::assertNotPrompted('Missing prompt');
});

test('no unexpected agent calls', function () {
    SalesCoach::fake()->preventStrayPrompts();

    SalesCoach::make()->prompt('Expected call');
    // Any unfaked agent call would throw
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Prompts\AgentPrompt;
use Tests\TestCase;

class SalesCoachTest extends TestCase
{
    public function test_coach_analyzes_transcript(): void
    {
        SalesCoach::fake(['Great job on the opening.']);

        $response = SalesCoach::make()->prompt('Analyze this transcript...');

        $this->assertEquals('Great job on the opening.', (string) $response);

        SalesCoach::assertPrompted('Analyze this transcript...');
    }

    public function test_no_unexpected_agent_calls(): void
    {
        SalesCoach::fake()->preventStrayPrompts();

        SalesCoach::make()->prompt('Expected call');
    }
}
```

**Structured output agents auto-generate fake data matching the schema — no manual setup needed.**

## Why It Matters

- **Fast**: No API calls — tests run in milliseconds
- **Deterministic**: Same response every run
- **Free**: No API costs in CI/CD pipelines
- **Safe**: `preventStrayPrompts()` catches accidental real calls

Reference: [Laravel AI SDK — Testing Agents](https://laravel.com/docs/13.x/ai-sdk#testing-agents)

---

title: Fake AI Image, Audio, and Transcription
impact: HIGH
impactDescription: Test media generation without real API calls
tags: testing, ai, image, audio, transcription, fake, assert, laravel-ai-sdk
---

## Fake AI Image, Audio, and Transcription

**Impact: HIGH (Test media generation without real API calls)**

Use `Image::fake()`, `Audio::fake()`, and `Transcription::fake()` to prevent real generation calls. Assert prompts, aspect ratios, voices, and diarization.

## Bad Example — Pest

```php
<?php

use Laravel\Ai\Image;

test('generates product image', function () {
    // Real API call — slow, expensive, different image every run
    $image = Image::of('Product photo')->landscape()->generate();
    expect((string) $image)->not->toBeEmpty();
});
```

## Good Example — Pest

```php
<?php

use Laravel\Ai\Audio;
use Laravel\Ai\Image;
use Laravel\Ai\Transcription;
use Laravel\Ai\Prompts\AudioPrompt;
use Laravel\Ai\Prompts\ImagePrompt;
use Laravel\Ai\Prompts\TranscriptionPrompt;

test('generates landscape product image', function () {
    Image::fake();

    Image::of('A sunset over the ocean')->landscape()->generate();

    Image::assertGenerated(function (ImagePrompt $prompt) {
        return $prompt->contains('sunset') && $prompt->isLandscape();
    });
});

test('generates welcome audio', function () {
    Audio::fake();

    Audio::of('Welcome to our app.')->female()->generate();

    Audio::assertGenerated(function (AudioPrompt $prompt) {
        return $prompt->contains('Welcome') && $prompt->isFemale();
    });
});

test('transcribes uploaded audio with diarization', function () {
    Transcription::fake(['Meeting transcript text here.']);

    $transcript = Transcription::fromStorage('meeting.mp3')
        ->diarize()
        ->generate();

    expect((string) $transcript)->toBe('Meeting transcript text here.');

    Transcription::assertGenerated(function (TranscriptionPrompt $prompt) {
        return $prompt->isDiarized();
    });
});

test('no unexpected media generation', function () {
    Image::fake()->preventStrayImages();
    Audio::fake()->preventStrayAudio();
    Transcription::fake()->preventStrayTranscriptions();
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use Laravel\Ai\Image;
use Laravel\Ai\Prompts\ImagePrompt;
use Tests\TestCase;

class ImageGenerationTest extends TestCase
{
    public function test_generates_landscape_product_image(): void
    {
        Image::fake();

        Image::of('A sunset over the ocean')->landscape()->generate();

        Image::assertGenerated(function (ImagePrompt $prompt) {
            return $prompt->contains('sunset') && $prompt->isLandscape();
        });
    }
}
```

## Why It Matters

- **Fast**: No image/audio generation — tests complete in milliseconds
- **Deterministic**: Controlled fake responses every run
- **Assertable**: Verify prompts, aspect ratios, voices, and diarization settings
- **Prevent leaks**: `preventStray*()` methods catch accidental real API calls

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)

---

title: Fake AI Embeddings, Reranking, Files, and Vector Stores
impact: HIGH
impactDescription: Test AI data operations without real API calls
tags: testing, ai, embeddings, reranking, files, vector-stores, fake, assert, laravel-ai-sdk
---

## Fake AI Embeddings, Reranking, Files, and Vector Stores

**Impact: HIGH (Test AI data operations without real API calls)**

Use `Embeddings::fake()`, `Reranking::fake()`, `Files::fake()`, and `Stores::fake()` to test data operations without provider connections.

## Bad Example — Pest

```php
<?php

use Illuminate\Support\Str;

test('stores document with embedding', function () {
    // Real API call to generate embedding — slow, costs money
    $embedding = Str::of($content)->toEmbeddings();
    Document::create(['content' => $content, 'embedding' => $embedding]);
});
```

## Good Example — Pest

```php
<?php

use Laravel\Ai\Embeddings;
use Laravel\Ai\Files;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Reranking;
use Laravel\Ai\Stores;
use Laravel\Ai\Contracts\Files\StorableFile;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Laravel\Ai\Prompts\RerankingPrompt;

test('generates document embeddings', function () {
    Embeddings::fake();

    Embeddings::for(['Laravel is great.'])->generate();

    Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
        return $prompt->contains('Laravel');
    });
});

test('reranks search results', function () {
    Reranking::fake();

    Reranking::of(['Doc A', 'Doc B'])->rerank('query');

    Reranking::assertReranked(function (RerankingPrompt $prompt) {
        return $prompt->contains('query');
    });
});

test('stores document with provider', function () {
    Files::fake();

    Document::fromString('Hello, Laravel!', mimeType: 'text/plain')
        ->as('hello.txt')
        ->put();

    Files::assertStored(fn (StorableFile $file) =>
        (string) $file === 'Hello, Laravel!'
    );
});

test('creates vector store and adds files', function () {
    Stores::fake(); // Also fakes file operations

    $store = Stores::create('Knowledge Base');
    Stores::assertCreated('Knowledge Base');

    $store->add(Document::fromString('Content', 'text/plain')->as('doc.txt'));
    $store->assertAdded(fn (StorableFile $file) => $file->name() === 'doc.txt');
});

test('no unexpected data operations', function () {
    Embeddings::fake()->preventStrayEmbeddings();
    Files::fake();
    Stores::fake();
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Tests\TestCase;

class EmbeddingTest extends TestCase
{
    public function test_generates_document_embeddings(): void
    {
        Embeddings::fake();

        Embeddings::for(['Laravel is great.'])->generate();

        Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
            return $prompt->contains('Laravel');
        });
    }
}
```

## Why It Matters

- **Fast**: No embedding API calls or provider connections
- **Deterministic**: `Embeddings::fake()` auto-generates vectors of proper dimensions
- **Comprehensive**: `Stores::fake()` also fakes file operations automatically
- **Assertable**: Verify what was generated, stored, added, and removed

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)

---

## How to Use This Guide

1. **For AI Agents**: Reference specific rules by category and rule name when generating or reviewing test code
2. **For Developers**: Use as a comprehensive reference for Laravel 13 testing best practices
3. **For Code Review**: Check test implementations against these patterns
4. **For CI/CD**: Ensure all AI SDK features are properly faked in test suites
