# Webhooks

## Overview

Webhooks provide a way to send real-time notifications to external services when events occur in your organization. The webhook infrastructure is built on top of `spatie/laravel-webhook-server` with circuit breaker protection via `harris21/laravel-fuse`.

When an event occurs (e.g., a user registers), the system queries all active webhook endpoints subscribed to that event and dispatches the event payload to each endpoint's URL via HTTP POST.

## How the Dispatch System Works

The `WebhookDispatcher` service is the core mechanism:

1. **Query Active Endpoints**: Find all endpoints for the organization that are active and subscribed to the event
2. **Check Circuit Breaker**: Before dispatching, check if the endpoint's circuit breaker is open (tripped). If open, skip that endpoint.
3. **Create Webhook Call**: Create a `WebhookCall` via Spatie's package with the event, timestamp, and payload
4. **Sign with Secret**: The call is automatically signed with HMAC-SHA256 using the endpoint's secret
5. **Dispatch**: Queue the webhook for delivery via Laravel's queue system (Horizon in production)
6. **Update Timestamp**: Touch `last_called_at` on success (even if the remote endpoint returns an error)

### Example Dispatch

```php
app(WebhookDispatcher::class)->dispatch(
    'user.created',
    $user->toArray(),
    $user->organization_id
);
```

The webhook recipient receives:

```json
{
  "event": "user.created",
  "timestamp": "2026-03-30T10:00:00+00:00",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com"
  }
}
```

## Registering New Events

Events are grouped by domain and registered in `config/webhooks.php`. To add new events from a module, register them in your module's `ServiceProvider::boot()`:

```php
<?php

namespace Modules\Contacts\Providers;

use Illuminate\Support\ServiceProvider;

final class ContactsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register webhook events
        $existing = config('webhooks.events', []);
        $existing['Contacts'] = [
            'contact.created' => 'A new contact was created',
            'contact.updated' => 'A contact was updated',
            'contact.deleted' => 'A contact was deleted',
        ];
        config(['webhooks.events' => $existing]);
    }
}
```

The UI will automatically group these under the "Contacts" section in the webhook event multi-select.

## Dispatching Webhooks

Dispatch webhooks from any action, controller, or event listener:

```php
use App\Services\WebhookDispatcher;

// In a controller, action, or listener
app(WebhookDispatcher::class)->dispatch(
    event: 'contact.created',
    payload: $contact->toArray(),
    organizationId: $contact->organization_id
);
```

The payload should be an associative array of data to send to the webhook recipient. Typically this is the model converted to an array.

## Circuit Breaker Behavior

The circuit breaker (Fuse) automatically protects endpoints from cascading failures:

- **Threshold**: 50% failure rate — if 50% of requests to an endpoint fail, the circuit trips
- **Min Requests**: 3 — at least 3 requests must be made before evaluating the failure rate
- **Timeout**: 3600 seconds (1 hour) — after tripping, the circuit waits 1 hour before attempting a "half-open" probe
- **Half-Open Probe**: After the timeout, the next webhook attempt to that endpoint is sent as a test. If it succeeds, the circuit closes and normal operation resumes. If it fails, the circuit remains open for another hour.

Endpoints with an open circuit are skipped during dispatch—no webhooks are sent to them until the circuit recovers. Organization admins can manually reset a tripped circuit via the webhooks UI.

## Configuration

All webhook settings are in `config/webhooks.php`:

```php
return [
    'events' => [
        // Grouped by domain
        'Users' => [
            'user.created' => 'A new user registered',
            'user.deleted' => 'A user was deleted',
        ],
        // Modules extend this at runtime
    ],
    'timeout' => 5,  // Seconds to wait for test ping responses
    'fuse' => [
        'threshold' => 50,    // Failure rate percentage
        'timeout' => 3600,    // Seconds before half-open probe
        'min_requests' => 3,  // Min requests to evaluate
    ],
];
```

## Permissions

Webhook management is controlled by two permissions:

- **`org.webhooks.view`**: View webhook endpoints and logs (owner, admin)
- **`org.webhooks.manage`**: Create, update, delete, test, and reset endpoints (owner, admin)

Both permissions are `org_grantable`, meaning organization owners can grant them to other members via roles.

## Testing Webhooks in Development

### Using the Test Button

The webhooks UI provides a **Test** button on each endpoint. Clicking it sends a synchronous test ping with the event `test.ping`:

```json
{
  "event": "test.ping",
  "timestamp": "2026-03-30T10:00:00+00:00",
  "data": {}
}
```

The test ping uses a 5-second timeout (configurable via `config('webhooks.timeout')`). Results show the HTTP status code and response time in milliseconds.

### Using `Http::fake()` in Tests

In feature or unit tests, mock webhook calls using Illuminate's HTTP faking:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'https://example.com/webhooks' => Http::response(['ok' => true], 200),
]);

app(WebhookDispatcher::class)->dispatch('user.created', ['id' => 1], 1);

// Assert the webhook was queued (via Horizon in production, or synchronous queue in tests)
```

## Webhook Endpoint Model

Endpoints are stored in the `webhook_endpoints` table with the following attributes:

| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGINT | Primary key |
| `organization_id` | BIGINT | Foreign key to organizations |
| `url` | VARCHAR(500) | Target URL (max 500 chars) |
| `events` | JSON | Array of subscribed event names |
| `secret` | TEXT | HMAC secret (encrypted at rest) |
| `is_active` | BOOLEAN | Whether endpoint receives webhooks |
| `description` | VARCHAR(255) | Optional admin note |
| `last_called_at` | TIMESTAMP | Last dispatch attempt time |
| `created_by` | BIGINT | Foreign key to users (nullable) |
| `timestamps` | — | `created_at`, `updated_at` |
| `deleted_at` | TIMESTAMP | Soft delete support |

## References

- **Spatie Webhook Server**: [spatie/laravel-webhook-server](https://github.com/spatie/laravel-webhook-server)
- **Fuse Circuit Breaker**: [harris21/laravel-fuse](https://github.com/harris21/laravel-fuse)
- **Config**: `config/webhooks.php`
- **Service**: `App\Services\WebhookDispatcher`
- **Model**: `App\Models\WebhookEndpoint`
