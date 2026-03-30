# Starter Kit Enhancement: Outbound Webhooks & Email Template Management

> **Date:** 2026-03-30
> **Status:** Approved
> **Scope:** Two independent subsystems for the Laravel starter kit
> **Origin:** [starter-kit-enhancements-round2.md](/Users/apple/Code/clients/piab/fusioncrmv3/v4/specs/starter-kit-enhancements-round2.md)

---

## 1. Architecture Overview

Two independent subsystems, both following the same pattern:

```
config registry -> model (org-scoped) -> service -> Inertia settings page
```

### Outbound Webhooks

- `config/webhooks.php` — event registry (grouped by domain), retry config, Fuse circuit breaker settings
- `WebhookEndpoint` model — `BelongsToOrganization`, `LogsActivity`, encrypted secret
- `WebhookDispatcher` service — SQL-filtered dispatch via spatie/webhook-server, circuit breaking via harris21/laravel-fuse
- `/settings/webhooks` — CRUD + grouped event multi-select + circuit state display + test ping + reset button

### Email Template Management

- Extends existing `martinpetricko/laravel-database-mail` — no parallel system
- Migration adds `organization_id` to existing `mail_templates` table
- Inertia UI reads/writes to existing table, variables pulled from `TriggersDatabaseMail` contract
- Fallback chain: org template -> default template (where `organization_id IS NULL`)
- `/settings/email-templates` — list by event, edit with Tiptap + variable toolbar, live preview, reset to default

### Shared Infrastructure

- Two new permission groups: `org_webhooks`, `org_email_templates`
- Both models use `LogsActivity` (secrets excluded from logging)
- Documentation page for org admins listing all default events

---

## 2. Outbound Webhooks

### 2.1 Dependencies

| Package | Purpose | Status |
|---------|---------|--------|
| `spatie/laravel-webhook-server` | Signed webhook dispatch with retry | Already installed (^3.10) |
| `harris21/laravel-fuse` | Circuit breaker per endpoint | **New — needs `composer require`** |

### 2.2 Migration: `create_webhook_endpoints_table`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigIncrements | PK |
| `organization_id` | foreignId | FK -> organizations, cascades, indexed |
| `url` | string(500) | Endpoint URL |
| `events` | json | Array of subscribed event names |
| `secret` | text | Encrypted at rest via Eloquent `encrypted` cast |
| `is_active` | boolean | Manual toggle by org admin (default true) |
| `description` | string(255), nullable | Optional label like "Zapier Production" |
| `last_called_at` | timestamp, nullable | Updated on successful dispatch |
| `created_by` | foreignId, nullable | FK -> users, SET NULL on delete |
| `timestamps` | | created_at, updated_at |
| `softDeletes` | | Recoverable deletes |

No `failure_count` column — Fuse tracks failure state in Redis.

### 2.3 Model: `WebhookEndpoint`

**Traits:** `BelongsToOrganization`, `SoftDeletes`, `LogsActivity`

**Casts:**

- `events` -> `array`
- `secret` -> `encrypted`
- `is_active` -> `boolean`
- `last_called_at` -> `datetime`

**Activity log options:**

- Logs: `url`, `events`, `is_active`, `description`
- Excludes: `secret`
- Log name: `webhooks`
- Only dirty attributes

### 2.4 Config: `config/webhooks.php`

```php
return [
    'events' => [
        'Users' => [
            'user.created' => 'A new user registered',
            'user.deleted' => 'A user was deleted',
        ],
        'Organizations' => [
            'organization.updated' => 'Organization settings changed',
        ],
        'Invitations' => [
            'invitation.sent' => 'An invitation was sent',
            'invitation.accepted' => 'An invitation was accepted',
        ],
    ],

    'timeout' => 5, // seconds for test ping

    'fuse' => [
        'threshold' => 50,    // failure rate % to trip circuit
        'timeout' => 3600,    // seconds before half-open probe (1hr)
        'min_requests' => 3,  // minimum requests before evaluating
    ],
];
```

- Top-level keys in `events` are group names used by the grouped multi-select UI
- Modules extend in their ServiceProvider: `config(['webhooks.events.Contacts' => ['contact.created' => '...']])`

### 2.5 Service: `WebhookDispatcher`

