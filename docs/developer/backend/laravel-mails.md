# Laravel Mails (mail tracking)

[backstage/laravel-mails](https://github.com/backstagephp/laravel-mails) logs **all sent emails** to the database and can receive **delivery and bounce events** from providers (e.g. Postmark, Mailgun) via webhooks. This is separate from [Database Mail](database-mail.md), which stores *templates* and sends event-driven emails; Laravel Mails *tracks* every message your app sends.

## What it does

- **Logging:** Every outgoing mail is recorded (subject, from, to, html/text, attachments) when `MessageSent` fires. Controlled by `config/mails.php` → `logging.enabled` (default true) and `logging.attributes`.
- **Events:** Webhooks from the mail provider update `mail_events` (delivered, bounced, complained, etc.). Configure your provider to send webhooks to the routes under `config/mails.php` → `webhooks.routes.prefix` (e.g. `/webhooks/mails/...`).
- **Pruning:** Old mails can be pruned automatically (`config/mails.php` → `database.pruning`).
- **Notifications:** Optional alerts (e.g. Discord, Slack, mail) on hard/soft bounces and bouncerate thresholds (`config/mails.php` → `events`, `notifications`).

## Configuration

- **config/mails.php:** Models, table names, pruning, logging attributes, encryption (`MAILS_ENCRYPTED`), attachment storage, webhook prefix, event notifications.
- **Environment:** `MAILS_LOGGING_ENABLED` (default true), `MAILS_ENCRYPTED` (default true), `MAILS_LOGGING_ATTACHMENTS_ENABLED`, `MAILS_QUEUE_WEBHOOKS`.
- **Provider credentials:** For webhooks, add Postmark/Mailgun (or supported driver) to `config/services.php` and run `php artisan mail:webhooks [postmark|mailgun]` to register routes.

## Relation to Database Mail

- **Database Mail** = event → template → send (content and recipients from DB templates).
- **Laravel Mails** = log every send + optional delivery/bounce feedback. All mail sent by the app (including mail sent via Database Mail) is logged by Laravel Mails when logging is enabled.

## Filament UI (optional)

The [Filament Mails](https://github.com/backstagephp/filament-mails) plugin (`backstage/filament-mails`) provides an admin UI to browse sent mails and events. Install with Composer and register the plugin in your Filament panel if you want a UI; the base package only requires config and migrations.
