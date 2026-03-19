# Spatie Laravel Health

**spatie/laravel-health** runs scheduled health checks (database, cache, disk, Horizon, Redis, queue, backups, schedule) and can notify you via Slack or mail when checks fail or warn.

## Overview

- **Composer**: `spatie/laravel-health` is installed as a dependency.
- **Checks**: Registered in `App\Providers\HealthServiceProvider` via `Health::checks([...])`.
- **Config**: `config/health.php` — result stores, notifications, throttle.
- **Storage**: Results are stored in the database (Eloquent health result history).

## Registered checks

- **Database** — connection check.
- **Cache** — read/write check.
- **Used disk space** — warn at 70%, fail at 90%.
- **Horizon** — Horizon is running.
- **Redis** — connection check.
- **Queue** — queue connectivity/size.
- **Backups** — integrates with spatie/laravel-backup.
- **Schedule** — scheduler ran recently.

## Notifications

- **Channels**: Configured in `config/health.php` under `notifications.notifications`.
- **Default**: This starter kit ships with **mail-only** notifications for `CheckFailedNotification` to avoid Slack payload incompatibilities between Spatie Health and newer Laravel Slack notification channel types.
- **Slack**: You can add `'slack'` back once you’ve verified the notification payload is compatible with your installed `laravel/slack-notification-channel` version (or route failures through your existing Slack alert pattern). Webhook URL resolution uses `HEALTH_SLACK_WEBHOOK_URL` with fallback to `SLACK_WEBHOOK_URL`.
- **Throttling**: Notifications are throttled (default once per hour) to avoid spam.

## Scheduling

- **Command**: `php artisan health:check`
- **Schedule**: Runs every 5 minutes in `routes/console.php` so results stay fresh and notifications can fire when checks fail.

## Optional status page

- The package can expose a simple status page/route; see Spatie docs. If enabled, protect it with the same admin gate and exclude from response cache if needed.

## Relation to app health endpoints

- **HealthController** (`/up`, `/ready`): Liveness and readiness for Kubernetes or load balancers; unchanged by this package.
- **AppHealthCommand** (`php artisan app:health`): Comprehensive CLI health for CI/runbooks; independent of Spatie Health.
- Spatie Health adds **scheduled** checks and **notifications**; the existing controller and command remain for probes and CLI.

## References

- [Spatie Laravel Health](https://spatie.be/docs/laravel-health/v1/introduction) — official documentation.
- Config: `config/health.php`
- Provider: `App\Providers\HealthServiceProvider`
