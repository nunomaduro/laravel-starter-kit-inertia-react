---
name: laravel-testing
description: Laravel 13 testing with Pest PHP 4 or PHPUnit 12. Use when writing feature tests, unit tests, or any test code in a Laravel application. Triggers on tasks involving HTTP tests, model factories, database assertions, mocking facades, authentication testing, or test organisation patterns.
license: MIT
metadata:
  author: Laravel Community
  version: "1.1.0"
  laravelVersion: "13.x"
  phpVersion: "8.3+"
  pestVersion: "4.x"
  phpunitVersion: "12.x"
---

# Laravel 13 Testing — Pest PHP 4 & PHPUnit 12

> **Supports both Pest PHP 4 and PHPUnit 12.** See Framework Detection below.
>
> **PHPUnit version note:** Laravel 13 ships with `phpunit/phpunit: ^12.5.12` in its default `composer.json`. All patterns in this skill are compatible with PHPUnit 11, 12, and 13.

Comprehensive testing guide for Laravel 13 applications. Contains 24 rules across 6 categories for writing fast, readable, and reliable tests. Supports both **Pest PHP 4** and **PHPUnit 12** (Laravel 13 default).

## Framework Detection

**Before writing or reviewing any test code, detect which testing framework the project uses:**

### Step 1 — Check composer.json

```bash
# Look for these in require-dev:
# "pestphp/pest" → Pest
# "phpunit/phpunit" (without pest) → PHPUnit
```

- If `pestphp/pest` is present → **use Pest syntax**
- If only `phpunit/phpunit` is present → **use PHPUnit syntax**
- If both are present → **Pest takes priority** (Pest runs on top of PHPUnit)

### Step 2 — Check for tests/Pest.php

- If `tests/Pest.php` exists → **Pest is configured**, use Pest syntax

### Step 3 — If still unclear, ask the user

> "I couldn't detect the testing framework. Does this project use **Pest PHP** or **PHPUnit**?"

---

## Syntax Reference

### Test Declaration

| | Pest | PHPUnit |
|--|------|---------|
| Test function | `test('...', fn() => ...)` | `public function test_...(): void` |
| Readable name | `it('...', fn() => ...)` | `#[Test] public function it_...()` |
| Grouping | `describe('...', fn() => ...)` | Test class name / nested classes |
| Trait application | `uses(RefreshDatabase::class)` | `use RefreshDatabase;` inside class |
| Before each | `beforeEach(fn() => ...)` | `protected function setUp(): void` |
| After each | `afterEach(fn() => ...)` | `protected function tearDown(): void` |
| Parameterised | `->with([...])` | `#[DataProvider]` attribute |
| Global setup | `uses(...)->in('Feature')` in `Pest.php` | Base `TestCase` class |

### Core assertions (identical in both frameworks)

`assertStatus`, `assertJson`, `assertJsonPath`, `assertDatabaseHas`, `assertModelExists`, `actingAs`, `Mail::fake()`, `Queue::fake()`, `Event::fake()`, `Notification::fake()`, `Storage::fake()` — all work the same in Pest and PHPUnit.

---

## When to Apply

Reference these guidelines when:
- Writing feature or unit tests for Laravel
- Testing HTTP endpoints and API responses
- Creating factories and test data
- Asserting database state after operations
- Faking Mail, Queue, Notification, or Event facades
- Testing authenticated routes and API tokens
- Organising tests with describe blocks, datasets, or test classes

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | HTTP & Feature Tests | CRITICAL | `http-` |
| 2 | Model Factories | CRITICAL | `factory-` |
| 3 | Database Assertions | HIGH | `db-` |
| 4 | Faking Services | HIGH | `fake-` |
| 5 | Authentication Testing | HIGH | `auth-` |
| 6 | Test Organisation Patterns | MEDIUM | `pest-` |

## Quick Reference

### 1. HTTP & Feature Tests (CRITICAL)

- `http-test-structure` - Arrange/Act/Assert with factories — Pest + PHPUnit examples
- `http-assert-response` - assertStatus, assertJson, assertRedirect, assertJsonMissing
- `http-assert-json-fluent` - Fluent assertJson with AssertableJson closure
- `http-refresh-database` - RefreshDatabase vs DatabaseTransactions — when to use each

### 2. Model Factories (CRITICAL)

- `factory-define` - Define factories with typed fake data and PHP 8.3 syntax
- `factory-states` - Factory states for distinct test scenarios
- `factory-sequences` - sequence() for varied data across multiple records
- `factory-relationships` - has(), for(), recycle(), afterCreating()

### 3. Database Assertions (HIGH)

- `db-assert-has` - assertDatabaseHas, assertModelExists for presence checks
- `db-assert-missing` - assertDatabaseMissing, assertModelMissing for deletion
- `db-assert-soft-deletes` - assertSoftDeleted, trashed() factory state

### 4. Faking Services (HIGH)

- `fake-mail` - Mail::fake(), assertSent vs assertQueued, assertNothingSent
- `fake-queue` - Queue::fake(), assertPushed, assertPushedOn
- `fake-notification` - Notification::fake(), assertSentTo, assertCount
- `fake-event` - Event::fake(), assertDispatched, assertNotDispatched
- `fake-storage` - Storage::fake(), UploadedFile::fake(), assertExists
- `fake-ai-agent` - Agent::fake(), assertPrompted, preventStrayPrompts (Laravel 13+)
- `fake-ai-media` - Image::fake(), Audio::fake(), Transcription::fake() (Laravel 13+)
- `fake-ai-data` - Embeddings::fake(), Reranking::fake(), Files::fake(), Stores::fake() (Laravel 13+)

### 5. Authentication Testing (HIGH)

- `auth-acting-as` - actingAs() for session/web authenticated tests
- `auth-sanctum` - Sanctum::actingAs() for API token authentication

### 6. Test Organisation Patterns (MEDIUM)

- `pest-describe-it` - describe()/it() (Pest) or test class organisation (PHPUnit)
- `pest-datasets` - with() datasets (Pest) or #[DataProvider] (PHPUnit)
- `pest-hooks` - beforeEach/afterEach (Pest) or setUp/tearDown (PHPUnit)

## Essential Patterns

### Pest

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create a post', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/posts', ['title' => 'Hello World', 'body' => 'Content.'])
        ->assertStatus(201)
        ->assertJsonPath('data.title', 'Hello World');

    $this->assertDatabaseHas('posts', ['title' => 'Hello World', 'user_id' => $user->id]);
});
```

### PHPUnit

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/posts', ['title' => 'Hello World', 'body' => 'Content.'])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Hello World');

        $this->assertDatabaseHas('posts', ['title' => 'Hello World', 'user_id' => $user->id]);
    }
}
```

## How to Use

Read individual rule files for detailed explanations and code examples.

Each rule file contains:
- YAML frontmatter with metadata (title, impact, tags)
- Brief explanation of why it matters
- Bad Example with explanation
- Good Example with both Pest and PHPUnit where syntax differs
- Laravel 13 specific context and references

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
