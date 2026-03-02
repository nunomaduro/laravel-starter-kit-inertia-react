## Codebase Patterns
- MCP routes are auto-loaded by `Laravel\Mcp\Server\McpServiceProvider` from `routes/ai.php` — no manual registration in `bootstrap/app.php` needed
- MCP uses JSON-RPC 2.0 protocol; test with `postJson('/mcp/api', [...])` using `actingAs($user, 'sanctum')`
- Supported MCP protocol versions: `2025-11-25`, `2025-06-18`, `2025-03-26`
- User factory: use `User::factory()->withoutTwoFactor()->create()` for tests that don't need 2FA
- Pint check: `vendor/bin/pint --dirty --format agent` (pre-commit hook runs automatically)
- Tests: `php artisan test --compact --filter=TestName`
- Test style: Pest with `declare(strict_types=1)`, no `uses()` needed for basic feature tests
- `.chief/` is in `.gitignore` — use `git add -f` when committing PRD/progress files
- CI workflow: Pint step uses `--test` flag (non-modifying), local pre-commit uses `--dirty --format agent`
- Pre-commit hook checks documentation completeness with `docs:sync --check` — new controllers require documentation before committing
- Scout search with `collection` driver: `Model::search($query)->query(fn ($b) => $b->published())->take(5)->get()` respects Eloquent scopes
- User model doesn't use BelongsToOrganization; scope to org members via `whereHas('organizations', ...)`
- BelongsToOrganization models (Post, HelpArticle, ChangelogEntry) auto-filter by TenantContext via global OrganizationScope
- Feature flag check: `FeatureHelper::isActiveForKey('blog')` — checks globally_disabled first, then Pennant
- CommandPalette uses custom DOM events (`open-command-palette`) for cross-component communication without context providers
- CommandPalette must be mounted in both sidebar and header layouts for Cmd+K to work everywhere
- PaymentGatewayManager is `final` — mock gateways by binding to the concrete class: `app()->instance(StripeGateway::class, $mock)` + `Cache::forget('billing.default_gateway_model')`
- Webhook tests: use `$this->call('POST', $url, [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json'], $rawPayload)` for raw JSON with custom headers
- Invoice model uses BelongsToOrganization (global scope) — use `->withoutGlobalScopes()` when querying in tests

---

## 2026-03-02 - US-001
- Verified MCP route registration: `routes/ai.php` is auto-loaded by the `laravel/mcp` service provider, `php artisan route:list --path=mcp` shows GET and POST `/mcp/api`
- Created `tests/Feature/McpRouteTest.php` with 3 tests (15 assertions):
  - MCP route is registered and reachable (initialize handshake via Sanctum auth)
  - MCP endpoint requires authentication (returns 401 without auth)
  - MCP endpoint rejects GET requests (returns 405)
- No changes needed to `bootstrap/app.php` or documentation — everything works out of the box
- Files changed: `tests/Feature/McpRouteTest.php` (new)
- **Learnings for future iterations:**
  - `laravel/mcp` v0.5.9 auto-discovers and loads `routes/ai.php` — no need for explicit route registration
  - MCP initialize request requires `protocolVersion`, `clientInfo` (name+version), `capabilities` in params
  - The MCP server responds with `serverInfo`, `capabilities`, `instructions`, and `protocolVersion` in the result
  - Use `actingAs($user, 'sanctum')` guard specifier for Sanctum-protected routes in tests
---

## 2026-03-02 - US-002
- Added Pint code style check step to `.github/workflows/tests.yml`
- Step runs `vendor/bin/pint --test` before documentation checks and test suite (fail-fast on style)
- Step name: "Check code style (Pint)" — descriptive and consistent with existing naming
- Files changed: `.github/workflows/tests.yml` (modified)
- **Learnings for future iterations:**
  - CI workflow already has Rector and PHPStan cache steps; Pint doesn't need caching (it's fast)
  - Pre-commit hook already runs `vendor/bin/pint --dirty --format agent` locally, CI uses `--test` flag for non-modifying check
  - `.chief/` directory is in `.gitignore` — use `git add -f` to force-add PRD files
  - Rector dry-run step already exists in CI; `vendor/bin/rector --dry-run` runs after Pint, before tests
---

## 2026-03-02 - US-003
- Verified Rector dry-run step is already present in `.github/workflows/tests.yml`
- Step "Check refactoring rules (Rector)" at line 108-109 runs `vendor/bin/rector --dry-run`
- Rector cache step (lines 88-93) caches `/tmp/rector` for faster runs
- Execution order: Pint (line 105) → Rector (line 108) → Docs/Tests (line 111+) — correct fail-fast ordering
- `rector.php` config is valid with Laravel sets, dead code, code quality, type declarations, privatization, early return
- Ran `vendor/bin/rector --dry-run` locally — passes cleanly
- No files changed — all acceptance criteria were already met
- **Learnings for future iterations:**
  - Rector config uses `/tmp/rector` cache dir with `FileCacheStorage` — matches CI cache path
  - Rector includes Laravel-specific rules via `RectorLaravel\Set\LaravelSetProvider` and multiple `LaravelSetList` sets
  - A custom rule `RemoveFinalFromAnonymousClassRector` exists in `app/Rector/`
  - `AddOverrideAttributeToOverriddenMethodsRector` is explicitly skipped
