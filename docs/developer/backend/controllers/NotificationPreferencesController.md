# NotificationPreferencesController

## Purpose
Manages per-user notification channel preferences (in-app and email) for each configured notification type.

## Location
`app/Http/Controllers/Settings/NotificationPreferencesController.php`

## Route Information
- **URL (GET)**: `/settings/notifications` — `settings.notifications.show`
- **URL (PATCH)**: `/settings/notifications` — `settings.notifications.update`
- **Middleware**: `auth` (no org required — per-user preferences)

## Methods

### `show()`
Merges all notification types from `config/notification-types.php` with the user's stored preferences (defaulting both channels to `true`). Returns an Inertia page with:
- `preferences` — `NotificationPref[]` (key, label, channels, via_database, via_email)

### `update(Request $request)`
Bulk-upserts `notification_preferences` rows for each submitted preference. Validates each entry has `notification_type`, `via_database`, `via_email`.

## Related
- `app/Models/NotificationPreference.php`
- `config/notification-types.php`
- `resources/js/pages/settings/notifications.tsx`
