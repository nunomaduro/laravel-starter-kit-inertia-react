# Outbound Webhooks Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add org-scoped outbound webhook infrastructure with circuit breaker protection, allowing any module to dispatch webhooks and org admins to manage endpoints via a settings UI.

**Architecture:** Config-driven event registry -> WebhookEndpoint model (org-scoped, encrypted secrets) -> WebhookDispatcher service using spatie/webhook-server for queued delivery and harris21/laravel-fuse for circuit breaking -> Inertia React settings page with CRUD, grouped event selector, circuit state display, and test ping.

**Tech Stack:** Laravel 13, spatie/laravel-webhook-server ^3.10, harris21/laravel-fuse (new), Inertia.js v2, React 19, Tailwind CSS v4

**Spec:** `docs/superpowers/specs/2026-03-30-webhooks-email-templates-design.md` (Section 2)

---

## File Structure

### New Files

| File | Responsibility |
|------|---------------|
| `config/webhooks.php` | Event registry (grouped), timeout, Fuse config |
| `database/migrations/XXXX_create_webhook_endpoints_table.php` | Schema for webhook endpoints |
| `app/Models/WebhookEndpoint.php` | Eloquent model with org scoping, encrypted secret, activity log |
| `database/factories/WebhookEndpointFactory.php` | Test factory |
| `app/Services/WebhookDispatcher.php` | Dispatch + test ping logic |
| `app/Listeners/RecordWebhookSuccess.php` | Circuit breaker success recording |
| `app/Listeners/RecordWebhookFailure.php` | Circuit breaker failure recording |
| `app/Http/Controllers/Settings/WebhooksController.php` | CRUD + test ping + circuit reset |
| `app/Http/Requests/Settings/StoreWebhookEndpointRequest.php` | Create validation |
| `app/Http/Requests/Settings/UpdateWebhookEndpointRequest.php` | Update validation |
| `resources/js/pages/settings/webhooks/index.tsx` | List page with status indicators |
| `resources/js/pages/settings/webhooks/create.tsx` | Create form with grouped multi-select |
| `resources/js/pages/settings/webhooks/edit.tsx` | Edit form |
| `tests/Feature/Settings/WebhooksControllerTest.php` | Controller feature tests |
| `tests/Unit/Services/WebhookDispatcherTest.php` | Dispatcher unit tests |
| `docs/developer/backend/webhooks.md` | Developer documentation |
| `docs/user-guide/webhooks.md` | Admin user guide |

### Modified Files

| File | Change |
|------|--------|
| `database/seeders/data/organization-permissions.json` | Add `org_webhooks` permission group |
| `routes/settings.php` | Add webhook settings routes |
| `resources/js/layouts/settings/layout.tsx` | Add webhooks nav item |
| `app/Providers/AppServiceProvider.php` | Register webhook event listeners |

---

### Task 1: Install harris21/laravel-fuse

**Files:**
- Modify: `composer.json`
- Create: `config/fuse.php` (via vendor:publish)

- [ ] **Step 1: Install the package**

```bash
composer require harris21/laravel-fuse
```

- [ ] **Step 2: Publish the config**

```bash
php artisan vendor:publish --tag=fuse-config
```

- [ ] **Step 3: Verify config exists**

```bash
ls config/fuse.php
```

Expected: file exists

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.lock config/fuse.php
git commit -m "chore: install harris21/laravel-fuse circuit breaker package"
```

---

### Task 2: Create webhooks config and permissions

**Files:**
- Create: `config/webhooks.php`
- Modify: `database/seeders/data/organization-permissions.json`

- [ ] **Step 1: Create the config file**

Create `config/webhooks.php`:

```php
<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Event Registry
    |--------------------------------------------------------------------------
    |
    | Events are grouped by domain. Top-level keys are group names used by
    | the grouped multi-select in the UI. Modules extend in their
    | ServiceProvider boot(): config(['webhooks.events.Contacts' => [...]])
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | Test Ping Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait when an org admin clicks "Test" on an endpoint.
    |
    */
    'timeout' => 5,

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker (Fuse) Settings
    |--------------------------------------------------------------------------
    |
    | Controls when endpoints are automatically protected from cascading
    | failures. See harris21/laravel-fuse documentation for details.
    |
    */
    'fuse' => [
        'threshold' => 50,    // Failure rate percentage to trip circuit
        'timeout' => 3600,    // Seconds before half-open probe (1 hour)
        'min_requests' => 3,  // Minimum requests before evaluating failure rate
    ],
];
```

- [ ] **Step 2: Add webhook permissions to organization-permissions.json**

In `database/seeders/data/organization-permissions.json`, add the `org_webhooks` group after the `org_dashboards` group inside `organization_permissions`:

```json
"org_webhooks": {
    "permissions": [
        {"name": "org.webhooks.view", "roles": ["owner", "admin"], "org_grantable": true},
        {"name": "org.webhooks.manage", "roles": ["owner", "admin"], "org_grantable": true}
    ]
}
```

- [ ] **Step 3: Commit**

```bash
git add config/webhooks.php database/seeders/data/organization-permissions.json
git commit -m "feat(webhooks): add config registry and org permissions"
```

---

### Task 3: Create migration and model

**Files:**
- Create: `database/migrations/XXXX_create_webhook_endpoints_table.php`
- Create: `app/Models/WebhookEndpoint.php`
- Create: `database/factories/WebhookEndpointFactory.php`

- [ ] **Step 1: Create the migration**

```bash
php artisan make:migration create_webhook_endpoints_table --no-interaction
```

Edit the generated migration:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_endpoints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('url', 500);
            $table->json('events')->default('[]');
            $table->text('secret')->comment('Encrypted at rest via Eloquent cast');
            $table->boolean('is_active')->default(true);
            $table->string('description', 255)->nullable();
            $table->timestamp('last_called_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index(['organization_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
```

