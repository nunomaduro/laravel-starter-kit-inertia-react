# ClearAllNotificationsController

## Purpose
Permanently deletes all notifications for the authenticated user.

## Location
`app/Http/Controllers/Notifications/ClearAllNotificationsController.php`

## Route Information
- **URL**: `DELETE /notifications`
- **Route Name**: `notifications.clear`
- **Middleware**: `auth`

## Response
JSON `{ message: 'ok' }` on success.
