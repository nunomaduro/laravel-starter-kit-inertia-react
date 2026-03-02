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
