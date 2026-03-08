# DeleteNotificationController

## Purpose
Permanently deletes a single notification for the authenticated user.

## Location
`app/Http/Controllers/Notifications/DeleteNotificationController.php`

## Route Information
- **URL**: `DELETE /notifications/{notification}`
- **Route Name**: `notifications.delete`
- **Middleware**: `auth`

## Response
JSON `{ message: 'ok' }` on success, 404 if not found or unauthorized.