- [ ] **Step 2: Create the model**

```bash
php artisan make:class "Models/WebhookEndpoint" --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\WebhookEndpointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $url
 * @property array<int, string> $events
 * @property string $secret
 * @property bool $is_active
 * @property string|null $description
 * @property \Carbon\Carbon|null $last_called_at
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Organization $organization
 * @property-read User|null $creator
 */
final class WebhookEndpoint extends Model
{
    /** @use HasFactory<WebhookEndpointFactory> */
    use HasFactory;

    use BelongsToOrganization;
    use LogsActivity;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'url',
        'events',
        'is_active',
        'description',
        'secret',
        'created_by',
    ];

    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['url', 'events', 'is_active', 'description'])
            ->logOnlyDirty()
            ->useLogName('webhooks');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'events' => 'array',
            'secret' => 'encrypted',
            'is_active' => 'boolean',
            'last_called_at' => 'datetime',
        ];
    }
}
```

- [ ] **Step 3: Create the factory**

```bash
php artisan make:factory WebhookEndpointFactory --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEndpoint>
 */
final class WebhookEndpointFactory extends Factory
{
    protected $model = WebhookEndpoint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allEvents = collect(config('webhooks.events', []))
            ->flatMap(fn (array $group): array => array_keys($group))
            ->all();

        return [
            'organization_id' => Organization::factory(),
            'url' => fake()->url().'/webhooks',
            'events' => fake()->randomElements($allEvents, min(2, count($allEvents))),
            'secret' => Str::random(32),
            'is_active' => true,
            'description' => fake()->optional(0.7)->sentence(3),
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    /**
     * @param list<string> $events
     */
    public function forEvents(array $events): static
    {
        return $this->state(fn (): array => ['events' => $events]);
    }
}
```

- [ ] **Step 4: Run the migration**

```bash
php artisan migrate
```

Expected: Migration runs successfully.

