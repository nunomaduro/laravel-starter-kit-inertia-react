---
name: telescope
description: "Laravel Telescope debug dashboard (laravel/telescope v5). Activates when configuring or debugging with Telescope; working with watchers, pruning, gates, or when the user mentions Telescope, debug dashboard, requests, queries, jobs, mail monitoring."
license: MIT
metadata:
  author: project
---

# Laravel Telescope

## When to apply

Activate when:

- Configuring Telescope watchers, pruning, or gates
- Debugging requests, queries, jobs, mail, or exceptions
- User mentions Telescope, debug dashboard, or monitoring

## Package

**laravel/telescope** v5 — debug dashboard at `/telescope` (local only; admin panel access).

## Installation / config

- **Composer**: `laravel/telescope` is installed as dev dependency.
- **Local-only**: Registered only when `APP_ENV=local`; in `dont-discover` for production.
- **Config**: `config/telescope.php` — watchers, pruning, etc.
- **Migrations**: `telescope_entries` table.

## Dashboard

- **URL**: `/telescope` (when `APP_ENV=local` or when gate allows).
- **Gate**: `viewTelescope` in `TelescopeServiceProvider::gate()` — allows users who `can('access admin panel')` (same as Horizon).

## Watchers

Configured in `config/telescope.php`: Request, Query, Command, Job, Exception, Log, Mail, Cache, Event, Model, Schedule.

## Filtering

- **Local**: All entries recorded.
- **Non-local**: Only reportable exceptions, failed requests/jobs, scheduled tasks, slow queries, monitored tags.

## Tagging

Request entries tagged with `status:{code}`. Add tags to monitoring list for production debugging.

## Pruning

- `telescope:prune` scheduled daily in `routes/console.php`.
- Response cache excludes `telescope/*`.

## Documentation

- Full guide: `docs/developer/backend/telescope.md`
- Backend at-a-glance: `docs/developer/backend/README.md` (Telescope bullet)
- [Laravel Telescope docs](https://laravel.com/docs/12.x/telescope)
