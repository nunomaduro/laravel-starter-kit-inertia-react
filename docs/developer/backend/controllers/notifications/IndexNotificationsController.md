# IndexNotificationsController

## Purpose
Returns a paginated list of the authenticated user's database notifications.

## Location
`app/Http/Controllers/Notifications/IndexNotificationsController.php`

## Route Information
- **URL**: `GET /notifications`
- **Route Name**: `notifications.index`
- **Middleware**: `auth`

## Response
JSON array of notification objects, newest first. Each item includes:
- `id` — UUID
- `type` — Notification class name
- `data` — Payload (`title`, `message`, `type`, `action_url`)
- `read_at` — Null if unread
- `created_at`

## Related
- `app/Http/Middleware/HandleInertiaRequests.php` — shares `notifications.unread_count`
- `resources/js/types/index.d.ts` — `AppNotification` interface