- [ ] **Step 5: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/*create_webhook_endpoints_table.php app/Models/WebhookEndpoint.php database/factories/WebhookEndpointFactory.php
git commit -m "feat(webhooks): add WebhookEndpoint model, migration, and factory"
```

---

### Task 4: Create WebhookDispatcher service

**Files:**
- Create: `app/Services/WebhookDispatcher.php`
- Create: `tests/Unit/Services/WebhookDispatcherTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/Services/WebhookDispatcherTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\WebhookEndpoint;
use App\Services\WebhookDispatcher;
use Spatie\WebhookServer\WebhookCall;

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->dispatcher = app(WebhookDispatcher::class);
});

test('dispatch sends to active endpoints subscribed to the event', function (): void {
    WebhookCall::fake();

    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->forEvents(['user.created', 'user.deleted'])
        ->create();

    $this->dispatcher->dispatch('user.created', ['id' => 1], $this->organization->id);

    WebhookCall::assertSent(function (array $call) use ($endpoint): bool {
        return $call['url'] === $endpoint->url
            && $call['payload']['event'] === 'user.created'
            && $call['payload']['data'] === ['id' => 1];
    });
});

test('dispatch skips inactive endpoints', function (): void {
    WebhookCall::fake();

    WebhookEndpoint::factory()
        ->for($this->organization)
        ->forEvents(['user.created'])
        ->inactive()
        ->create();

    $this->dispatcher->dispatch('user.created', ['id' => 1], $this->organization->id);

    WebhookCall::assertNothingSent();
});

test('dispatch skips endpoints not subscribed to the event', function (): void {
    WebhookCall::fake();

    WebhookEndpoint::factory()
        ->for($this->organization)
        ->forEvents(['user.deleted'])
        ->create();

    $this->dispatcher->dispatch('user.created', ['id' => 1], $this->organization->id);

    WebhookCall::assertNothingSent();
});

test('dispatch skips endpoints from other organizations', function (): void {
    WebhookCall::fake();

    $otherOrg = Organization::factory()->create();

    WebhookEndpoint::factory()
        ->for($otherOrg)
        ->forEvents(['user.created'])
        ->create();

    $this->dispatcher->dispatch('user.created', ['id' => 1], $this->organization->id);

    WebhookCall::assertNothingSent();
});

test('testPing returns status and time on success', function (): void {
    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->create(['url' => 'https://httpbin.org/post']);

    // We mock HTTP instead of hitting a real URL
    Http::fake(['*' => Http::response('OK', 200)]);

    $result = $this->dispatcher->testPing($endpoint);

    expect($result)->toHaveKeys(['status', 'time_ms'])
        ->and($result['status'])->toBe(200);
});

test('testPing returns error on connection failure', function (): void {
    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->create(['url' => 'https://unreachable.invalid/webhook']);

    Http::fake(['*' => Http::response('', 500)]);

    $result = $this->dispatcher->testPing($endpoint);

    expect($result)->toHaveKey('status')
        ->and($result['status'])->toBe(500);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=WebhookDispatcherTest
```

Expected: FAIL — class not found.

- [ ] **Step 3: Write the service**

Create `app/Services/WebhookDispatcher.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WebhookEndpoint;
use Harris21\Fuse\CircuitBreaker;
use Illuminate\Support\Facades\Http;
use Spatie\WebhookServer\WebhookCall;

final class WebhookDispatcher
{
    /**
     * Dispatch an event to all matching active endpoints for an organization.
     * Skips endpoints whose circuit breaker is open.
     */
    public function dispatch(string $event, array $payload, int $organizationId): void
    {
        WebhookEndpoint::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->each(function (WebhookEndpoint $endpoint) use ($event, $payload): void {
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
     * Synchronous test ping with configurable timeout.
     *
     * @return array{status?: int, error?: string, time_ms: int}
     */
    public function testPing(WebhookEndpoint $endpoint): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout((int) config('webhooks.timeout', 5))
                ->withHeaders(['Content-Type' => 'application/json'])
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

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=WebhookDispatcherTest
```

Expected: All tests pass. If `WebhookCall::fake()` is not supported by spatie/webhook-server, adjust tests to mock HTTP directly and assert the HTTP call was made instead.

- [ ] **Step 5: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add app/Services/WebhookDispatcher.php tests/Unit/Services/WebhookDispatcherTest.php
git commit -m "feat(webhooks): add WebhookDispatcher service with circuit breaker"
```

---

### Task 5: Create circuit breaker event listeners

**Files:**
- Create: `app/Listeners/RecordWebhookSuccess.php`
- Create: `app/Listeners/RecordWebhookFailure.php`
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Create the success listener**

```bash
php artisan make:listener RecordWebhookSuccess --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Harris21\Fuse\CircuitBreaker;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

final class RecordWebhookSuccess
{
    public function handle(WebhookCallSucceededEvent $event): void
    {
        $endpointId = $this->extractEndpointId($event);

        if ($endpointId === null) {
            return;
        }

        $breaker = new CircuitBreaker("webhook-{$endpointId}");
        $breaker->recordSuccess();
    }

    private function extractEndpointId(WebhookCallSucceededEvent $event): ?int
    {
        // The endpoint ID is embedded in the webhook meta or can be extracted
        // from the URL match against WebhookEndpoint records
        $meta = $event->meta ?? [];

        return isset($meta['endpoint_id']) ? (int) $meta['endpoint_id'] : null;
    }
}
```

- [ ] **Step 2: Create the failure listener**

```bash
php artisan make:listener RecordWebhookFailure --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Harris21\Fuse\CircuitBreaker;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;

final class RecordWebhookFailure
{
    public function handle(WebhookCallFailedEvent $event): void
    {
        $endpointId = $this->extractEndpointId($event);

        if ($endpointId === null) {
            return;
        }

        $breaker = new CircuitBreaker("webhook-{$endpointId}");
        $breaker->recordFailure();
    }

    private function extractEndpointId(WebhookCallFailedEvent $event): ?int
    {
        $meta = $event->meta ?? [];

        return isset($meta['endpoint_id']) ? (int) $meta['endpoint_id'] : null;
    }
}
```

- [ ] **Step 3: Update WebhookDispatcher to include endpoint_id in meta**

In `app/Services/WebhookDispatcher.php`, update the dispatch method to pass endpoint_id in the webhook meta. Replace the `WebhookCall::create()` chain with:

```php
WebhookCall::create()
    ->url($endpoint->url)
    ->payload([
        'event' => $event,
        'timestamp' => now()->toIso8601String(),
        'data' => $payload,
    ])
    ->useSecret($endpoint->secret)
    ->meta(['endpoint_id' => $endpoint->id])
    ->dispatch();
```

- [ ] **Step 4: Register listeners in AppServiceProvider**

In `app/Providers/AppServiceProvider.php`, add these imports at the top:

```php
use App\Listeners\RecordWebhookFailure;
use App\Listeners\RecordWebhookSuccess;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
```

In the `boot()` method, add:

```php
Event::listen(WebhookCallSucceededEvent::class, RecordWebhookSuccess::class);
Event::listen(WebhookCallFailedEvent::class, RecordWebhookFailure::class);
```

- [ ] **Step 5: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add app/Listeners/RecordWebhookSuccess.php app/Listeners/RecordWebhookFailure.php app/Services/WebhookDispatcher.php app/Providers/AppServiceProvider.php
git commit -m "feat(webhooks): add circuit breaker listeners for webhook success/failure"
```

---

### Task 6: Create controller and form requests

**Files:**
- Create: `app/Http/Controllers/Settings/WebhooksController.php`
- Create: `app/Http/Requests/Settings/StoreWebhookEndpointRequest.php`
- Create: `app/Http/Requests/Settings/UpdateWebhookEndpointRequest.php`

- [ ] **Step 1: Create StoreWebhookEndpointRequest**

```bash
php artisan make:request Settings/StoreWebhookEndpointRequest --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class StoreWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization !== null
            && $this->user()?->canInOrganization('org.webhooks.manage', $organization);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
```

- [ ] **Step 2: Create UpdateWebhookEndpointRequest**

```bash
php artisan make:request Settings/UpdateWebhookEndpointRequest --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization !== null
            && $this->user()?->canInOrganization('org.webhooks.manage', $organization);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
```

- [ ] **Step 3: Create the controller**

```bash
php artisan make:controller Settings/WebhooksController --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreWebhookEndpointRequest;
use App\Http\Requests\Settings\UpdateWebhookEndpointRequest;
use App\Models\WebhookEndpoint;
use App\Services\TenantContext;
use App\Services\WebhookDispatcher;
use Harris21\Fuse\CircuitBreaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class WebhooksController extends Controller
{
    public function index(): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $endpoints = WebhookEndpoint::withoutGlobalScopes()
            ->where('organization_id', $organization->id)
            ->latest()
            ->get()
            ->map(function (WebhookEndpoint $endpoint): array {
                $breaker = new CircuitBreaker("webhook-{$endpoint->id}");

                $circuitState = 'healthy';
                if ($breaker->isOpen()) {
                    $circuitState = 'tripped';
                } elseif ($breaker->isHalfOpen()) {
                    $circuitState = 'recovering';
                }

                return [
                    'id' => $endpoint->id,
                    'url' => $endpoint->url,
                    'events' => $endpoint->events,
                    'is_active' => $endpoint->is_active,
                    'description' => $endpoint->description,
                    'last_called_at' => $endpoint->last_called_at?->toIso8601String(),
                    'circuit_state' => $circuitState,
                    'created_at' => $endpoint->created_at->toIso8601String(),
                ];
            });

        return Inertia::render('settings/webhooks/index', [
            'endpoints' => $endpoints,
            'eventGroups' => Inertia::once(fn (): array => config('webhooks.events', [])),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('settings/webhooks/create', [
            'eventGroups' => config('webhooks.events', []),
        ]);
    }

    public function store(StoreWebhookEndpointRequest $request): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        WebhookEndpoint::withoutGlobalScopes()->create([
            'organization_id' => $organization->id,
            'url' => $request->validated('url'),
            'events' => $request->validated('events'),
            'description' => $request->validated('description'),
            'is_active' => $request->validated('is_active', true),
            'secret' => Str::random(32),
            'created_by' => $request->user()?->id,
        ]);

        return to_route('settings.webhooks.index')->with('success', 'Webhook endpoint created.');
    }

    public function edit(WebhookEndpoint $webhook): Response
    {
        $organization = TenantContext::get();
        abort_if(! $organization || $webhook->organization_id !== $organization->id, 403);

        return Inertia::render('settings/webhooks/edit', [
            'endpoint' => [
                'id' => $webhook->id,
                'url' => $webhook->url,
                'events' => $webhook->events,
                'is_active' => $webhook->is_active,
                'description' => $webhook->description,
            ],
            'eventGroups' => config('webhooks.events', []),
        ]);
    }

    public function update(UpdateWebhookEndpointRequest $request, WebhookEndpoint $webhook): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_if(! $organization || $webhook->organization_id !== $organization->id, 403);

        $webhook->update($request->validated());

        return to_route('settings.webhooks.index')->with('success', 'Webhook endpoint updated.');
    }

    public function destroy(WebhookEndpoint $webhook): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_if(! $organization || $webhook->organization_id !== $organization->id, 403);

        $webhook->delete();

        return to_route('settings.webhooks.index')->with('success', 'Webhook endpoint deleted.');
    }

    public function testPing(WebhookEndpoint $webhook, WebhookDispatcher $dispatcher): JsonResponse
    {
        $organization = TenantContext::get();
        abort_if(! $organization || $webhook->organization_id !== $organization->id, 403);

        $result = $dispatcher->testPing($webhook);

        return response()->json($result);
    }

    public function resetCircuit(WebhookEndpoint $webhook): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_if(! $organization || $webhook->organization_id !== $organization->id, 403);

        $breaker = new CircuitBreaker("webhook-{$webhook->id}");
        $breaker->reset();

        return back()->with('success', 'Circuit breaker reset. Delivery will resume.');
    }

    public function regenerateSecret(WebhookEndpoint $webhook): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_if(! $organization || $webhook->organization_id !== $organization->id, 403);

        $webhook->update(['secret' => Str::random(32)]);

        return back()->with('success', 'Webhook secret regenerated.');
    }
}
```

- [ ] **Step 4: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Settings/WebhooksController.php app/Http/Requests/Settings/StoreWebhookEndpointRequest.php app/Http/Requests/Settings/UpdateWebhookEndpointRequest.php
git commit -m "feat(webhooks): add controller and form requests"
```