```php
namespace App\Services;

use App\Models\WebhookEndpoint;
use Harris21\Fuse\CircuitBreaker;
use Spatie\WebhookServer\WebhookCall;

final class WebhookDispatcher
{
    /**
     * Dispatch an event to all matching active endpoints for an organization.
     * Skips endpoints whose circuit breaker is open.
     */
    public function dispatch(string $event, array $payload, int $organizationId): void
    {
        WebhookEndpoint::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->each(function (WebhookEndpoint $endpoint) use ($event, $payload) {
                $breaker = new CircuitBreaker("webhook-{$endpoint->id}");

                if ($breaker->isOpen()) {
                    return;
                }

                WebhookCall::create()
                    ->url($endpoint->url)
                    ->payload([
                        'event' => $event,
                        'timestamp' => now()->toIso8601String(),
                        'data' => $payload,
                    ])
                    ->useSecret($endpoint->secret)
                    ->dispatch();

                $endpoint->touchQuietly('last_called_at');
            });
    }

    /**
     * Synchronous test ping with 5s timeout.
     * Returns ['status' => int, 'time_ms' => int] or ['error' => string].
     */
    public function testPing(WebhookEndpoint $endpoint): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(config('webhooks.timeout', 5))
                ->post($endpoint->url, [
                    'event' => 'test.ping',
                    'timestamp' => now()->toIso8601String(),
                    'data' => [],
                ]);

            return [
                'status' => $response->status(),
                'time_ms' => (int) ((microtime(true) - $start) * 1000),
            ];
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
                'time_ms' => (int) ((microtime(true) - $start) * 1000),
            ];
        }
    }
}
```

### 2.6 Circuit Breaker Integration

**Recording success/failure:** Register listeners for spatie/webhook-server's events:

- `WebhookCallSucceededEvent` -> `(new CircuitBreaker("webhook-{$endpointId}"))->recordSuccess()`
- `WebhookCallFailedEvent` -> `(new CircuitBreaker("webhook-{$endpointId}"))->recordFailure()`

**Fuse config (`config/fuse.php`):** Add a `webhook` service entry using values from `config/webhooks.php`:

```php
'services' => [
    'webhook' => [
        'threshold' => 50,
        'timeout' => 3600,
        'min_requests' => 3,
    ],
],
```

**Separation of concerns:**

- `is_active` = manual toggle (org admin deliberately disables/enables)
- Circuit breaker = automatic protection (Fuse trips/recovers based on failure rate)
- Both must pass for dispatch: `is_active = true` AND circuit not open

### 2.7 Webhook Settings UI (`/settings/webhooks`)

**Permission gate:** `org.webhooks.view` (read-only), `org.webhooks.manage` (full CRUD)

**List view:**

| Column | Content |
|--------|---------|
| URL | Truncated with tooltip |
| Description | Optional label |
| Events | Badge with count (e.g., "3 events") |
| Status | Indicator: green=healthy, amber=half-open, red=tripped, grey=disabled |
| Last Called | Relative timestamp |
| Actions | Edit, Test, Reset Circuit, Delete |

- "Add Webhook" button (gated by `org.webhooks.manage`)

**Create/Edit form:**

- **URL** — text input, validated (must be HTTPS in production)
- **Description** — optional text input
- **Events** — grouped multi-select, groups from `config/webhooks.php` top-level keys
- **Active** — toggle switch
- **Secret** — auto-generated on create (32-byte random), hidden on edit with "Regenerate" button (confirmation dialog)

**Test ping:**

- Button on each endpoint row
- Sends synchronous POST with 5s timeout
- Inline toast result: "200 OK in 142ms" or "Connection timeout after 5s"

**Reset circuit:**

- Button visible only when circuit is tripped or half-open
- Calls `CircuitBreaker("webhook-{$id}")->reset()`
- Confirmation dialog: "This will re-enable automatic delivery to this endpoint"

---

## 3. Email Template Management

### 3.1 Dependencies

| Package | Purpose | Status |
|---------|---------|--------|
| `martinpetricko/laravel-database-mail` | DB-backed email templates + event binding | Already installed (^2.0.3) |
| `@tiptap/react` + starter-kit + extensions | Rich text editor for template body | **New — needs `npm install`** |

### 3.2 Migration: `add_organization_id_to_mail_templates`

Adds to the existing `mail_templates` table:

| Column | Type | Notes |
|--------|------|-------|
| `organization_id` | foreignId, nullable | FK -> organizations, cascades, indexed. NULL = default template |
| `is_default` | boolean | Default: false. Seeded templates marked true |

No new `event_class` or `variables` columns. The package's existing `event` column (already indexed) and `TriggersDatabaseMail` contract handle both.

### 3.3 Template Resolution Logic

No new service class. Resolution via query:

1. `WHERE event = $event AND organization_id = $orgId AND is_active = true`
2. Fallback: `WHERE event = $event AND organization_id IS NULL AND is_default = true`

When an org admin edits a default template, an org-scoped copy is created. "Reset to default" deletes the org-scoped row, falling back to the default.

### 3.4 Variable Discovery

Variables are pulled at runtime from each event's `TriggersDatabaseMail` contract. The existing 7 events already define their recipients and resolvers. The UI calls an API endpoint that introspects registered events in `config/database-mail.php` and returns available variables for the toolbar.

No second config file (`config/email-templates.php` from the original spec is dropped).

