# Spatie Webhooks

The application uses **spatie/laravel-webhook-server** (v3.10) and **spatie/laravel-webhook-client** (v3.5) for sending and receiving webhooks.

## Webhook Client (receiving)

- **Route**: `POST /webhooks/spatie` (config name: `default`)
- **Config**: `config/webhook-client.php`
- **Secret**: `WEBHOOK_CLIENT_SECRET` in `.env` — shared with the sending app for signature verification
- **Job**: `App\Jobs\ProcessWebhookJob` — extend or modify to handle payloads; rate-limited (10/sec) via [rate-limited-jobs.md](./rate-limited-jobs.md)
- **Storage**: Incoming webhooks are stored in `webhook_calls`; pruned after 30 days via `model:prune`
- **CSRF**: Excluded via `webhooks/*` in `bootstrap/app.php`

### Signature verification

The client expects an `Signature` header: `hash_hmac('sha256', $request->getContent(), $secret)`.

### Multiple endpoints

Add more configs in `config/webhook-client.php` and register routes:

```php
Route::webhooks('webhooks/another-app', 'another-app');
```

## Webhook Server (sending)

- **Config**: `config/webhook-server.php`
- **Queue**: Uses `default` queue; Horizon tags: `['webhooks']`

### Usage

```php
use Spatie\WebhookServer\WebhookCall;

WebhookCall::create()
    ->url('https://other-app.com/webhooks')
    ->payload(['event' => 'order.created', 'id' => 123])
    ->useSecret('shared-secret')
    ->useTimestamp()  // recommended for replay protection
    ->dispatch();
```

### Options

- `->dispatchSync()` — run immediately (no queue)
- `->timeoutInSeconds(5)` — override default 3s
- `->maximumTries(5)` — override default 3 retries
- `->withHeaders([...])` — extra headers
- `->doNotSign()` — skip signing (not recommended)

## References

- [laravel-webhook-server](https://github.com/spatie/laravel-webhook-server)
- [laravel-webhook-client](https://github.com/spatie/laravel-webhook-client)