---

### Task 7: Add routes and sidebar navigation

**Files:**
- Modify: `routes/settings.php`
- Modify: `resources/js/layouts/settings/layout.tsx`

- [ ] **Step 1: Add routes to routes/settings.php**

At the top of `routes/settings.php`, add this import:

```php
use App\Http\Controllers\Settings\WebhooksController;
```

Inside the `Route::middleware(['auth', 'verified', 'tenant', 'permission:org.settings.manage'])` group (after the domains routes, around line 95), add:

```php
Route::get('settings/webhooks', [WebhooksController::class, 'index'])->name('settings.webhooks.index');
Route::get('settings/webhooks/create', [WebhooksController::class, 'create'])->name('settings.webhooks.create');
Route::post('settings/webhooks', [WebhooksController::class, 'store'])->name('settings.webhooks.store');
Route::get('settings/webhooks/{webhook}/edit', [WebhooksController::class, 'edit'])->name('settings.webhooks.edit');
Route::put('settings/webhooks/{webhook}', [WebhooksController::class, 'update'])->name('settings.webhooks.update');
Route::delete('settings/webhooks/{webhook}', [WebhooksController::class, 'destroy'])->name('settings.webhooks.destroy');
Route::post('settings/webhooks/{webhook}/test', [WebhooksController::class, 'testPing'])->name('settings.webhooks.test');
Route::post('settings/webhooks/{webhook}/reset-circuit', [WebhooksController::class, 'resetCircuit'])->name('settings.webhooks.reset-circuit');
Route::post('settings/webhooks/{webhook}/regenerate-secret', [WebhooksController::class, 'regenerateSecret'])->name('settings.webhooks.regenerate-secret');
```

