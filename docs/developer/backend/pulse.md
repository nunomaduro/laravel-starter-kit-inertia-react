# Laravel Pulse

**laravel/pulse** provides real-time application monitoring: requests, jobs, queue depth, server stats (CPU, memory, disk), exceptions, and usage. It replaces Telescope for observability and is first-party Laravel.

## Overview

- **Composer**: `laravel/pulse` is installed as a dependency.
- **URL**: `/pulse` (admin only via `viewPulse` gate).
- **Config**: `config/pulse.php` — storage, ingest, middleware, recording.
- **Storage**: Database (default) or Redis; tables created by `pulse` migrations.

## Authorization

- **Gate**: `viewPulse` is defined in `AppServiceProvider::boot()`.
- Only users who `can('access admin panel')` may access Pulse (same as Horizon and Waterline).

## Configuration

- **Storage**: Default driver is `database`; set `PULSE_STORAGE_DRIVER` and `PULSE_DB_CONNECTION` if needed.
- **Middleware**: Pulse routes use the middleware defined in `config/pulse.php` (e.g. `web`, `auth`); the `Authorize` middleware ensures the `viewPulse` gate passes.
- **Ignore paths**: Configure in `config/pulse.php` so Pulse’s own dashboard requests are not counted in application usage metrics.

## Response cache and Pan

- Response cache (`PublicContentCacheProfile`) excludes `pulse/*`.
- Dashboard quick link uses Pan key `dashboard-quick-pulse`.

## References

- [Laravel Pulse (Laravel 12.x)](https://laravel.com/docs/12.x/pulse) — official documentation.
- Config: `config/pulse.php`
