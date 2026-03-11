# Deployment Guide

This guide covers deploying the Laravel + Inertia React application to production: environment configuration, assets, caching, and hardening.

## Health and readiness

For load balancers, Kubernetes, or other orchestrators, use the health endpoints as the health or readiness URL for probes.

- **GET `/up`** (liveness) — Returns `200` with `{"status":"ok","checks":{"app":true,"database":true}}` when the app and database are reachable. Returns `503` with `"status":"degraded"` if the database check fails. Use for Kubernetes liveness probe or simple load balancer health. No authentication required.
- **GET `/up/ready`** (readiness) — Returns `200` with `{"status":"ok","checks":{"app":true,"database":true,"cache":true}}` when the app, database, and cache are reachable. Returns `503` if any check fails. Use for Kubernetes readiness probe so traffic is not sent until cache (and thus the app) is ready. No authentication required.

Example Kubernetes probes: liveness `httpGet /up`, readiness `httpGet /up/ready`.

### Application health check (CLI)

For CI, local verification, or production runbooks, run the comprehensive health check:

```bash
php artisan app:health
```

Use `--json` for scriptable output. Exit code `0` = all checks passed; `1` = one or more failures. Use `--fail-on-warn` to treat warnings as failures (e.g. in CI). In production, you can run this from cron or a monitoring script and alert on non-zero exit.

## Laravel Cloud

