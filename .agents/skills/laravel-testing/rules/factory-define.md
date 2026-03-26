---
title: Define Factories with Typed Fake Data
impact: CRITICAL
impactDescription: Provides clean, realistic test data without manual setup
tags: factory, faker, definition, typed, PHP 8.3, pest
---

## Define Factories with Typed Fake Data

**Impact: CRITICAL (Provides clean, realistic test data without manual setup)**

Define model factories using Faker to generate realistic typed attributes. Use PHP 8.3 typed properties in the factory class. Every model that appears in tests should have a factory — raw `DB::table()->insert()` calls are fragile, bypass model events and casts, and duplicate schema knowledge across tests.

## Bad Example

```php
<?php

// Raw DB insert — bypasses model events, casts, and observers
test('user profile is created', function () {
    DB::table('users')->insert([
        'name'       => 'Test User',
        'email'      => 'test@test.com',
        'password'   => 'password',  // plaintext — not hashed
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::first();
    expect($user->profile)->not->toBeNull(); // profile observer never fired
});

// Factory without types and with hardcoded values
class UserFactory extends Factory
{
    public function definition()  // missing return type
    {
        return [
            'name'  => 'John',      // hardcoded — collisions in tests
            'email' => 'john@a.com',// always the same — unique constraint fails
        ];
    }
}
```

## Good Example

```php
<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
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
            'password'          => Hash::make('password'), // consistent test password
            'remember_token'    => Str::random(10),
            'role'              => UserRole::Member,
        ];
    }
}
```

**Using the factory in tests:**

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Create a persisted record
test('user has a profile', function () {
    $user = User::factory()->create();
    expect($user->profile)->not->toBeNull();
});

// Make without persisting (unit tests, no DB needed)
test('user full name is formatted', function () {
    $user = User::factory()->make(['name' => 'Alice Smith']);
    expect($user->full_name)->toBe('Alice Smith');
});

// Create multiple records
test('index returns all users', function () {
    User::factory()->count(5)->create();

    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(5, 'data');
});
```

## Why It Matters

- **Uniqueness**: `fake()->unique()->safeEmail()` prevents constraint violations across test runs
- **Model events fire**: `create()` goes through Eloquent — observers, casts, and booted traits work
- **Single source of truth**: Factory definition lives once — changes propagate everywhere
- **Realistic data**: Faker generates varied, realistic values that expose edge cases raw data misses

Reference: [Laravel Eloquent Factories — Defining Factories](https://laravel.com/docs/13.x/eloquent-factories#defining-model-factories)