- [ ] **Step 2: Generate Wayfinder routes**

```bash
php artisan wayfinder:generate
```

- [ ] **Step 3: Add sidebar nav item**

In `resources/js/layouts/settings/layout.tsx`, add the import at the top with the other route imports:

```typescript
import { index as indexWebhooks } from '@/routes/settings/webhooks';
```

Add the `Webhook` lucide-react icon to the imports:

```typescript
import { Webhook } from 'lucide-react';
```

Add the nav item in the `sidebarNavItems` array, after the "Audit log" entry (around line 103):

```typescript
{
    title: 'Webhooks',
    href: indexWebhooks(),
    icon: Webhook,
    dataPan: 'settings-nav-webhooks',
    requiresOrgAdmin: true,
},
```

- [ ] **Step 4: Run Pint and check TypeScript**

```bash
vendor/bin/pint --dirty --format agent
npx tsc --noEmit
```

- [ ] **Step 5: Commit**

```bash
git add routes/settings.php resources/js/layouts/settings/layout.tsx
git commit -m "feat(webhooks): add routes and sidebar navigation"
```

---

### Task 8: Create Inertia pages — Index

**Files:**
- Create: `resources/js/pages/settings/webhooks/index.tsx`

- [ ] **Step 1: Create the index page**

Create `resources/js/pages/settings/webhooks/index.tsx`:

```tsx
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import SettingsLayout from '@/layouts/settings/layout';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Plus, TestTube, RotateCcw, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface WebhookEndpoint {
    id: number;
    url: string;
    events: string[];
    is_active: boolean;
    description: string | null;
    last_called_at: string | null;
    circuit_state: 'healthy' | 'tripped' | 'recovering';
    created_at: string;
}

interface Props extends SharedData {
    endpoints: WebhookEndpoint[];
    eventGroups: Record<string, Record<string, string>>;
}

const circuitBadge = {
    healthy: { label: 'Healthy', variant: 'default' as const, className: 'bg-emerald-500/10 text-emerald-500' },
    tripped: { label: 'Tripped', variant: 'destructive' as const, className: '' },
    recovering: { label: 'Recovering', variant: 'outline' as const, className: 'border-amber-500 text-amber-500' },
};

export default function WebhooksIndex() {
    const { endpoints } = usePage<Props>().props;
    const [testingId, setTestingId] = useState<number | null>(null);
    const [testResult, setTestResult] = useState<Record<number, { status?: number; error?: string; time_ms: number } | null>>({});

    const handleTest = async (id: number) => {
        setTestingId(id);
        setTestResult((prev) => ({ ...prev, [id]: null }));

        try {
            const response = await fetch(route('settings.webhooks.test', { webhook: id }), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                },
            });
            const result = await response.json();
            setTestResult((prev) => ({ ...prev, [id]: result }));
        } catch {
            setTestResult((prev) => ({ ...prev, [id]: { error: 'Request failed', time_ms: 0 } }));
        } finally {
            setTestingId(null);
        }
    };

    const handleDelete = (id: number) => {
        if (!confirm('Are you sure you want to delete this webhook endpoint?')) return;
        router.delete(route('settings.webhooks.destroy', { webhook: id }));
    };

    const handleResetCircuit = (id: number) => {
        if (!confirm('This will re-enable automatic delivery to this endpoint. Continue?')) return;
        router.post(route('settings.webhooks.reset-circuit', { webhook: id }));
    };

    return (
        <SettingsLayout>
            <Heading
                title="Webhooks"
                description="Send real-time event notifications to external services"
            />

            <div className="flex justify-end">
                <Button asChild size="sm" data-pan="webhooks-add">
                    <Link href={route('settings.webhooks.create')}>
                        <Plus className="mr-1 size-4" />
                        Add Webhook
                    </Link>
                </Button>
            </div>

            {endpoints.length === 0 ? (
                <div className="text-muted-foreground py-12 text-center text-sm">
                    No webhook endpoints configured yet.
                </div>
            ) : (
                <div className="space-y-3">
                    {endpoints.map((endpoint) => {
                        const badge = endpoint.is_active
                            ? circuitBadge[endpoint.circuit_state]
                            : { label: 'Disabled', variant: 'secondary' as const, className: '' };
                        const result = testResult[endpoint.id];

                        return (
                            <div key={endpoint.id} className="bg-muted/50 rounded-lg border p-4">
                                <div className="flex items-start justify-between gap-4">
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-2">
                                            <p className="truncate font-mono text-sm">{endpoint.url}</p>
                                            <Badge variant={badge.variant} className={badge.className}>
                                                {badge.label}
                                            </Badge>
                                        </div>
                                        {endpoint.description && (
                                            <p className="text-muted-foreground mt-1 text-sm">{endpoint.description}</p>
                                        )}
                                        <p className="text-muted-foreground mt-1 text-xs">
                                            {endpoint.events.length} event{endpoint.events.length !== 1 ? 's' : ''}
                                            {endpoint.last_called_at && (
                                                <> &middot; Last called {new Date(endpoint.last_called_at).toLocaleDateString()}</>
                                            )}
                                        </p>
                                        {result && (
                                            <p className={`mt-1 text-xs ${result.error ? 'text-destructive' : 'text-emerald-500'}`}>
                                                {result.error
                                                    ? `Error: ${result.error}`
                                                    : `${result.status} OK in ${result.time_ms}ms`}
                                            </p>
                                        )}
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => handleTest(endpoint.id)}
                                            disabled={testingId === endpoint.id}
                                            title="Test ping"
                                            data-pan="webhooks-test"
                                        >
                                            <TestTube className="size-4" />
                                        </Button>
                                        {endpoint.circuit_state !== 'healthy' && endpoint.is_active && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() => handleResetCircuit(endpoint.id)}
                                                title="Reset circuit"
                                                data-pan="webhooks-reset-circuit"
                                            >
                                                <RotateCcw className="size-4" />
                                            </Button>
                                        )}
                                        <Button variant="ghost" size="icon" asChild data-pan="webhooks-edit">
                                            <Link href={route('settings.webhooks.edit', { webhook: endpoint.id })}>
                                                <Pencil className="size-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => handleDelete(endpoint.id)}
                                            className="text-destructive hover:text-destructive"
                                            data-pan="webhooks-delete"
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </SettingsLayout>
    );
}
```