When deploying to [Laravel Cloud](https://cloud.laravel.com/), use the same build steps (`composer install`, `npm ci`, `npm run build`) and run `php artisan migrate --force` in deploy commands. Migrations are compatible with managed Postgres (no `session_replication_role`; pgvector extension is skipped if the DB user cannot create it). Ensure `package-lock.json` is committed and in sync with `package.json` so `npm ci` succeeds. Set `APP_URL`, `APP_DEBUG=false`, and other production env vars in the Cloud dashboard; Cloud injects database and cache credentials automatically when you attach resources.

## Pre-deployment checklist

- [ ] Tests passing: `php artisan test`
- [ ] Code formatted: `vendor/bin/pint`
- [ ] Production env vars set (see below)
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Database migrations tested (e.g. in staging)
- [ ] HTTPS and secure cookies in production

## Environment configuration

### Critical production settings

Set **`APP_URL`** to the exact production URL (e.g. `https://yourdomain.com`). It is used for links, redirects, and asset URLs; an incorrect value can break password reset, emails, and API docs links.

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Use a strong app key (generate with php artisan key:generate)
APP_KEY=base64:...
```

### Session and cache

Use `database` or `redis` for session and cache in production. The **file** driver for cache, session, or queue is not supported in multi-server production; Redis (or equivalent) is required for Horizon and Reverb. Example:

```bash
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

For Redis:

```bash
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Optional: IP whitelist for admin

To restrict Filament (or other routes) to specific IPs, set:

```bash
IP_WHITELIST=203.0.113.10,198.51.100.0/24
```

Then apply the `ip.whitelist` middleware to the relevant route group or Filament panel. See [Middleware](#middleware) below.

## Asset compilation

```bash
npm ci
npm run build
```

Ensure `APP_URL` in `.env` matches the production domain so Vite-generated asset URLs are correct. Build output goes to `public/build/`.

## Response cache

When response cache is enabled (Settings > Performance or `responsecache.enabled`), only **guest GET** requests are cached. Admin, API, auth, and `pages`/`p/*` routes are excluded. Cache is invalidated by **TTL only** (default 7 days); there is no tag-based purge. To purge after updating public content (e.g. blog, changelog), run `php artisan responsecache:clear` or clear the application cache.

## Caching

After deployment, run:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Clear caches when you change config, routes, or views:

```bash
php artisan optimize:clear
# or individually: config:clear, route:clear, view:clear, cache:clear
```

## Web installer and express install

When the app is not yet installed (no completed setup wizard), the **web installer** at **GET `/install`** guides you through database, migrations, admin user, app settings, optional steps (tenancy, mail, billing, feature flags, etc.), and demo data. Use it for first-time setup in a browser.

**Availability:** Install routes (including express) are only available when **`APP_ENV`** is **`local`** or **`testing`**; in production or staging they return 404. They are rate-limited (10 requests per minute per IP). Progress files are deleted when status is read as `done` or `error`. Express returns 409 if already installed; invalid body returns 422.

**Express install** (POST `/install/express`) skips the wizard: it configures SQLite and `.env`, then runs migrations, essential seeders, creates a default admin (`admin@example.com` / `password`), and saves app/mail/setup settings in the background. The response is JSON `{ "progressFile": "install_progress_<uuid>.json" }`; poll **GET `/install/express/status?key=<progressFile>`** until `status` is `done` or `error`, then redirect to `redirect` (e.g. `/admin`).

Express accepts an optional JSON body to customize run:

| Parameter | Values | Effect |
|-----------|--------|--------|
| `tenancy` | `multi`, `single` | Single = one organization, no user org creation |
| `demo` | `none`, `minimal`, `full` | Minimal = users, organizations, content seeders; full = all demo modules |
| `single_org_name` | string | When `tenancy` is `single`, used as default organization name |
| `preset` | `saas`, `internal`, `ai_first` | If `tenancy`/`demo` are omitted: `internal` → single-tenant, no demo; `saas` → multi, no demo; `ai_first` → multi, minimal demo |

Example (single-tenant, no demo, custom org name):

```bash
curl -X POST -H "Content-Type: application/json" -d '{"tenancy":"single","demo":"none","single_org_name":"Acme Corp"}' https://yourapp.test/install/express
```

**Install presets** (step-by-step wizard): On the App step you can choose a preset (None, SaaS, Internal tool, AI-first). The preset prefills later steps (e.g. Internal → single-tenant default on Tenancy, hint to skip Billing, Registration unchecked on Feature flags). You can still change any value.

## First-run and post-deploy

- **Migrations**: Run `php artisan migrate --force` on first deploy and after pulling migration changes.
- **Seeding**: On first deploy (or when you need default data), run `php artisan db:seed`. This creates roles, permissions (including org permissions via `permission:sync` in `RolesAndPermissionsSeeder`), and optional data (e.g. gamification levels/achievements via `GamificationSeeder`). The main `DatabaseSeeder` runs the essential seeders.
- **Feature flags**: To turn all feature flags back to their default (e.g. all on) after a deploy, run: `php artisan features:reset-to-defaults`. Run this only when you intend to reset; it overwrites current feature state. Features listed in `GLOBALLY_DISABLED_MODULES` remain off regardless of reset.

## Queue and scheduler

If the app uses queues (e.g. personal data export, notifications):

- **Database driver**: Run a queue worker: `php artisan queue:work` (or use Supervisor). Restart after deploy: `php artisan queue:restart`.
- **Redis driver**: When `QUEUE_CONNECTION=redis`, use **Laravel Horizon** instead of `queue:work`: run `php artisan horizon` and supervise it (e.g. via Supervisor). Horizon manages workers and its own schedule (e.g. snapshot). Restart after deploy: `php artisan horizon:terminate`.

For scheduled tasks (e.g. `personal-data-export:clean`), add to the server crontab:

```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

If you use Horizon with Redis, the scheduler is still run by this cron entry; Horizon does not replace it for application scheduled tasks.

### Running the scheduler without cron

On PaaS or environments where cron is not available (e.g. Heroku, Render), use [spatie/laravel-cronless-schedule](https://github.com/spatie/laravel-cronless-schedule). Run a long-lived process:

```bash
php artisan schedule:run-cronless
```

This runs the same scheduler every minute without cron. See [Cronless schedule](backend/cronless-schedule.md) for options and deployment notes.

## Webhook security

Stripe (`POST /webhooks/stripe`), Paddle (`POST /webhooks/paddle`), and Lemon Squeezy (`POST /lemon-squeezy/webhook`) endpoints are excluded from CSRF. Each verifies the request signature (Stripe-Signature, Paddle-Signature, or Lemon Squeezy HMAC) before processing. Do not disable signature verification; keep webhook secrets in config and never expose them to the client.

## Security hardening

- **HTTPS**: Enforce TLS; set `APP_URL` to `https://`.
- **Cookies**: In production, ensure `SESSION_SECURE_COOKIE` and secure cookie options are enabled where applicable.
- **Headers**: The app uses `AdditionalSecurityHeaders` and CSP (Spatie); keep them enabled.
- **Admin IP restriction**: Use `IP_WHITELIST` and the `ip.whitelist` middleware on the Filament panel or admin route group when required.

## Middleware

- **EnforceIpWhitelist** (`ip.whitelist`): Restricts access by IP when `config('app.ip_whitelist')` is non-empty. Apply to route groups or Filament panel as needed.
- **ThrottleTwoFactorManagement**: Applied globally to web routes; rate-limits 2FA management endpoints (5 requests per minute per user).

## Composer

Production install:

```bash
composer install --optimize-autoloader --no-dev
```

## API (rate limiting and CORS)

- **Rate limiting**: The `/api/v1/*` routes use the default API throttle. To customize limits, configure the `api` rate limiter in `App\Providers\AppServiceProvider` or `bootstrap/app.php` and apply `throttle:api` (or a named limit) to the API route group.
- **CORS**: When the API is called from another origin (e.g. a separate SPA or mobile app), configure CORS. Laravel supports CORS via the `Illuminate\Http\Middleware\HandleCors` middleware (usually in the API middleware group). Publish and edit `config/cors.php` if needed, and set `allowed_origins` (or `allowed_origins_patterns`) to your front-end origin(s). Ensure `APP_URL` and any CORS origins use the correct scheme and domain.

## Troubleshooting

- **Vite manifest missing**: Run `npm run build` and ensure `public/build` is deployed.
- **403 on admin**: If using `ip.whitelist`, ensure your IP is in `IP_WHITELIST` or the middleware is not applied to that route.
- **Queued jobs not running**: Start a queue worker and ensure `QUEUE_CONNECTION` is not `sync` in production.