---

## 2026-03-02 - US-007
- Updated `docs/developer/backend/scout-typesense.md` with comprehensive Typesense documentation
- Added: environment variables table, all 4 searchable models (User, Post, HelpArticle, ChangelogEntry) with field schemas, "Adding a new searchable model" guide, indexing existing data commands, Typesense Cloud production setup, self-hosted setup, Scout driver management via Filament
- Ran `php artisan docs:sync` — manifest updated (date only, all items already documented)
- Files changed: `docs/developer/backend/scout-typesense.md` (updated), `docs/.manifest.json` (date update)
- **Learnings for future iterations:**
  - Only User has a Typesense collection schema in `config/scout.php`; Post, HelpArticle, ChangelogEntry have `toSearchableArray()` but no schema — they work with `collection` driver but need schemas for Typesense
  - Scout driver is managed via Filament Settings > Scout (`ScoutSettings`), which overlays the `.env` value
  - `docs:sync --check` confirms all items documented; `docs:sync` updates manifest and index files
  - Pre-commit hook auto-runs Pint, so documentation-only commits still pass
---

## 2026-03-02 - US-008
- Created `app/Http/Controllers/SearchController.php` — invokable controller with `__invoke()` method
- Added `GET /search` route in `routes/web.php` under `['auth', 'verified']` group with `tenant` middleware
- Searches across User, Post, HelpArticle, ChangelogEntry using Laravel Scout
- Users scoped to current org via `whereHas('organizations')`, other models scoped via BelongsToOrganization global scope
- Feature flags respected: blog, help, changelog checked via `FeatureHelper::isActiveForKey()`
- Only published content returned (Post, HelpArticle, ChangelogEntry use `->published()` scope)
- Results grouped by category with max 5 per category, 20 total
- Each result has: id, title, subtitle, url, type
- Created `tests/Feature/SearchTest.php` with 13 tests (54 assertions):
  - Requires authentication, empty query returns empty results
  - Searches users within current org, published posts, published help articles, published changelog entries
  - Filters by type parameter, respects tenant scope, returns correct structure
  - Limits to 5 per category, excludes posts when blog feature disabled
- Created `docs/developer/backend/controllers/SearchController.md`
- Updated `docs/.manifest.json` and `docs/developer/backend/controllers/README.md`
- Files changed: `app/Http/Controllers/SearchController.php` (new), `tests/Feature/SearchTest.php` (new), `routes/web.php` (modified), `docs/developer/backend/controllers/SearchController.md` (new), `docs/.manifest.json` (modified), `docs/developer/backend/controllers/README.md` (modified)
- **Learnings for future iterations:**
  - Pre-commit hook checks documentation — new controllers must have docs before commit
  - Scout `collection` driver searches in-memory; `->query()` callback adds Eloquent scopes
  - User model has no BelongsToOrganization trait; use `whereHas('organizations', ...)` for org scoping
  - BelongsToOrganization models auto-filter via OrganizationScope (global scope); no explicit `where` needed
  - `FeatureHelper::isActiveForKey('blog')` is the correct way to check feature flags programmatically
  - Test setup pattern: create user, create org, `addMember($user, 'admin')`, `TenantContext::set($org)`
  - `Feature::for($user)->activate(BlogFeature::class)` / `->deactivate()` to toggle flags in tests
---

## 2026-03-02 - US-009
- Extended `resources/js/components/command-dialog.tsx` with global search functionality:
  - Added debounced (300ms) API calls to `/search` endpoint with AbortController for cancellation
  - Search results grouped by category (Users, Posts, Help Articles, Changelog) with per-category icons
  - Each result shows title, subtitle, and type badge
  - Clicking a result navigates via `router.visit()`; arrow keys/Enter/Escape handled by cmdk
  - Empty state shows existing navigation shortcuts and account actions
  - Loading state shows animated skeleton placeholders
  - No results state shows "No results found for {query}"
  - Listens for custom `open-command-palette` event from header search button
