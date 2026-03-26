---
title: Faking Events with Event::fake()
impact: HIGH
impactDescription: Isolates event dispatch from listener side effects during testing
tags: faking, events, Event::fake, assertDispatched, assertNotDispatched, pest
---

## Faking Events with Event::fake()

**Impact: HIGH (Isolates event dispatch from listener side effects during testing)**

Call `Event::fake()` to prevent listeners from executing while still recording which events were dispatched. This isolates the unit under test from listener side effects. Assert that the correct events are dispatched with the right payloads.

## Bad Example

```php
<?php

// No Event::fake() — all listeners run, causing chain reactions in tests
test('publishing a post fires event', function () {
    $post = Post::factory()->create();

    $post->publish();
    // PostPublished event fired → listeners run → emails sent, jobs pushed, cache cleared
    // Test is slow and fragile due to cascading side effects
});

// Checking listener outcome instead of event dispatch
test('post is indexed after publish', function () {
    $post = Post::factory()->create();
    $post->publish();

    // Asserting on search index is testing the listener, not the publisher
    expect(SearchIndex::has($post->id))->toBeTrue();
});
```

## Good Example

```php
<?php

use App\Events\OrderShipped;
use App\Events\OrderFailedToShip;
use App\Events\PostPublished;
use App\Models\User;
use App\Models\Post;
use App\Models\Order;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Assert event was dispatched
test('shipping an order dispatches OrderShipped event', function () {
    Event::fake();

    $order = Order::factory()->create();
    $order->ship();

    Event::assertDispatched(OrderShipped::class);
});

// Assert dispatched once
test('order shipped event fires exactly once', function () {
    Event::fake();

    $order = Order::factory()->create();
    $order->ship();

    Event::assertDispatchedOnce(OrderShipped::class);
});

// Assert dispatched with closure — inspect event payload
test('order shipped event contains the correct order', function () {
    Event::fake();

    $order = Order::factory()->create(['number' => 'ORD-123']);
    $order->ship();

    Event::assertDispatched(
        OrderShipped::class,
        fn (OrderShipped $event) => $event->order->number === 'ORD-123'
    );
});

// Assert failure event is dispatched on error
test('failed shipment dispatches failure event', function () {
    Event::fake();

    $order = Order::factory()->outOfStock()->create();
    $order->ship();

    Event::assertDispatched(OrderFailedToShip::class);
    Event::assertNotDispatched(OrderShipped::class);
});

// Assert nothing dispatched for read-only operations
test('viewing a post does not dispatch any events', function () {
    Event::fake();

    $post = Post::factory()->create();

    $this->getJson("/api/posts/{$post->id}")->assertOk();

    Event::assertNothingDispatched();
});

// Fake only specific events — let others fire normally
test('only PostPublished is faked', function () {
    Event::fake([PostPublished::class]);

    $post = Post::factory()->create();
    $post->publish();

    Event::assertDispatched(PostPublished::class);
    // Other events (e.g., ModelSaved) still fire normally
});
```

## Key Assertions

| Method | Description |
|--------|-------------|
| `Event::assertDispatched(Event::class)` | Event was dispatched at least once |
| `Event::assertDispatched(Event::class, $closure)` | Dispatched and closure returns true |
| `Event::assertDispatchedOnce(Event::class)` | Dispatched exactly once |
| `Event::assertNotDispatched(Event::class)` | Event was not dispatched |
| `Event::assertNothingDispatched()` | No events dispatched |

## Why It Matters

- **Isolation**: Listeners don't run — tests stay fast and side-effect free
- **Focused**: Tests only verify that the correct event was dispatched, not what listeners do
- **Precise**: Closure assertions confirm event payload without coupling to listener logic

Reference: [Laravel Events — Testing](https://laravel.com/docs/13.x/events#testing)
