# Lemon Squeezy Integration (One-Time Products)

This app supports **Lemon Squeezy** as a payment gateway for **one-time products** (generic, custom-priced checkouts). Subscriptions are not supported via this gateway; use Stripe or Paddle for subscriptions.

## Prerequisites

- [Lemon Squeezy](https://lemonsqueezy.com) account
- A **single-payment** product in Lemon Squeezy (used as the "generic" product for dynamic amounts)

## Setup

### 1. Create a generic one-time product in Lemon Squeezy

1. In [Lemon Squeezy Dashboard](https://app.lemonsqueezy.com), create a product.
2. Set pricing to **Single payment** (one-time).
3. You can set a placeholder price; the app will override it with **custom price** when creating checkouts.
4. Copy the **Variant ID** (from the product's variant).

### 2. API key and store

1. Go to **Settings** → **API** and create an API key.
2. Go to **Settings** → **Stores** and copy your **Store ID** (the number after `#` in the store URL).

### 3. Configure in the app

Add to `.env`:

```env
LEMON_SQUEEZY_API_KEY=your-api-key
LEMON_SQUEEZY_STORE=your-store-id
LEMON_SQUEEZY_SIGNING_SECRET=your-webhook-signing-secret
LEMON_SQUEEZY_GENERIC_VARIANT_ID=your-variant-id
```

Optional: `BILLING_LEMON_SQUEEZY_CENTS_PER_CREDIT` – used when deriving credits from order total when `custom_data.credits` is not set (default: 10).

### 4. Webhooks

The `lemonsqueezy/laravel` package registers:

- **URL:** `https://yourdomain.com/lemon-squeezy/webhook`
- This path is excluded from CSRF in `bootstrap/app.php`.

In Lemon Squeezy: **Settings** → **Webhooks** → add the URL above and subscribe to `order_created` for one-time orders. Set the **Signing secret** and store it in `LEMON_SQUEEZY_SIGNING_SECRET`.

### 5. Using the gateway in code

```php
use Modules\Billing\Services\PaymentGateway\PaymentGatewayManager;

$gateway = app(PaymentGatewayManager::class)->resolve('lemon_squeezy');

// One-time checkout (e.g. credits)
$url = $gateway->createCheckoutSession($organization, [
    [
        'name' => '100 Credits',
        'amount' => 10000, // cents
        'quantity' => 1,
        'credits' => 100,
        'credit_pack_id' => 1, // optional, for webhook metadata
    ],
], $successUrl, $cancelUrl);

return redirect()->away($url);
```

## Package

- [lemonsqueezy/laravel](https://github.com/lemonsqueezy/laravel) – checkout creation and webhooks
- App gateway: `Modules\Billing\Services\PaymentGateway\Gateways\LemonSqueezyGateway`

## Related

- [Billing & Tenancy](billing-and-tenancy.md)
