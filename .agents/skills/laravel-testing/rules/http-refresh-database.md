---
title: RefreshDatabase vs DatabaseTransactions
impact: HIGH
impactDescription: Prevents test pollution and ensures a clean database state between tests
tags: http, RefreshDatabase, DatabaseTransactions, database, isolation, pest
---

## RefreshDatabase vs DatabaseTransactions

**Impact: HIGH (Prevents test pollution and ensures a clean database state between tests)**

Use `RefreshDatabase` for most feature tests — it migrates a fresh schema and wraps each test in a transaction that rolls back on completion. Use `DatabaseTransactions` when you need the schema already migrated (e.g., in CI with a pre-built test database). Never share database state across tests.

## Bad Example

```php
<?php

// No database reset — tests can pollute each other
test('user count is 1 after registration', function () {
    $this->post('/register', ['name' => 'Alice', 'email' => 'a@a.com', 'password' => 'secret']);
    expect(User::count())->toBe(1); // fails if previous test left users in DB
});

// Manual teardown is fragile and forgettable
test('post is deleted', function () {
    $post = Post::factory()->create();
    $this->delete("/posts/{$post->id}");
    $post->delete(); // attempting manual cleanup — unreliable
});
```

## Good Example

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

// Apply per-file via uses() — recommended approach in Pest
uses(RefreshDatabase::class);

test('user count is 1 after registration', function () {
    $this->post('/register', [
        'name'                  => 'Alice',
        'email'                 => 'alice@example.com',
        'password'              => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    expect(User::count())->toBe(1);
});

test('deleting a post removes it from database', function () {
    $user = User::factory()->create();
    $post = Post::factory()->for($user)->create();

    $this->actingAs($user)->delete("/posts/{$post->id}");

    $this->assertModelMissing($post);
});
```

**Apply to entire test directory via `Pest.php`:**

```php
<?php

// tests/Pest.php — apply RefreshDatabase to all Feature tests globally
uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');
```

**When to use `DatabaseTransactions` instead:**

```php
<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

// Use when: schema is pre-migrated in CI and you want faster tests
// (skips migration step, just wraps in transaction)
uses(DatabaseTransactions::class);

test('order total is calculated correctly', function () {
    $order = Order::factory()->hasItems(3)->create();
    expect($order->total)->toBeGreaterThan(0);
});
```

## Comparison

| Trait | Runs Migrations | Speed | Use When |
|-------|----------------|-------|----------|
| `RefreshDatabase` | Yes (first run) | Moderate | Default for most tests |
| `DatabaseTransactions` | No | Faster | Pre-migrated CI database |
| Neither | No | Fast | No DB interaction in test |

## Critical: DatabaseTransactions and Nested Transactions

**Do not use `DatabaseTransactions` when your code under test calls `DB::transaction()`.**

When the code opens its own transaction, the outer test transaction is implicitly committed — the rollback at test teardown has no effect, leaving dirty data in the database.

```php
<?php

// Code under test:
class OrderService
{
    public function place(array $data): Order
    {
        return DB::transaction(function () use ($data) { // inner transaction
            $order = Order::create($data);
            $order->items()->create([...]);
            return $order;
        });
    }
}

// BAD — DatabaseTransactions will not clean up after a nested transaction
uses(DatabaseTransactions::class);

test('order is placed', function () {
    (new OrderService())->place([...]);
    expect(Order::count())->toBe(1); // passes but data is NOT rolled back after test
});

// GOOD — RefreshDatabase handles nested transactions correctly
uses(RefreshDatabase::class);

test('order is placed', function () {
    (new OrderService())->place([...]);
    expect(Order::count())->toBe(1); // correctly isolated
});
```

## Why It Matters

- **Isolation**: Each test starts with a clean state — no order-dependency between tests
- **Reliability**: Eliminates "passes locally, fails in CI" issues caused by leftover data
- **Simplicity**: No manual teardown needed — the framework handles cleanup automatically

Reference: [Laravel Database Testing — Resetting the Database](https://laravel.com/docs/13.x/database-testing#resetting-the-database-after-each-test)
