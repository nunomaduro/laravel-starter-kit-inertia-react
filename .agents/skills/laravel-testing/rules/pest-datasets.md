---
title: Parameterised Testing — Datasets (Pest) or DataProvider (PHPUnit)
impact: MEDIUM
impactDescription: Replaces repeated tests with a single parameterised test, improving coverage without duplication
tags: pest, phpunit, datasets, dataProvider, parameterised, with, data-driven
---

## Parameterised Testing — Datasets (Pest) or DataProvider (PHPUnit)

**Impact: MEDIUM (Replaces repeated tests with a single parameterised test, improving coverage without duplication)**

**Pest:** Use `->with()` to supply datasets. Pest runs the test once per entry, injecting values as function arguments.

**PHPUnit:** Use `#[DataProvider]` attribute pointing to a static method that returns arrays of arguments. Note: the `@dataProvider` annotation was removed in PHPUnit 12 — use the attribute instead.

## Bad Example

```php
<?php

// Duplicate tests — only the input differs (bad in both frameworks)
test('title cannot be empty', function () {
    $this->postJson('/api/posts', ['title' => ''])
        ->assertUnprocessable();
});

test('title cannot be null', function () {
    $this->postJson('/api/posts', ['title' => null])
        ->assertUnprocessable();
});

test('title must not exceed 255 characters', function () {
    $this->postJson('/api/posts', ['title' => str_repeat('a', 256)])
        ->assertUnprocessable();
});
// Three identical tests — one parameterised test handles all three
```

## Good Example — Pest

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Inline dataset with named cases
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
    'integer'              => [123],
]);

// Multiple arguments
it('validates login credentials', function (string $email, string $password, string $field) {
    $this->postJson('/api/login', compact('email', 'password'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);
})->with([
    'invalid email'  => ['not-an-email', 'secret123', 'email'],
    'empty password' => ['valid@example.com', '', 'password'],
]);

// Named dataset — reusable across multiple tests
dataset('invalid_statuses', [
    'draft'    => ['draft'],
    'archived' => ['archived'],
    'banned'   => ['banned'],
]);

it('rejects invalid post status on update', function (string $status) {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)
        ->patchJson("/api/posts/{$post->id}", ['status' => $status])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
})->with('invalid_statuses');
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostValidationTest extends TestCase
{
    use RefreshDatabase;

    // DataProvider static method — returns array of [label => [args...]]
    public static function invalidTitles(): array
    {
        return [
            'empty string'         => [''],
            'null value'           => [null],
            'too long (256 chars)' => [str_repeat('a', 256)],
            'integer'              => [123],
        ];
    }

    #[DataProvider('invalidTitles')]
    public function test_rejects_invalid_post_titles(mixed $title): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/posts', ['title' => $title, 'body' => 'Content'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    // Multiple arguments
    public static function invalidCredentials(): array
    {
        return [
            'invalid email'  => ['not-an-email', 'secret123', 'email'],
            'empty password' => ['valid@example.com', '', 'password'],
        ];
    }

    #[DataProvider('invalidCredentials')]
    public function test_validates_login_credentials(
        string $email,
        string $password,
        string $errorField
    ): void {
        $this->postJson('/api/login', compact('email', 'password'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$errorField]);
    }
}
```

## Why It Matters

- **DRY**: One test body covers many input variations — no copy-paste duplication
- **Coverage**: Add cases by appending to the dataset/provider — no new test function needed
- **Clear output**: Both Pest and PHPUnit label each run with the dataset key, making failures locatable

Reference: [Pest PHP — Datasets](https://pestphp.com/docs/datasets) · [PHPUnit — Data Providers](https://docs.phpunit.de/en/12.0/writing-tests-for-phpunit.html#data-providers)