- [ ] **Step 2: Build frontend to check for errors**

```bash
npm run build
```

Expected: Build succeeds. Fix any TypeScript errors if they appear.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/settings/webhooks/index.tsx
git commit -m "feat(webhooks): add index page with status indicators and test ping"
```

---

### Task 9: Create Inertia pages — Create and Edit

**Files:**
- Create: `resources/js/pages/settings/webhooks/create.tsx`
- Create: `resources/js/pages/settings/webhooks/edit.tsx`

- [ ] **Step 1: Create the create page**

Create `resources/js/pages/settings/webhooks/create.tsx`:

```tsx
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import SettingsLayout from '@/layouts/settings/layout';
import { type SharedData } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';

interface Props extends SharedData {
    eventGroups: Record<string, Record<string, string>>;
}

export default function WebhooksCreate() {
    const { eventGroups } = usePage<Props>().props;

    const form = useForm({
        url: '',
        events: [] as string[],
        description: '',
        is_active: true,
    });

    const toggleEvent = (event: string) => {
        const events = form.data.events.includes(event)
            ? form.data.events.filter((e) => e !== event)
            : [...form.data.events, event];
        form.setData('events', events);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(route('settings.webhooks.store'));
    };

    return (
        <SettingsLayout>
            <Heading title="Add Webhook" description="Configure a new webhook endpoint" />

            <form onSubmit={submit} className="space-y-6">
                <div className="space-y-2">
                    <Label htmlFor="url">URL</Label>
                    <Input
                        id="url"
                        type="url"
                        placeholder="https://example.com/webhooks"
                        value={form.data.url}
                        onChange={(e) => form.setData('url', e.target.value)}
                    />
                    <InputError message={form.errors.url} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="description">Description (optional)</Label>
                    <Input
                        id="description"
                        placeholder="e.g., Zapier Production"
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                    />
                    <InputError message={form.errors.description} />
                </div>

                <div className="space-y-3">
                    <Label>Events</Label>
                    <InputError message={form.errors.events} />
                    {Object.entries(eventGroups).map(([group, events]) => (
                        <div key={group} className="space-y-2">
                            <p className="text-muted-foreground text-xs font-medium uppercase tracking-wider">{group}</p>
                            {Object.entries(events).map(([event, description]) => (
                                <label key={event} className="flex items-start gap-3 py-1">
                                    <Checkbox
                                        checked={form.data.events.includes(event)}
                                        onCheckedChange={() => toggleEvent(event)}
                                    />
                                    <div>
                                        <p className="font-mono text-sm">{event}</p>
                                        <p className="text-muted-foreground text-xs">{description}</p>
                                    </div>
                                </label>
                            ))}
                        </div>
                    ))}
                </div>

                <div className="flex items-center gap-3">
                    <Switch
                        id="is_active"
                        checked={form.data.is_active}
                        onCheckedChange={(checked) => form.setData('is_active', checked)}
                    />
                    <Label htmlFor="is_active">Active</Label>
                </div>

                <div className="flex gap-3">
                    <Button type="submit" disabled={form.processing} data-pan="webhooks-save">
                        Create Webhook
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={route('settings.webhooks.index')}>Cancel</Link>
                    </Button>
                </div>
            </form>
        </SettingsLayout>
    );
}
```

- [ ] **Step 2: Create the edit page**

Create `resources/js/pages/settings/webhooks/edit.tsx`:

```tsx
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import SettingsLayout from '@/layouts/settings/layout';
import { type SharedData } from '@/types';
import { Link, router, useForm, usePage } from '@inertiajs/react';

