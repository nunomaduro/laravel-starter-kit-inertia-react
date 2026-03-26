---
title: Faking Notifications with Notification::fake()
impact: HIGH
impactDescription: Prevents real notifications from being sent and enables assertion on notification behaviour
tags: faking, notification, Notification::fake, assertSentTo, pest
---

## Faking Notifications with Notification::fake()

**Impact: HIGH (Prevents real notifications from being sent and enables assertion on notification behaviour)**

Call `Notification::fake()` before any code that triggers notifications. This stops real SMS, email, Slack, and database notifications from being sent. Assert that the right notification was sent to the right user with the correct data.

## Bad Example

```php
<?php

// Does not fake — sends real push/SMS/email notifications in tests
test('order shipped notification is sent', function () {
    $user  = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $order->ship();
    // No Notification assertion — unknown if notification fired
});

// Only checks HTTP status, ignores notification side effect
test('resetting password sends a notification', function () {
    $user = User::factory()->create();

    $this->postJson('/api/forgot-password', ['email' => $user->email])
        ->assertOk();
    // Real notification may have been sent or not
});
```

## Good Example

```php
<?php

use App\Notifications\OrderShipped;
use App\Notifications\PasswordResetLink;
use App\Notifications\WeeklyDigest;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Assert notification sent to a specific user
test('user is notified when their order ships', function () {
    Notification::fake();

    $user  = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $order->ship();

    Notification::assertSentTo($user, OrderShipped::class);
});

// Assert sent with closure — inspect notification data
test('order shipped notification contains order number', function () {
    Notification::fake();

    $user  = User::factory()->create();
    $order = Order::factory()->for($user)->create(['number' => 'ORD-001']);

    $order->ship();

    Notification::assertSentTo(
        $user,
        OrderShipped::class,
        fn (OrderShipped $notification) =>
            $notification->order->number === 'ORD-001'
    );
});

// Assert nothing was sent for non-triggering operations
test('viewing an order does not send a notification', function () {
    Notification::fake();

    $user  = User::factory()->create();
    $order = Order::factory()->for($user)->create();

    $this->actingAs($user)
        ->getJson("/api/orders/{$order->id}")
        ->assertOk();

    Notification::assertNothingSent();
});

// Assert notification was not sent to a user
test('other users are not notified of someone elses order', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->for($owner)->create();

    $order->ship();

    Notification::assertSentTo($owner, OrderShipped::class);
    Notification::assertNotSentTo($other, OrderShipped::class);
});

// Assert sent count
test('weekly digest is sent to all subscribers', function () {
    Notification::fake();

    User::factory()->count(5)->create(['subscribed' => true]);
    User::factory()->count(2)->create(['subscribed' => false]);

    $this->artisan('notifications:weekly-digest')->assertSuccessful();

    Notification::assertSentTimes(WeeklyDigest::class, 5);
});
```

## Key Assertions

| Method | Description |
|--------|-------------|
| `Notification::assertSentTo($user, Notification::class)` | Sent to specific notifiable |
| `Notification::assertSentTo($user, Notification::class, $closure)` | Sent and closure returns true |
| `Notification::assertNotSentTo($user, Notification::class)` | Not sent to notifiable |
| `Notification::assertNothingSent()` | No notifications sent |
| `Notification::assertSentTimes(Notification::class, $n)` | Sent exactly N times |
| `Notification::assertCount($n)` | Total notifications sent equals N |

## Why It Matters

- **No real delivery**: Prevents SMS charges, cluttered inboxes, and Slack noise during test runs
- **Targeting verified**: Confirms the right user receives the notification, not just that one was sent
- **Payload checked**: Closure assertions verify the notification carries the correct data

Reference: [Laravel Notifications — Testing](https://laravel.com/docs/13.x/notifications#testing)
