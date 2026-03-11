# Database Mail (email templates)

[Laravel Database Mail](https://github.com/MartinPetricko/laravel-database-mail) (martinpetricko/laravel-database-mail) stores **email templates in the database**, links them to **events**, and sends those emails when the events are dispatched. Templates use Blade for subject and body; recipients and optional attachments are defined on the event.

## How it works

- **Events:** Implement `TriggersDatabaseMail` and use `CanTriggerDatabaseMail` on any Laravel event. Define `getName()`, `getDescription()`, `getRecipients()`, and optionally `getAttachments()`. Register the event in `config/database-mail.php` under `'events' => [...]`.
- **Templates:** Stored in `mail_templates` (name, event class, subject, body, recipients keys, attachments keys, delay, is_active). When the event is dispatched, the package finds active templates for that event and sends the mail to the configured recipients.
- **Exceptions:** Sending failures are logged to `mail_exceptions` and reported via `LaravelDatabaseMail::logException()` (registered in `bootstrap/app.php`). Old exceptions are pruned daily via `model:prune` in `routes/console.php`.

## Configuration

- **config/database-mail.php:** `register_event_listener` (default true), `prune_exceptions_period`, models, `event_mail`, resolvers, and **`events`** — list of event class names that trigger database mail.
- **Bootstrap:** In `bootstrap/app.php`, `DatabaseMailException` is reported so that `LaravelDatabaseMail::logException($e)` is called.
- **Schedule:** `routes/console.php` runs `model:prune` daily for `MailException` to remove old mail exceptions.

## Creating an event that triggers database mail

1. Implement `TriggersDatabaseMail` and use `CanTriggerDatabaseMail` on the event.
2. Public properties on the event are available as Blade variables in the template (e.g. `$user`, `$emailVerificationUrl`).
3. Implement:
   - `getName(): string` — label for the event in the UI.
   - `getDescription(): ?string` — optional description.
   - `getRecipients(): array` — map of recipient keys to `Recipient` instances (closure returns users or addresses for `Mail::to()`).
   - `getAttachments(): array` — optional map of attachment keys to `Attachment` instances (closure returns `Illuminate\Mail\Attachment` or array of them).
4. Register the event in `config/database-mail.php` under `'events' => [ \App\Events\YourEvent::class ]`.

Example (see `App\Events\User\UserCreated`):

- Recipient key `'user'` → `new Recipient('Registered user', fn (UserCreated $event): array => [$event->user])`.
- Template stores `recipients => ['user']` and optional `attachments => []`.

## Creating mail templates

Templates can be created in code (e.g. seeders) or via a Filament plugin ([Filament Database Mail](https://filamentphp.com/plugins/martin-petricko-database-mail)).

**Programmatically:**

```php
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

$t = MailTemplate::make();
$t->name = 'Welcome email';
$t->event = \App\Events\User\UserCreated::class;
$t->subject = 'Welcome {{ $user->name }}';
$t->body = '<h1>Hi {{ $user->name }}</h1><p>Thanks for signing up.</p>';
$t->recipients = ['user'];
$t->attachments = [];
$t->delay = null; // or e.g. '1 hour'
$t->is_active = true;
$t->save();
```

Subject and body are Blade; use the event’s public properties (e.g. `$user`).

## Import / export

- **Export:** `php artisan mail:export` — exports templates to JSON.
- **Import:** `php artisan mail:import` — imports from JSON. Options: `--all`, `--search`, `--replace` (e.g. replace localhost with production URL in seeders).

## Database

- **mail_templates:** id, name, event, subject, body, recipients (JSON), attachments (JSON), delay, is_active, timestamps.
- **mail_exceptions:** id, mail_template_id, event payload, exception message, etc.; pruned by schedule.

## Where it’s used in this app

All transactional emails are sent via Database Mail. Events and templates:

- **UserCreated** — welcome email; recipient `user`.
- **OrganizationInvitationSent** — invite email (sent and resent); recipient `invitee`.
- **OrganizationInvitationAccepted** — notify inviter; recipient `inviter`.
- **TrialEndingReminder** — trial ending reminder; recipient `owner`.
- **DunningFailedPaymentReminder** — failed payment reminder; recipient `owner`.
- **InvoicePaid** — invoice paid (e.g. Stripe webhook); recipient `owner`.
- **NewTermsVersionPublished** — new terms require acceptance; recipient `user`.

Templates are seeded in **`Database\Seeders\Essential\MailTemplatesSeeder`** (runs whenever the Essential seeders run: `php artisan db:seed` or deployment that includes seeding). **For emails to be sent, this seeder must have run at least once** so that active `MailTemplate` rows exist for each event. Super-admins can view and edit templates in **Filament → Settings → Email templates** (`/admin/mail-templates`) and from the **Dashboard** quick action "Email templates".

## Mail tracking

Sent mail (including mail sent via Database Mail) is logged and can receive delivery/bounce events using [backstage/laravel-mails](https://github.com/backstagephp/laravel-mails). See [Laravel Mails](laravel-mails.md) for configuration, webhooks, and optional Filament UI.
