---
title: Use Transaction Retry for Deadlocks
impact: HIGH
impactDescription: "Automatically recovers from transient deadlocks instead of crashing the request"
tags: transactions, deadlock, retry
---

## Use Transaction Retry for Deadlocks

**Impact: HIGH (Automatically recovers from transient deadlocks instead of crashing the request)**

Deadlocks occur when two or more transactions hold locks that the other needs, creating a circular wait. The database resolves this by killing one transaction. Without retry logic, that killed transaction throws an exception and fails the user's request — even though a simple retry would succeed immediately.

## Incorrect

```php
// ❌ No retry — a single deadlock crashes the entire request
DB::transaction(function (): void {
    $sender = Account::where('id', $senderId)->lockForUpdate()->first();
    $receiver = Account::where('id', $receiverId)->lockForUpdate()->first();

    $sender->decrement('balance', $amount);
    $receiver->increment('balance', $amount);
});

// If another request locks these accounts in reverse order, one transaction
// is killed by the database with: "Deadlock found when trying to get lock"
// The request returns a 500 error to the user
```

**Problems:**
- A transient deadlock causes a permanent request failure with no recovery
- Deadlocks are normal under concurrent load — they are not bugs, they are expected
- Users see intermittent 500 errors that disappear on retry, undermining trust

## Correct

```php
// ✅ Second argument to DB::transaction() sets the retry count
DB::transaction(function (): void {
    $sender = Account::where('id', $senderId)->lockForUpdate()->first();
    $receiver = Account::where('id', $receiverId)->lockForUpdate()->first();

    $sender->decrement('balance', $amount);
    $receiver->increment('balance', $amount);
}, attempts: 3);

// If a deadlock occurs, Laravel automatically retries the entire closure
// up to 3 times before throwing the exception

// ✅ Consistent lock ordering to minimize deadlocks in the first place
DB::transaction(function () use ($senderId, $receiverId, $amount): void {
    // Always lock in ascending ID order to prevent circular waits
    $ids = collect([$senderId, $receiverId])->sort()->values();

    $accounts = Account::whereIn('id', $ids)
        ->lockForUpdate()
        ->orderBy('id')
        ->get()
        ->keyBy('id');

    $accounts[$senderId]->decrement('balance', $amount);
    $accounts[$receiverId]->increment('balance', $amount);
}, attempts: 3);
```

**Benefits:**
- Automatic recovery from transient deadlocks without user-facing errors
- The `attempts` parameter is built into Laravel's `DB::transaction()` — no external packages needed
- Consistent lock ordering combined with retry makes deadlocks rare and survivable

Reference: [Laravel Database Transactions](https://laravel.com/docs/13.x/database#database-transactions)
