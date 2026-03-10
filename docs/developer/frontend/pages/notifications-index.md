# Notifications Index Page

## Purpose

Displays a paginated list of the authenticated user's in-app notifications with actions to mark individual notifications as read, mark all as read, and delete notifications.

## Location

`resources/js/pages/notifications/index.tsx`

## Route

`GET /notifications` — rendered by `IndexNotificationsController`

## Props

| Prop | Type | Description |
|------|------|-------------|
| `notificationsList` | `PaginatedNotifications` | Paginated Laravel notifications for the current user |

### `PaginatedNotifications` shape

```ts
{
  data: Notification[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}
```

### `Notification` shape

```ts
{
  id: string;
  type: string;
  data: Record<string, unknown>;
  read_at: string | null;
  created_at: string;
}
```

## Features

- Lists notifications grouped by read/unread state
- Mark individual notification as read (via router PUT)
- Mark all notifications as read
- Delete individual notifications
- Pagination via Inertia `router.get`

## Related

- Controller: `app/Http/Controllers/Notifications/IndexNotificationsController.php`
- Layout: `AppLayout`
