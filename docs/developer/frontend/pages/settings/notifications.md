# settings/notifications

## Purpose

Notification preferences settings page. Allows users to configure which notification types they receive and through which channels (in-app database notifications and/or email).

## Location

`resources/js/pages/settings/notifications.tsx`

## Route Information

- **URL**: `/settings/notifications`
- **Route Names**: `settings.notifications.show` (GET), `settings.notifications.update` (PATCH)
- **Middleware**: `auth` (no org required — per-user preferences)

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| `preferences` | `NotificationPref[]` | List of notification types with current channel preferences |

## Features

- Table-based checkbox UI for each notification type
- Columns: Notification label, In-App toggle, Email toggle
- Channels grayed out (`—`) when the notification type doesn't support that channel
- PATCH form saves all preferences at once

## Related

- `app/Http/Controllers/Settings/NotificationPreferencesController.php`
- `app/Models/NotificationPreference.php`
- `config/notification-types.php`
