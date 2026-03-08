# MarkAllNotificationsReadController

## Purpose
Marks all of the authenticated user's unread notifications as read in a single request.

## Location
`app/Http/Controllers/Notifications/MarkAllNotificationsReadController.php`

## Route Information
- **URL**: `POST /notifications/read-all`
- **Route Name**: `notifications.read-all`
- **Middleware**: `auth`

## Response
JSON `{ message: 'ok' }` on success.
