# MarkNotificationReadController

## Purpose
Marks a single notification as read for the authenticated user.

## Location
`app/Http/Controllers/Notifications/MarkNotificationReadController.php`

## Route Information
- **URL**: `POST /notifications/{notification}/read`
- **Route Name**: `notifications.read`
- **Middleware**: `auth`

## Authorization
Uses policy / where-clause to ensure the notification belongs to `Auth::user()`.

## Response
JSON `{ message: 'ok' }` on success, 404 if not found.

## Related
- `IndexNotificationsController` — re-fetch list after marking read
