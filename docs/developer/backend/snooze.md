# Snooze (thomasjohnkane/snooze)

Schedule future notifications and reminders in Laravel. Notifications are stored in the database and sent at the specified time by the `snooze:send` command (runs every minute via the scheduler).

## Use cases

- Reminder system (1 week before appointment, 1 day before, 1 hour before)
- Follow-up surveys (2 days after purchase)
- On-boarding email drips (welcome after sign-up, tips after 3 days, upsell after 7 days)
- Birthday emails, short-term recurring reports

## Usage

### Model trait (SnoozeNotifiable)

Add `SnoozeNotifiable` to any `Notifiable` model (e.g. User):

```php
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;

class User extends Authenticatable
{
    use Notifiable, SnoozeNotifiable;
}

// Schedule a notification
$user->notifyAt(new BirthdayNotification, Carbon::parse($user->birthday));
$user->notifyAt(new FollowUpNotification, Carbon::now()->addDays(7));
```

### ScheduledNotification::create

For one-off scheduling or anonymous targets:

```php
use Thomasjohnkane\Snooze\Models\ScheduledNotification;

ScheduledNotification::create(
    Auth::user(),
    new MyNotification($order),
    Carbon::now()->addHour()
);

// Anonymous (e.g. email/SMS route)
$target = (new AnonymousNotifiable)
    ->route('mail', 'hello@example.com');
ScheduledNotification::create($target, new MyNotification(), Carbon::now()->addDay());
```

### Cancel, reschedule, duplicate

```php
$notification->cancel();
$notification->reschedule(Carbon::now()->addDay(1));
$newNotification = $notification->scheduleAgainAt(Carbon::now()->addWeek());
```

## Scheduler

The package auto-schedules `snooze:send` every minute (configurable via `sendFrequency`). Ensure `schedule:run` is running (cron: `* * * * * php artisan schedule:run`).

## Configuration

**config/snooze.php** (published):

| Option | Env | Default | Description |
|--------|-----|---------|-------------|
| `sendFrequency` | `SCHEDULED_NOTIFICATION_SEND_FREQUENCY` | `everyMinute` | How often to run `snooze:send` |
| `sendTolerance` | `SCHEDULED_NOTIFICATION_SEND_TOLERANCE` | 86400 (24h) | Max seconds to send overdue notifications (prevents backlog flood) |
| `pruneAge` | `SCHEDULED_NOTIFICATION_PRUNE_AGE` | `null` | Days to keep sent/cancelled; `null` = no pruning |
| `disabled` | `SCHEDULED_NOTIFICATIONS_DISABLED` | `false` | Disable sending (notifications still scheduled) |
| `onOneServer` | `SCHEDULED_NOTIFICATIONS_ONE_SERVER` | `false` | Use `onOneServer()` for multi-server deployments |
| `scheduleCommands` | `SCHEDULED_NOTIFICATIONS_SCHEDULE_COMMANDS` | `true` | Auto-schedule snooze:send and snooze:prune |

## Database

- **scheduled_notifications:** id, notifiable_type, notifiable_id, notification_class, scheduled_at, sent_at, cancelled_at, meta, etc.

## Commands

- `php artisan snooze:send` — Send due notifications (runs every minute by default)
- `php artisan snooze:prune` — Prune old sent/cancelled (runs daily when `pruneAge` is set)

## Reference

- [GitHub](https://github.com/thomasjohnkane/snooze)
- [Packagist](https://packagist.org/packages/thomasjohnkane/snooze)
