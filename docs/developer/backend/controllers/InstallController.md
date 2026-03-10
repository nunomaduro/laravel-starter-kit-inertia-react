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

## Routes

- `install`: `GET /install` — displays the step resolved by `resolveStep()`
- `install.store`: `POST /install` — dispatches to the appropriate step handler

Both routes are protected by the `EnsureNotInstalled` middleware (skipped once the app is marked as installed).

## Steps

### Required (cannot be skipped)

| Step | What it does |
|------|--------------|
| `database` | Configures `DB_CONNECTION` in `.env`; verifies connectivity |
| `migrate` | Runs `php artisan migrate --force` and `db:seed` (essential data) |
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
