---
title: Use Expand-Contract Pattern for Zero-Downtime Migrations
impact: HIGH
impactDescription: "Prevents broken deploys — running code continues working while schema evolves"
tags: migrations, zero-downtime, deployment
---

## Use Expand-Contract Pattern for Zero-Downtime Migrations

**Impact: HIGH (Prevents broken deploys — running code continues working while schema evolves)**

Renaming or dropping a column in a single migration breaks the currently running application during deployment. Between the moment the migration runs and the new code is fully deployed, requests hitting old application servers reference columns that no longer exist, causing 500 errors.

## Incorrect

```php
// ❌ Single migration renames column — breaks running code during deploy
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('name', 'full_name');
        });
    }
};

// Timeline:
// 1. Migration runs: column renamed to full_name
// 2. Old code still references 'name' → 500 errors for every request
// 3. New code deploys minutes later → errors stop
// The gap between steps 1 and 3 causes downtime
```

**Problems:**
- Running application servers reference the old column name after the migration executes
- In multi-server deployments, the gap between migration and full code rollout can be minutes
- Rollback is destructive — renaming back may lose data written to the new column by new code

## Correct

```php
// ✅ Step 1: EXPAND — Add new column alongside old one (Deploy #1)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('full_name')->nullable()->after('name');
        });
    }
};

// In the User model — write to both columns during transition
protected static function booted(): void
{
    static::saving(function (User $user): void {
        $user->full_name = $user->full_name ?? $user->name;
    });
}

// ✅ Step 2: MIGRATE DATA — Backfill and switch code to new column (Deploy #2)
// Migration to backfill:
return new class extends Migration
{
    public function up(): void
    {
        User::whereNull('full_name')
            ->chunkById(1000, function (Collection $users): void {
                foreach ($users as $user) {
                    $user->update(['full_name' => $user->name]);
                }
            });
    }
};
// Update all code to read/write 'full_name' instead of 'name'

// ✅ Step 3: CONTRACT — Drop old column after all code uses new one (Deploy #3)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('name');
        });
    }
};
```

**Benefits:**
- Zero downtime — old and new code both work at every step of the migration
- Each deploy is independently reversible without data loss
- Safe for multi-server rolling deployments where old and new code coexist

Reference: [Laravel Migrations](https://laravel.com/docs/13.x/migrations)