- Modified `resources/js/components/ui/command.tsx`: added `shouldFilter` prop to `CommandDialog` (passed through to cmdk `Command`)
- Wired search button in `resources/js/components/app-header.tsx`: dispatches `open-command-palette` custom event on click, added `data-pan="global-search"`
- Added `CommandPalette` to `resources/js/layouts/app/app-header-layout.tsx` (was only in sidebar layout)
- Registered `global-search` Pan analytics name in `app/Providers/AppServiceProvider.php`
- Files changed: `command-dialog.tsx` (rewritten), `ui/command.tsx` (modified), `app-header.tsx` (modified), `app-header-layout.tsx` (modified), `AppServiceProvider.php` (modified)
- **Learnings for future iterations:**
  - cmdk's `shouldFilter` prop on `Command` disables client-side filtering — needed for server-side search results
  - `CommandDialog` wrapper doesn't pass arbitrary props to `Command` — had to explicitly add `shouldFilter`
  - Custom DOM events (`window.dispatchEvent(new CustomEvent(...))`) are the simplest way to communicate between disconnected components without context providers
  - `CommandPalette` was only mounted in sidebar layout, not header layout — both need it for Cmd+K to work everywhere
  - `useRef<ReturnType<typeof setTimeout>>(undefined)` is the correct way to type timeout refs in React 19 (no `null` initial)
  - AbortController pattern: create new controller per request, abort previous on new request, check `signal.aborted` before setting state
  - Pre-existing TS errors in command-dialog.tsx: `Mod+k` hotkey type, `item.href.url()` pattern, RouteDefinition types — all from original code
---

## 2026-03-02 - US-011
- Created `tests/Feature/Billing/StripeWebhookTest.php` with 12 tests (59 assertions):
  - Signature validation: rejects invalid signature, rejects empty signature header
  - customer.subscription.created: sets gateway_subscription_id on existing subscription, logs to WebhookLog with org_id
  - customer.subscription.updated: updates quantity from webhook data, handles canceled status
  - customer.subscription.deleted: sets canceled_at and ends_at, logs with processed=true
  - invoice.paid: creates Invoice model with correct amounts/currency, dispatches InvoicePaid event, idempotent (updates existing invoice)
  - invoice.payment_failed: updates existing invoice status to 'open'
  - WebhookLog: verifies all webhooks are logged with gateway='stripe' and payload
  - Unknown organization: handles gracefully, logs but marks processed=false
  - Unknown event type: handles gracefully, logs event_type but processed=false
- Files changed: `tests/Feature/Billing/StripeWebhookTest.php` (new)
- **Learnings for future iterations:**
  - `PaymentGatewayManager` is `final` — can't mock with Mockery. Instead, bind mock `PaymentGatewayInterface` to `StripeGateway::class` via `app()->instance(StripeGateway::class, $mock)` — the manager's `resolve()` uses `resolve($class)` which picks up the container binding
  - Must `Cache::forget('billing.default_gateway_model')` before each test to prevent cached `PaymentGatewayModel` from bypassing the mocked gateway
  - `$this->call('POST', '/webhooks/stripe', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json'], $payload)` is how to POST raw JSON bodies with custom headers in Laravel tests
  - Invoice model uses `BelongsToOrganization` trait (global OrganizationScope) — use `->withoutGlobalScopes()` for test assertions
  - `$org->planSubscriptions()->create([...])` creates a subscription linked to the organization (polymorphic subscriber)
  - WebhookLog is always created first (even before signature validation), then updated with event_type and processed=true on success
---

## 2026-03-02 - US-012
- Created `tests/Feature/Billing/PaddleWebhookTest.php` with 14 tests (66 assertions):
  - Signature validation: rejects invalid signature, rejects empty signature header
  - subscription.created: sets gateway_subscription_id on existing subscription, logs to WebhookLog with org_id
  - subscription.updated: updates quantity from webhook data, handles canceled status
  - subscription.canceled: sets canceled_at and ends_at from scheduled_change.effective_at, logs with processed=true
  - transaction.completed: creates Invoice with PDL- prefix, correct amounts/currency, idempotent (updates existing invoice)
  - transaction.payment_failed: creates FailedPaymentAttempt record, increments attempt_number on repeated failures
  - WebhookLog: verifies all webhooks are logged with gateway='paddle' and payload
  - Unknown organization: handles gracefully, logs but marks processed=false
  - Unknown event type: handles gracefully, logs event_type but processed=false
  - customer.created: handled as no-op, logs but not processed
- Files changed: `tests/Feature/Billing/PaddleWebhookTest.php` (new)
- **Learnings for future iterations:**
  - Paddle webhook signature format: `ts={timestamp};h1={hmac_sha256}` — different from Stripe's `t={timestamp},v1={signature}`
  - Paddle uses `Paddle-Signature` header (vs Stripe's `Stripe-Signature`)
  - Paddle payload structure: `{ event_type: "...", data: {...} }` — different from Stripe's `{ type: "...", data: { object: {...} } }`
  - Paddle controller resolves PaddleGateway directly via `resolve(PaddleGateway::class)` — mock with `app()->instance(PaddleGateway::class, $mock)`
  - Paddle invoice number format: `PDL-{first 20 chars of transaction_id}`
  - FailedPaymentAttempt uses `increment('attempt_number')` — doesn't use updateOrCreate, uses find-then-increment pattern
  - Paddle handles both new API (`subscription.created`) and legacy API (`subscription_created`) event names
---
