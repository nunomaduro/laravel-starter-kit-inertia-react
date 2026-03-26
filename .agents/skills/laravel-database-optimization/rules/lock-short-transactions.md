---
title: Keep Transactions as Short as Possible
impact: HIGH
impactDescription: "Reduces lock contention and connection hold time — prevents cascading timeouts"
tags: transactions, locks, performance, contention
---

## Keep Transactions as Short as Possible

**Impact: HIGH (Reduces lock contention and connection hold time — prevents cascading timeouts)**

Database transactions hold locks on affected rows until the transaction commits or rolls back. Including slow operations like API calls, file uploads, or queue dispatches inside a transaction extends lock duration, blocking other requests that need the same rows and potentially causing cascading timeouts.

## Incorrect

```php
// ❌ Transaction wraps external API call and job dispatch — holds locks for seconds
DB::transaction(function (): void {
    $order = Order::create([
        'user_id' => auth()->id(),
        'total' => $total,
        'status' => 'confirmed',
    ]);

    // External API call — 200ms-5s network latency while locks are held
    $payment = PaymentGateway::charge($order->total);

    $order->update(['payment_id' => $payment->id]);

    // Queue dispatch inside transaction — if queue is backed up, commit is delayed
    dispatch(new SendOrderConfirmation($order));

    // File upload inside transaction — holds locks during I/O
    Storage::put("invoices/{$order->id}.pdf", $pdf);
});
```

**Problems:**
- External API latency extends lock duration from milliseconds to seconds
- Other requests needing the same rows queue up, causing cascading timeouts
- If the API call fails, the entire transaction rolls back — but the side effects (uploaded files, dispatched jobs) cannot be undone

## Correct

```php
// ✅ Only database operations inside the transaction
$order = DB::transaction(function () use ($total): Order {
    $order = Order::create([
        'user_id' => auth()->id(),
        'total' => $total,
        'status' => 'pending',
    ]);

    OrderItem::insert($items);

    return $order;
});

// External calls and side effects AFTER the transaction commits
$payment = PaymentGateway::charge($order->total);

$order->update(['payment_id' => $payment->id, 'status' => 'confirmed']);

// ✅ Use afterCommit() to ensure side effects only run after successful commit
DB::afterCommit(function () use ($order): void {
    dispatch(new SendOrderConfirmation($order));
    Storage::put("invoices/{$order->id}.pdf", $pdf);
});

// ✅ Queue jobs with afterCommit to prevent processing before data is committed
dispatch(new SendOrderConfirmation($order))->afterCommit();
```

**Benefits:**
- Transaction holds locks only during fast database writes — milliseconds instead of seconds
- Side effects like jobs and notifications only execute after successful commit
- External service failures don't roll back valid database changes

Reference: [Laravel Database Transactions](https://laravel.com/docs/13.x/database#database-transactions)
