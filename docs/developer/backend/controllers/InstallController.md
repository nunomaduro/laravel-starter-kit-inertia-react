# InstallController

## Purpose

Web-based application installer — mirrors every phase of `php artisan app:install` through a step-by-step browser wizard at `/install`.

## Location

`app/Http/Controllers/InstallController.php`

## Methods

| Method | HTTP | Route | Purpose |
|--------|------|-------|---------|
| `show` | GET | `/install` | Renders the current installer step |
| `store` | POST | `/install` | Processes a submitted installer step |
| `express` | POST | `/install/express` | Express install: SQLite + defaults, optional body (tenancy, demo, single_org_name, site_name, preset, locale, fallback_locale, ai_provider, ai_api_key, ai_model) |
| `expressStatus` | GET | `/install/express/status` | Poll progress of express install (query `key` = progress filename); deletes file when status is `done` or `error` |
| `testConnection` | POST | `/install/test-connection` | Test DB/mail/search connection for a given step |
| `complete` | GET | `/install/complete` | One-time auto-login after express install (query `token` = encrypted payload); redirects to `/admin` |

## Routes

- `install`: `GET /install` — displays the step resolved by `resolveStep()`
- `install.store`: `POST /install` — dispatches to the appropriate step handler
- `install.express`: `POST /install/express` — runs express install; optional JSON body: `tenancy`, `demo`, `single_org_name`, `site_name`, `preset`, `locale`, `fallback_locale`, `ai_provider`, `ai_api_key`, `ai_model`
- `install.express.status`: `GET /install/express/status?key=...` — returns progress JSON
- `install.test-connection`: `POST /install/test-connection` — connection test for current step
- `install.complete`: `GET /install/complete?token=...` — one-time auto-login after express install; redirects to `/admin`

Install routes use **EnsureInstallEnvironment** (404 when `APP_ENV` is not `local` or `testing`), **throttle:install** (10/min per IP), and **EnsureNotInstalled** (redirect to `/admin` when setup is complete). The `complete` route is not behind EnsureNotInstalled so it can run after install for auto-login. Express returns **409** if already installed; invalid body returns **422**.

## Install presets

On the **App** step, an optional **Install preset** can be selected: None, SaaS, Internal tool, AI-first. The preset prefills or suggests values on later steps:

- **Tenancy**: Internal → single-organization selected by default.
- **Billing**: Internal → info hint to consider skipping billing.
- **Feature flags**: Internal → Registration, API access, and contact form unchecked by default.

Express install accepts a `preset` body param; when `tenancy`/`demo` are omitted, preset maps to: `internal` → single-tenant, no demo; `saas` → multi-tenant, no demo; `ai_first` → multi-tenant, minimal demo. Optional `site_name` sets AppSettings and mail from name; optional `ai_provider` (openrouter, openai, anthropic, groq, gemini, xai, deepseek, mistral, ollama) plus `ai_api_key` and `ai_model` persist to PrismSettings.

## Steps

### Required (cannot be skipped)

| Step | What it does |
|------|--------------|
| `database` | Configures `DB_CONNECTION` in `.env`; verifies connectivity |
| `migrate` | Runs `php artisan migrate --force` and essential seeders: RolesAndPermissions, Gamification, MailTemplates, Governor (non-fatal) |
| `admin` | Creates the super-admin user |
| `app` | Saves `AppSettings` (site name, URL, timezone, locale) |

### Optional (each has a Skip button)

| Step | Settings class saved |
|------|---------------------|
| `tenancy` | `TenancySettings` |
| `infrastructure` | `InfrastructureSettings` + `.env` |
| `mail` | `MailSettings` |
| `search` | `ScoutSettings` |
| `ai` | `PrismSettings` |
| `social` | `AuthSettings` (OAuth credentials) |
| `storage` | `FilesystemSettings` |
| `broadcasting` | `BroadcastingSettings` |
| `seo` | `SeoSettings` |
| `monitoring` | `MonitoringSettings` |
| `billing` | `BillingSettings` (default gateway, currency, trial days) |
| `features` | `FeatureFlagSettings` (globally disabled modules) |

### Final

| Step | What it does |
|------|--------------|
| `demo` | Runs selected module seeders; marks `SetupWizardSettings::completed = true` |

## Session State

Completed optional steps are tracked in `install_optional_done` session key (array of step names). `resolveStep()` walks through `OPTIONAL_STEPS` in order and returns the first incomplete one.

## Related

- CLI equivalent: `php artisan app:install`
- Middleware: `EnsureNotInstalled`
- View: `resources/views/install/index.blade.php`
- Settings classes: `AppSettings`, `TenancySettings`, `InfrastructureSettings`, `MailSettings`, `ScoutSettings`, `PrismSettings`, `AuthSettings`, `FilesystemSettings`, `BroadcastingSettings`, `SeoSettings`, `MonitoringSettings`, `SetupWizardSettings`
