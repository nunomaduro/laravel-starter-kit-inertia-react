# SEO & Monitoring

SEO, sitemap, error tracking, analytics, and Slack notifications.

## Overview

| Area | Package / Config | Notes |
|------|------------------|--------|
| **Sitemap** | `spatie/laravel-sitemap` | `sitemap:generate` command; daily schedule |
| **SEO meta / OG** | `App\Settings\SeoSettings` | Filament Manage SEO; `app.blade` + Inertia shared `seo` |
| **robots.txt** | Route `robots` | Dynamic; includes `Sitemap: {app.url}/sitemap.xml` |
| **Legal pages** | Inertia | `/legal/terms`, `/legal/privacy`; linked from welcome footer |
| **Public content** | Inertia | `/blog`, `/changelog`, `/help`; linked from welcome nav; sitemap includes index URLs |
| **Sentry** | `sentry/sentry-laravel` | Set `SENTRY_LARAVEL_DSN` to enable |
| **GA4** | `spatie/laravel-analytics` | Server-side; `ANALYTICS_PROPERTY_ID`, credentials path |
| **Slack** | `laravel/slack-notification-channel` | `SLACK_WEBHOOK_URL`; failed-job alerts |

## Sitemap

- **Command**: `php artisan sitemap:generate`
- **Output**: `public/sitemap.xml`
- **Schedule**: Daily (in `routes/console.php`)
- **URLs included**: home, contact, login, register, legal/terms, legal/privacy, blog, changelog, help (when routes exist)

Add more URLs in `App\Console\Commands\GenerateSitemap` (e.g. dynamic models implementing `Spatie\Sitemap\Contracts\Sitemapable`).

## SEO meta and Open Graph

- **Settings**: `App\Settings\SeoSettings` (meta title, meta description, OG image); edited in Filament → Settings → Manage SEO.
- **Server-side**: View composer for `app` view injects `$seo` into `resources/views/app.blade.php` (meta description, `og:type`, `og:title`, `og:description`, `og:url`, `og:image`, Twitter card).
- **Inertia**: `HandleInertiaRequests` shares `seo` so the frontend can use default meta/OG values.

## robots.txt

- **Route**: `GET robots.txt` (name: `robots`)
- **Response**: Plain text with `User-agent: *`, `Disallow:`, and `Sitemap: {APP_URL}/sitemap.xml`.

## Legal pages

- **Routes**: `legal.terms` → `legal/terms`, `legal.privacy` → `legal/privacy`
- **Pages**: `resources/js/pages/legal/terms.tsx`, `legal/privacy.tsx`
- **Links**: Welcome page footer uses `@/routes/legal` (terms, privacy).

## Public content (Blog, Changelog, Help)

- **Routes**: `blog.index`, `blog.show`, `changelog.index`, `help.index`, `help.show`, `help.rate`
- **Pages**: `resources/js/pages/blog/`, `changelog/`, `help/`
- **Links**: Welcome page nav uses `@/routes/blog`, `@/routes/changelog`, `@/routes/help`
- **Sitemap**: Index URLs `/blog`, `/changelog`, `/help` are added by `GenerateSitemap` when routes exist.

## Sentry

- **Config**: `config/sentry.php` (published)
- **Env**: `SENTRY_LARAVEL_DSN` (optional: `SENTRY_SAMPLE_RATE`, `SENTRY_TRACES_SAMPLE_RATE`)
- When DSN is set, exceptions are reported to Sentry.

## Google Analytics 4

- **Config**: `config/analytics.php`
- **Env**: `ANALYTICS_PROPERTY_ID`, `GOOGLE_ANALYTICS_CREDENTIALS_PATH` (default: `storage_path('app/analytics/service-account-credentials.json')`)
- Use `Spatie\Analytics\Facades\Analytics` for server-side queries (see package docs).
- **Filament**: `App\Filament\Widgets\Ga4OverviewWidget` shows a 7-day visitors/page views summary on the Billing Analytics (Revenue) dashboard when GA4 is configured; otherwise shows a "Not configured" card.

## Slack notifications

- **Config**: `config/services.php` → `slack.webhook_url` from `SLACK_WEBHOOK_URL`
- **Recipient**: `App\Support\SlackWebhookRecipient` (returns webhook URL for `routeNotificationForSlack()`)
- **Notification**: `App\Notifications\SlackCriticalAlertNotification` (title, body, level)
- **Example**: `SendSlackAlertOnJobFailed` listener on `Illuminate\Queue\Events\JobFailed`; sends to Slack when webhook is set.

To send ad hoc: `(new SlackWebhookRecipient)->notify(new SlackCriticalAlertNotification('Title', 'Body', 'error'));`

## For agents

- New public routes (e.g. legal, sitemap) must be added to `GenerateSitemap` if they should be in the sitemap.
- After adding routes, run `php artisan permission:sync-routes` when route-based permissions are enabled.
- Run `php artisan wayfinder:generate` after new web routes so frontend route helpers are updated.
