# Laravel Horizon

Queue monitoring and workers are provided by **laravel/horizon**. Horizon uses Redis for the queue driver and exposes a dashboard for jobs, failed jobs, and metrics.

## Requirements

- **Redis** — Horizon requires the queue connection to be `redis`. Set `QUEUE_CONNECTION=redis` and ensure Redis is running (`REDIS_*` in `.env`).
- **Authorization** — Only users who can access the Filament admin panel (`access admin panel` permission) may view the Horizon dashboard.

## Configuration

- **Config**: `config/horizon.php` — path, Redis connection, middleware, supervisors, environments (e.g. `local`, `production`), trim times, metrics.
- **Gate**: `App\Providers\HorizonServiceProvider::gate()` defines `viewHorizon`; it allows users who `can('access admin panel')`.

## Running Horizon

- **Development**: `php artisan horizon` — runs in the foreground; use `horizon:listen` for file watching and auto-restart.
- **Production**: Run Horizon as a long-running process (e.g. Supervisor or systemd). Terminate gracefully with `php artisan horizon:terminate`.
- **Commands**: `horizon:status`, `horizon:pause`, `horizon:continue`, `horizon:snapshot` (for metrics).

## Environment

In `.env.example`:

- `QUEUE_CONNECTION=database` by default; set to `redis` when using Horizon.
- Optional: `HORIZON_PATH=horizon` (dashboard path), `HORIZON_PREFIX`, `HORIZON_NAME`, `HORIZON_DOMAIN`.

## Dashboard

With Horizon running and `QUEUE_CONNECTION=redis`, visit `/horizon` (or `HORIZON_PATH`) when logged in as a user with admin panel access. The dashboard shows recent jobs, failed jobs, and queue metrics.

## Related

- **Waterline** — Workflow monitoring UI at `/waterline` (admin only). Use it to inspect runs of [Durable Workflow](./durable-workflow.md) workflows. Same authorization as Horizon (`access admin panel`).

## References

- [Laravel Horizon](https://laravel.com/docs/horizon) — official documentation.