interface EndpointData {
    id: number;
    url: string;
    events: string[];
    is_active: boolean;
    description: string | null;
}

interface Props extends SharedData {
    endpoint: EndpointData;
    eventGroups: Record<string, Record<string, string>>;
}

export default function WebhooksEdit() {
    const { endpoint, eventGroups } = usePage<Props>().props;

    const form = useForm({
        url: endpoint.url,
        events: endpoint.events,
        description: endpoint.description ?? '',
        is_active: endpoint.is_active,
    });

    const toggleEvent = (event: string) => {
        const events = form.data.events.includes(event)
            ? form.data.events.filter((e) => e !== event)
            : [...form.data.events, event];
        form.setData('events', events);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(route('settings.webhooks.update', { webhook: endpoint.id }));
    };

    const handleRegenerateSecret = () => {
        if (!confirm('Are you sure? Any integrations using the current secret will stop working.')) return;
        router.post(route('settings.webhooks.regenerate-secret', { webhook: endpoint.id }));
    };

    return (
        <SettingsLayout>
            <Heading title="Edit Webhook" description="Update webhook endpoint configuration" />

            <form onSubmit={submit} className="space-y-6">
                <div className="space-y-2">
                    <Label htmlFor="url">URL</Label>
                    <Input
                        id="url"
                        type="url"
                        value={form.data.url}
                        onChange={(e) => form.setData('url', e.target.value)}
                    />
                    <InputError message={form.errors.url} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="description">Description (optional)</Label>
                    <Input
                        id="description"
                        value={form.data.description}
                        onChange={(e) => form.setData('description', e.target.value)}
                    />
                    <InputError message={form.errors.description} />
                </div>

                <div className="space-y-3">
                    <Label>Events</Label>
                    <InputError message={form.errors.events} />
                    {Object.entries(eventGroups).map(([group, events]) => (
                        <div key={group} className="space-y-2">
                            <p className="text-muted-foreground text-xs font-medium uppercase tracking-wider">{group}</p>
                            {Object.entries(events).map(([event, description]) => (
                                <label key={event} className="flex items-start gap-3 py-1">
                                    <Checkbox
                                        checked={form.data.events.includes(event)}
                                        onCheckedChange={() => toggleEvent(event)}
                                    />
                                    <div>
                                        <p className="font-mono text-sm">{event}</p>
                                        <p className="text-muted-foreground text-xs">{description}</p>
                                    </div>
                                </label>
                            ))}
                        </div>
                    ))}
                </div>

                <div className="flex items-center gap-3">
                    <Switch
                        id="is_active"
                        checked={form.data.is_active}
                        onCheckedChange={(checked) => form.setData('is_active', checked)}
                    />
                    <Label htmlFor="is_active">Active</Label>
                </div>

                <div className="space-y-4">
                    <div className="flex gap-3">
                        <Button type="submit" disabled={form.processing} data-pan="webhooks-update">
                            Update Webhook
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={route('settings.webhooks.index')}>Cancel</Link>
                        </Button>
                    </div>

                    <div className="border-t pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={handleRegenerateSecret}
                            className="text-destructive"
                            data-pan="webhooks-regenerate-secret"
                        >
                            Regenerate Secret
                        </Button>
                        <p className="text-muted-foreground mt-1 text-xs">
                            This will invalidate the current signing secret. Update your integration after regenerating.
                        </p>
                    </div>
                </div>
            </form>
        </SettingsLayout>
    );
}
```

- [ ] **Step 3: Build frontend**

```bash
npm run build
```

Expected: Build succeeds.

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/settings/webhooks/create.tsx resources/js/pages/settings/webhooks/edit.tsx
git commit -m "feat(webhooks): add create and edit pages with grouped event selector"
```

---

### Task 10: Write feature tests for WebhooksController

**Files:**
- Create: `tests/Feature/Settings/WebhooksControllerTest.php`

- [ ] **Step 1: Create the test file**

