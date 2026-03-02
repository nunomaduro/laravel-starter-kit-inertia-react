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
