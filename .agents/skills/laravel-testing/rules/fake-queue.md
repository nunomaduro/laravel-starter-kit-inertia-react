---
title: Faking Queues with Queue::fake()
impact: HIGH
impactDescription: Prevents real job execution in tests and enables assertion on dispatch behaviour
tags: faking, queue, jobs, Queue::fake, assertPushed, assertNothingPushed, pest
---

## Faking Queues with Queue::fake()

**Impact: HIGH (Prevents real job execution in tests and enables assertion on dispatch behaviour)**

Call `Queue::fake()` before code that dispatches jobs. The fake records dispatches without executing them, making tests fast and deterministic. Assert that jobs were dispatched with the right data, to the right queue, the right number of times.

## Bad Example

```php
<?php

// No Queue::fake() — job runs synchronously and creates real side effects
test('placing order dispatches shipment job', function () {
    $order = Order::factory()->create();

    $this->postJson('/api/orders', ['product_id' => 1])
        ->assertStatus(201);
    // ShipOrder job runs immediately — unpredictable in tests
});

// Checks only HTTP, not job dispatch
test('bulk import queues a job', function () {
    $this->postJson('/api/imports', ['file' => 'data.csv'])
        ->assertAccepted();
    // No assertion that a job was pushed
});
```

## Good Example

```php
<?php

use App\Jobs\ShipOrder;
use App\Jobs\SendInvoice;
use App\Jobs\ProcessImport;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Assert job was pushed
test('placing an order dispatches the ship order job', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/orders', ['product_id' => 1])
        ->assertStatus(201);

    Queue::assertPushed(ShipOrder::class);
});

// Assert pushed to specific queue
test('invoice job is dispatched to billing queue', function () {
    Queue::fake();

    $order = Order::factory()->create();
    $order->generateInvoice();

    Queue::assertPushedOn('billing', SendInvoice::class);
});

// Assert nothing was pushed for safe operations
test('viewing an order does not dispatch any jobs', function () {
    Queue::fake();

    $order = Order::factory()->create();

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk();

    Queue::assertNothingPushed();
});

// Assert pushed with closure — inspect job properties
test('ship order job contains correct order id', function () {
    Queue::fake();

    $order = Order::factory()->create();
    $order->ship();

    Queue::assertPushed(ShipOrder::class, fn (ShipOrder $job) =>
        $job->order->id === $order->id
    );
});

// Assert pushed count
test('importing 5 rows dispatches 5 jobs', function () {
    Queue::fake();

    $this->postJson('/api/imports', ['rows' => array_fill(0, 5, ['name' => 'Item'])])
        ->assertAccepted();

    Queue::assertPushedTimes(ProcessImport::class, 5);
});

// Fake only specific jobs — let others execute normally
test('notification job is faked but audit job runs', function () {
    Queue::fake([SendInvoice::class]);

    $order = Order::factory()->create();
    $order->complete();

    Queue::assertPushed(SendInvoice::class); // faked
    // AuditLog job ran normally
});
```

## Key Assertions

| Method | Description |
|--------|-------------|
| `Queue::assertPushed(Job::class)` | Job was dispatched |
| `Queue::assertPushed(Job::class, $closure)` | Dispatched and closure returns true |
| `Queue::assertPushedOn($queue, Job::class)` | Job pushed to specific queue |
| `Queue::assertPushedTimes(Job::class, $n)` | Dispatched exactly N times |
| `Queue::assertNotPushed(Job::class)` | Job was not dispatched |
| `Queue::assertNothingPushed()` | No jobs pushed at all |
| `Queue::assertCount($n)` | Total jobs pushed equals N |

## Why It Matters

- **No side effects**: Jobs don't execute — no emails, no DB writes, no external API calls
- **Fast**: No queue worker needed — tests run synchronously at full speed
- **Dispatch verified**: Confirms the controller/service dispatches jobs at the right time

Reference: [Laravel Mocking — Queue Fake](https://laravel.com/docs/13.x/mocking#queue-fake)
