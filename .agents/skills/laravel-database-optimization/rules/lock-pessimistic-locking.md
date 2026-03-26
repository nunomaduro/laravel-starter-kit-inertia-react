---
title: Use lockForUpdate for Concurrent Writes
impact: HIGH
impactDescription: "Prevents race conditions — serializes access to rows under concurrent modification"
tags: locking, pessimistic, race-condition, transactions
---

## Use lockForUpdate for Concurrent Writes

**Impact: HIGH (Prevents race conditions — serializes access to rows under concurrent modification)**

When two requests read a row, compute a new value, and write it back, they can overwrite each other's changes. Pessimistic locking with `lockForUpdate()` acquires an exclusive lock on the selected rows, forcing concurrent transactions to wait their turn instead of racing.

## Incorrect

```php
// ❌ Race condition — two concurrent requests can both read the same balance
// Request A reads balance: 1000
$account = Account::find($id);

// Request B reads balance: 1000 (same value — A hasn't written yet)
// Both calculate: 1000 - 200 = 800

$account->update(['balance' => $account->balance - $amount]);

// Both write 800 — the account should be 600 but $200 was lost
// This is a classic "lost update" race condition
```

**Problems:**
- Two concurrent reads see the same stale value and both overwrite with their own calculation
- Money, inventory, or credits can be silently lost or duplicated
- The bug is intermittent — it only manifests under concurrent load, making it hard to reproduce

## Correct

```php
// ✅ lockForUpdate() inside a transaction — serializes concurrent access
DB::transaction(function () use ($id, $amount): void {
    // SELECT * FROM accounts WHERE id = ? FOR UPDATE
    // This blocks other transactions from reading or writing this row
    $account = Account::lockForUpdate()->find($id);

    if ($account->balance < $amount) {
        throw new InsufficientFundsException();
    }

    $account->decrement('balance', $amount);
}, attempts: 3);

// ✅ Balance transfer between two accounts with pessimistic locking
DB::transaction(function () use ($senderId, $receiverId, $amount): void {
    // Lock both accounts in consistent order to prevent deadlocks
    $accounts = Account::whereIn('id', [$senderId, $receiverId])
        ->lockForUpdate()
        ->orderBy('id')
        ->get()
        ->keyBy('id');

    $sender = $accounts[$senderId];
    $receiver = $accounts[$receiverId];

    if ($sender->balance < $amount) {
        throw new InsufficientFundsException();
    }

    $sender->decrement('balance', $amount);
    $receiver->increment('balance', $amount);
}, attempts: 3);

// ✅ Use sharedLock() when you only need to prevent writes, not reads
$account = Account::sharedLock()->find($id);
// SELECT * FROM accounts WHERE id = ? LOCK IN SHARE MODE
```

**Benefits:**
- Eliminates lost updates by serializing access to contested rows
- `lockForUpdate()` blocks other transactions until the current one commits or rolls back
- Combined with `attempts: 3`, handles transient deadlocks gracefully

Reference: [Laravel Pessimistic Locking](https://laravel.com/docs/13.x/queries#pessimistic-locking)
