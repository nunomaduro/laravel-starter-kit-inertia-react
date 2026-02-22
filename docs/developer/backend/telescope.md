# Laravel Telescope

**laravel/telescope** provides insight into requests, exceptions, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, and more. It is a development companion for debugging and monitoring.

## Installation

- **Composer**: `laravel/telescope` is installed as a dev dependency (`--dev`).
- **Local-only**: Telescope is registered only when `APP_ENV=local`. Providers are registered conditionally in `AppServiceProvider`; the package is in `dont-discover` so it is not auto-loaded in production.
- **Config**: `config/telescope.php` — watchers, pruning, etc.
- **Migrations**: `telescope_entries` table stores Telescope data.

## Dashboard

- **URL**: `/telescope` (when `APP_ENV=local` or when gate allows).
- **Authorization**: `App\Providers\TelescopeServiceProvider::gate()` defines `viewTelescope`; it allows users who `can('access admin panel')` (same as Horizon).

## Data Pruning

Without pruning, the `telescope_entries` table grows quickly. The `telescope:prune` command is scheduled daily in `routes/console.php`; by default it removes entries older than 24 hours. Use `--hours=48` to retain longer.

## Watchers

Watchers are configured in `config/telescope.php`. Available watchers include:

- **Request** — request, headers, session, response
- **Query** — SQL, bindings, execution time; slow queries tagged
- **Command** — Artisan command args, output, exit code
- **Job** — queued job data and status
- **Exception** — reportable exceptions and stack traces
- **Log** — log entries (default: error level and above)
- **Mail** — outgoing mail preview
- **Cache** — cache hits, misses, updates
- **Event** — dispatched events and listeners
- **Model** — Eloquent model changes
- **Schedule** — scheduled task output

## Filtering

`TelescopeServiceProvider::register()` uses `Telescope::filter()` to control what is recorded:

- **Local**: All entries are recorded.
- **Non-local**: Only reportable exceptions, failed requests, failed jobs, scheduled tasks, slow queries, and entries with monitored tags.

## Tagging

Request entries are tagged with `status:{code}` (e.g. `status:500`) so you can add tags to the monitoring list and filter by HTTP status in non-local environments.

## User avatars

The dashboard displays user avatars from the User model's Spatie Media Library `avatar` collection (thumb conversion). Users without an avatar fall back to Gravatar or no avatar.

## Integration

- **Response cache**: `telescope/*` is excluded from caching (see `PublicContentCacheProfile`).
- **Reverb**: `config/reverb.php` has `telescope_ingest_interval` for WebSocket metrics.

## References

- [Laravel Telescope (Laravel 12.x)](https://laravel.com/docs/12.x/telescope) — official documentation.
- Config: `config/telescope.php`
