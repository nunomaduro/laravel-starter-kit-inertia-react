---
title: Faking Mail with Mail::fake()
impact: HIGH
impactDescription: Prevents real emails from being sent in tests and enables assertion on mail behaviour
tags: faking, mail, Mail::fake, assertSent, assertNothingSent, pest
---

## Faking Mail with Mail::fake()

**Impact: HIGH (Prevents real emails from being sent in tests and enables assertion on mail behaviour)**

Call `Mail::fake()` before any code that triggers mail. This swaps the mailer with a fake that records calls instead of sending. Always assert both that the correct mailable was sent and that nothing unexpected was sent.

## Bad Example

```php
<?php

// Does not fake — sends real emails during tests
test('order confirmation email is sent', function () {
    $order = Order::factory()->create();

    $this->postJson('/api/orders', ['product_id' => 1])
        ->assertStatus(201);
    // No Mail assertion — unknown if email was sent at all
});

// Checks response but ignores side effects
test('user registration triggers welcome email', function () {
    $this->postJson('/api/register', [
        'name'  => 'Alice',
        'email' => 'alice@example.com',
    ])->assertStatus(201);
    // No Mail::fake() — may fire real SMTP in CI
});
```

## Good Example

```php
<?php

use App\Mail\OrderShipped;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Assert a specific mailable was sent
test('order confirmation is emailed to buyer', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/orders', ['product_id' => 1])
        ->assertStatus(201);

    Mail::assertSent(OrderShipped::class);
});

// Assert sent to specific recipient
test('welcome email is sent to new user email address', function () {
    Mail::fake();

    $this->postJson('/api/register', [
        'name'                  => 'Alice',
        'email'                 => 'alice@example.com',
        'password'              => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertStatus(201);

    Mail::assertSent(WelcomeEmail::class, 'alice@example.com');
});

// Assert nothing was sent for non-triggering actions
test('updating profile does not send an email', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->patchJson('/api/profile', ['name' => 'Bob'])
        ->assertOk();

    Mail::assertNothingSent();
});

// Assert sent with closure — inspect mailable attributes
test('order email contains the correct order number', function () {
    Mail::fake();

    $user  = User::factory()->create();
    $order = Order::factory()->for($user)->create(['number' => 'ORD-999']);

    $order->ship();

    Mail::assertSent(OrderShipped::class, fn (OrderShipped $mail) =>
        $mail->order->number === 'ORD-999' &&
        $mail->hasTo($user->email)
    );
});

// Assert sent count
test('bulk action sends one email per user', function () {
    Mail::fake();

    User::factory()->count(3)->create();

    $this->postJson('/api/admin/send-newsletter')->assertOk();

    Mail::assertSentTimes(NewsletterMail::class, 3);
});
```

## assertSent vs assertQueued

If the mailable is dispatched via a queued job (`Mail::to()->queue()`), use `assertQueued` — `assertSent` will not see it:

```php
// Mailable sent immediately (Mail::to()->send())
Mail::assertSent(OrderShipped::class);

// Mailable queued for background delivery (Mail::to()->queue())
Mail::assertQueued(OrderShipped::class);

// Not sure? Assert at least one of them
Mail::assertSent(OrderShipped::class);
// or
Mail::assertQueued(OrderShipped::class);
```

## Key Assertions

| Method | Description |
|--------|-------------|
| `Mail::assertSent(Mailable::class)` | Mailable was sent immediately |
| `Mail::assertQueued(Mailable::class)` | Mailable was queued for delivery |
| `Mail::assertSent(Mailable::class, $email)` | Sent to specific address |
| `Mail::assertSent(Mailable::class, $closure)` | Sent and closure returns true |
| `Mail::assertNotSent(Mailable::class)` | Mailable was not sent |
| `Mail::assertNotQueued(Mailable::class)` | Mailable was not queued |
| `Mail::assertNothingSent()` | No mailables sent or queued |
| `Mail::assertSentTimes(Mailable::class, $n)` | Sent exactly N times |
| `Mail::assertSentCount($n)` | Total mails sent equals N |

## Why It Matters

- **No side effects**: Real emails in CI clutter inboxes and fail in sandboxed environments
- **Behaviour verified**: Tests confirm mail is triggered at the right time, to the right address
- **Fast**: No SMTP round-trip — tests run at full speed

Reference: [Laravel Mocking — Mail Fake](https://laravel.com/docs/13.x/mocking#mail-fake)
