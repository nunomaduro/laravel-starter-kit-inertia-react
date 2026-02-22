---
name: database-mail
description: "Database-backed email templates with martinpetricko/laravel-database-mail. Activates when adding events that should send emails from DB templates; creating or editing mail templates; or when the user mentions database mail, email templates, event-triggered emails, or TriggersDatabaseMail."
license: MIT
metadata:
  author: project
---

# Database Mail (email templates)

## When to apply

Activate when:

- Adding a new Laravel event that should send an email (welcome, notification, etc.) from a database-defined template
- Creating or updating mail templates (subject/body, recipients, attachments)
- User asks about email templates, event-based emails, or database mail

## What Database Mail does

- Stores email templates in the DB (`mail_templates` table): name, event class, Blade subject/body, recipient keys, attachment keys, delay, is_active
- Links templates to events: when an event implementing `TriggersDatabaseMail` is dispatched, active templates for that event are sent
- Logs send failures to `mail_exceptions`; exceptions are reported via `LaravelDatabaseMail::logException()` and pruned daily

## Rules

1. **New event that should send DB-backed email:** Implement `TriggersDatabaseMail`, use `CanTriggerDatabaseMail`, define `getName()`, `getDescription()`, `getRecipients()` (map of keys to `Recipient`), and optionally `getAttachments()` (map of keys to `Attachment`). **Register the event** in `config/database-mail.php` under `'events' => [ ... ]`.
2. **Recipients:** `Recipient` constructor: `(string $name, Closure $recipient)`. Closure receives the event and returns user(s) or address(es) for `Mail::to()` (single or array).
3. **Templates:** Create via code (e.g. seeders) or Filament plugin. Subject and body are Blade; event public properties are available (e.g. `$user`).

## Implementation

**Event (example: UserCreated):**

- Implement `TriggersDatabaseMail`, use `CanTriggerDatabaseMail`.
- `getRecipients()`: e.g. `'user' => new Recipient('Registered user', fn (UserCreated $e): array => [$e->user])`.
- `getAttachments()`: return `[]` or map of keys to `Attachment` instances.
- Register in `config/database-mail.php`: `'events' => [ \App\Events\User\UserCreated::class ]`.

**Template (e.g. in seeder):**

- `MailTemplate::make()`, set name, event, subject, body, recipients (array of keys), attachments (array of keys), delay (optional), is_active.

**Artisan:** `mail:export`, `mail:import` (optional `--all`, `--search`, `--replace`).

## Documentation

- Full guide: `docs/developer/backend/database-mail.md`
- Backend at-a-glance: `docs/developer/backend/README.md` (Database Mail bullet)
