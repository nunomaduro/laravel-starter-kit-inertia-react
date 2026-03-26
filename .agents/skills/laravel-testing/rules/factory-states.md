---
title: Factory States for Test Scenarios
impact: HIGH
impactDescription: Eliminates repetitive attribute overrides and makes test scenarios self-documenting
tags: factory, states, scenarios, pest, PHP 8.3
---

## Factory States for Test Scenarios

**Impact: HIGH (Eliminates repetitive attribute overrides and makes test scenarios self-documenting)**

Define named states on factories for distinct model configurations. States make tests readable by naming the scenario rather than spelling out every attribute override. They also centralise scenario logic so changes propagate automatically.

## Bad Example

```php
<?php

// Attribute overrides scattered across every test — not reusable
test('suspended user cannot login', function () {
    $user = User::factory()->create([
        'status'       => 'suspended',
        'suspended_at' => now(),
        'suspended_by' => 1,
    ]);
    // ...
});

test('suspended user cannot post', function () {
    $user = User::factory()->create([
        'status'       => 'suspended',
        'suspended_at' => now(),
        'suspended_by' => 1,
    ]);
    // ...
});
// If 'suspended_by' is renamed, every test must be updated manually
```

## Good Example

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),
            'status'            => 'active',
        ];
    }

    // State: unverified email
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    // State: suspended account
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    // State: admin role
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
```

**Using states in tests:**

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('suspended user cannot login', function () {
    $user = User::factory()->suspended()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');
});

test('unverified user cannot access dashboard', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/email/verify');
});

// Chain multiple states
test('suspended admin is also locked out', function () {
    $user = User::factory()->admin()->suspended()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});
```

## Why It Matters

- **DRY**: Scenario logic is defined once in the factory — not copy-pasted per test
- **Self-documenting**: `User::factory()->suspended()->create()` reads like plain English
- **Resilient**: Renaming a database column only requires updating the factory state
- **Composable**: Multiple states can be chained to build complex scenarios

Reference: [Laravel Eloquent Factories — Factory States](https://laravel.com/docs/13.x/eloquent-factories#factory-states)