### 3.5 API Routes

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/settings/email-templates` | Page: list all registered events with template status |
| GET | `/settings/email-templates/{event}` | Page: editor for specific event template |
| PUT | `/settings/email-templates/{event}` | Save org-scoped template (creates copy if editing default) |
| POST | `/settings/email-templates/{event}/preview` | Render template with sample data, return HTML |
| DELETE | `/settings/email-templates/{event}` | Reset: delete org-scoped copy, revert to default |

### 3.6 Tiptap Editor

- **Rich text** with basic formatting: bold, italic, links, lists, headings
- **Variable toolbar** above editor — buttons for each available variable grouped by entity
- Clicking a variable inserts `{{ variable.name }}` at cursor position
- **Subject field** — plain text input with variable dropdown (not Tiptap)
- **Split view** — editor left, live preview right (updates on blur/pause)
- **"Preview with sample data"** button — renders with realistic mock data via the preview endpoint
- **"Reset to default"** button — destructive action with confirmation dialog, only visible on customized templates

### 3.7 Email Template Settings UI (`/settings/email-templates`)

**Permission gate:** `org.email-templates.view` (read-only), `org.email-templates.manage` (edit/reset)

**List view:**

| Column | Content |
|--------|---------|
| Event | Human-readable name from TriggersDatabaseMail |
| Description | Event description |
| Status | Badge: "Default" (blue) or "Customized" (green) |
| Last Modified | Relative timestamp, only for customized |

Click row -> opens editor page.

### 3.8 Default Templates

The 7 existing events registered in `config/database-mail.php`:

1. `UserCreated` — Welcome email
2. `OrganizationInvitationSent` — Invitation email
3. `OrganizationInvitationAccepted` — Invitation accepted notification
4. `NewTermsVersionPublished` — Terms update notification
5. `TrialEndingReminder` — Trial expiry warning
6. `DunningFailedPaymentReminder` — Failed payment notification
7. `InvoicePaid` — Payment receipt

Seeder marks existing templates as `is_default = true` with `organization_id = NULL`.

---

## 4. Permissions

Added to `database/seeders/data/organization-permissions.json`:

```json
"org_webhooks": {
    "permissions": ["org.webhooks.view", "org.webhooks.manage"],
    "description": "Webhook endpoint management"
},
"org_email_templates": {
    "permissions": ["org.email-templates.view", "org.email-templates.manage"],
    "description": "Email template customization"
}
```

- `*.view` — read-only access to the settings page
- `*.manage` — full CRUD, test ping, reset templates, regenerate secrets
- Both permission groups added to default org admin role template
- Requires `permission:sync` after migration

---

## 5. Audit Trail

### WebhookEndpoint

- **Trait:** `LogsActivity`
- **Logged attributes:** `url`, `events`, `is_active`, `description`
- **Excluded:** `secret` (never log encrypted secrets)
- **Log name:** `webhooks`
- **Options:** `logOnlyDirty()`

### Email Templates

- **Manual** `activity()` calls when org admin saves or resets a template
- **Logged:** event name, action (`customized` / `reset`), subject change
- **Excluded:** full body HTML (too verbose)
- **Log name:** `email-templates`

Both appear in the existing audit log UI at `/settings/audit-log`.

---

## 6. Documentation

### Developer Docs

**`docs/developer/backend/webhooks.md`:**

- How the dispatch system works (WebhookDispatcher + spatie/webhook-server + Fuse)
- How to register new events from a module's ServiceProvider
- How to add a new event group to config
- Circuit breaker behavior and Fuse configuration
- Testing webhooks in development

**`docs/developer/backend/email-templates.md`** (update existing database-mail docs):

- How org-scoped templates extend database-mail
- Fallback chain behavior
- How to add a new event with `TriggersDatabaseMail`
- How variables are discovered and rendered
- Seeding default templates

### Admin/User Guide

**`docs/user-guide/webhooks.md`:**

- What webhooks are and why they matter (Zapier, Make, custom integrations)
- Table of all default events with descriptions and example payloads
- How to create an endpoint, select events, test it
- What status indicators mean (healthy / tripped / recovering / disabled)
- How to manually reset a tripped circuit
- HMAC signing explained simply

**`docs/user-guide/email-templates.md`:**

- Table of all default email events with descriptions and available variables
- How to customize a template
- How to use variables with examples
- How to preview before saving
- How to reset to default

---

## 7. New Dependencies Summary

| Package | Type | Purpose |
|---------|------|---------|
| `harris21/laravel-fuse` | Composer | Circuit breaker for webhook endpoints |
| `@tiptap/react` | NPM | Rich text editor for email template body |
| `@tiptap/starter-kit` | NPM | Tiptap base extensions (bold, italic, lists, etc.) |

All other packages (`spatie/laravel-webhook-server`, `martinpetricko/laravel-database-mail`, `spatie/laravel-activitylog`) are already installed.

---

## 8. Implementation Notes

- Features 1 and 2 are fully independent — can be built in parallel
- Both follow the same pattern: migration + model + service + config + Inertia page
- Both use `BelongsToOrganization` for org scoping
- Modules register events in their ServiceProvider's `boot()` method
- No changes to existing database-mail event registrations or behavior
- The `config/email-templates.php` and `EmailTemplateService` from the original spec are dropped in favor of extending the existing package