Create `tests/Feature/Settings/WebhooksControllerTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    $this->organization->addMember($this->user, 'admin');
    TenantContext::set($this->organization);
    $this->actingAs($this->user);
});

test('index page renders with endpoints', function (): void {
    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->create();

    $this->get(route('settings.webhooks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/webhooks/index')
            ->has('endpoints', 1)
            ->has('eventGroups')
        );
});

test('create page renders', function (): void {
    $this->get(route('settings.webhooks.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/webhooks/create')
            ->has('eventGroups')
        );
});

test('store creates a webhook endpoint', function (): void {
    $this->post(route('settings.webhooks.store'), [
        'url' => 'https://example.com/webhook',
        'events' => ['user.created'],
        'description' => 'Test webhook',
        'is_active' => true,
    ])->assertRedirect(route('settings.webhooks.index'));

    $this->assertDatabaseHas('webhook_endpoints', [
        'organization_id' => $this->organization->id,
        'url' => 'https://example.com/webhook',
        'description' => 'Test webhook',
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);
});

test('store validates required fields', function (): void {
    $this->post(route('settings.webhooks.store'), [])
        ->assertSessionHasErrors(['url', 'events']);
});

test('update modifies an endpoint', function (): void {
    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->create();

    $this->put(route('settings.webhooks.update', $endpoint), [
        'url' => 'https://updated.com/webhook',
        'events' => ['user.deleted'],
        'description' => 'Updated',
        'is_active' => false,
    ])->assertRedirect(route('settings.webhooks.index'));

    $endpoint->refresh();
    expect($endpoint->url)->toBe('https://updated.com/webhook')
        ->and($endpoint->events)->toBe(['user.deleted'])
        ->and($endpoint->is_active)->toBeFalse();
});

test('destroy soft deletes an endpoint', function (): void {
    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->create();

    $this->delete(route('settings.webhooks.destroy', $endpoint))
        ->assertRedirect(route('settings.webhooks.index'));

    $this->assertSoftDeleted('webhook_endpoints', ['id' => $endpoint->id]);
});

test('test ping returns status', function (): void {
    Http::fake(['*' => Http::response('OK', 200)]);

    $endpoint = WebhookEndpoint::factory()
        ->for($this->organization)
        ->create();

    $this->postJson(route('settings.webhooks.test', $endpoint))
        ->assertOk()
        ->assertJsonStructure(['status', 'time_ms']);
});

test('cannot access endpoints from another organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $endpoint = WebhookEndpoint::factory()
        ->for($otherOrg)
        ->create();

    $this->get(route('settings.webhooks.edit', $endpoint))->assertForbidden();
    $this->put(route('settings.webhooks.update', $endpoint), [
        'url' => 'https://evil.com',
        'events' => ['user.created'],
    ])->assertForbidden();
    $this->delete(route('settings.webhooks.destroy', $endpoint))->assertForbidden();
});

test('unauthenticated user cannot access webhooks', function (): void {
    auth()->logout();

    $this->get(route('settings.webhooks.index'))
        ->assertRedirect(route('login'));
});
```

- [ ] **Step 2: Run the tests**

```bash
php artisan test --compact --filter=WebhooksControllerTest
```

Expected: All tests pass.

- [ ] **Step 3: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Settings/WebhooksControllerTest.php
git commit -m "test(webhooks): add feature tests for WebhooksController"
```

---

### Task 11: Sync permissions and run full test suite

- [ ] **Step 1: Sync permissions**

```bash
php artisan permission:sync
```

Expected: New `org.webhooks.view` and `org.webhooks.manage` permissions created.

- [ ] **Step 2: Register Pan analytics names**

In `app/Providers/AppServiceProvider.php`, in the `configurePan()` method (or wherever Pan analytics names are registered), add:

```php
'webhooks-add',
'webhooks-test',
'webhooks-reset-circuit',
'webhooks-edit',
'webhooks-delete',
'webhooks-save',
'webhooks-update',
'webhooks-regenerate-secret',
'settings-nav-webhooks',
```

- [ ] **Step 3: Run full test suite**

```bash
php artisan test --compact
```

Expected: All tests pass including the new webhook tests.

- [ ] **Step 4: Run Pint on all modified files**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 5: Commit**

```bash
git add app/Providers/AppServiceProvider.php
git commit -m "chore(webhooks): sync permissions and register Pan analytics"
```

---

### Task 12: Write documentation

**Files:**
- Create: `docs/developer/backend/webhooks.md`
- Create: `docs/user-guide/webhooks.md`

- [ ] **Step 1: Write developer documentation**

Create `docs/developer/backend/webhooks.md` covering:
- How the dispatch system works (WebhookDispatcher + spatie/webhook-server + Fuse)
- How to register new events from a module's ServiceProvider:
  ```php
  // In your module's ServiceProvider::boot()
  $existing = config('webhooks.events', []);
  $existing['Contacts'] = [
      'contact.created' => 'A new contact was created',
      'contact.updated' => 'A contact was updated',
      'contact.deleted' => 'A contact was deleted',
  ];
  config(['webhooks.events' => $existing]);
  ```
- How to dispatch webhooks from anywhere:
  ```php
  app(WebhookDispatcher::class)->dispatch('contact.created', $contact->toArray(), $contact->organization_id);
  ```
- Circuit breaker behavior: threshold, timeout, half-open probe, manual reset
- Fuse configuration in `config/webhooks.php`
- Testing webhooks in development (test ping, `WebhookCall::fake()`)

- [ ] **Step 2: Write admin user guide**

Create `docs/user-guide/webhooks.md` covering:
- What webhooks are and why they matter (Zapier, Make, custom integrations)
- Default events table:
  | Event | Description |
  |-------|-------------|
  | `user.created` | A new user registered |
  | `user.deleted` | A user was deleted |
  | `organization.updated` | Organization settings changed |
  | `invitation.sent` | An invitation was sent |
  | `invitation.accepted` | An invitation was accepted |
- Example payload structure
- How to create an endpoint, select events, test it
- Status indicators: Healthy (green), Recovering (amber), Tripped (red), Disabled (grey)
- How to reset a tripped circuit
- HMAC signing: "Every webhook is signed with your endpoint's secret using HMAC-SHA256. Verify the `Signature` header to confirm the webhook came from your app."

- [ ] **Step 3: Commit**

```bash
git add docs/developer/backend/webhooks.md docs/user-guide/webhooks.md
git commit -m "docs(webhooks): add developer and admin documentation"
```
