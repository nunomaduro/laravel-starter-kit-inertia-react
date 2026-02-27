# User show

## Purpose

Displays a single user’s details (name, email, created at) with a back link to the users table. Reached from the Users table via row link or the row action “View”.

## Location

`resources/js/pages/users/show.tsx`

## Route Information

- **URL**: `/users/{user}`
- **Route Name**: `users.show`
- **HTTP Method**: GET
- **Middleware**: web, auth, verified

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| user | { id: number, name: string, email: string, created_at: string \| null } | The user to display. |

## User Flow

1. User navigates to `/users/{id}` (from the users table row link or “View” action).
2. Page shows user name, email, and created-at date.
3. User can click the back arrow to return to `/users`.

## Related Components

- **Route**: `users.show` (GET /users/{user}); closure in `routes/web.php` with same auth as `users.table`; non–super-admins only see users in the current organization.
- **Layout**: `AppSidebarLayout`.
- **Linked from**: [users/table](./table.md) (`rowLink` and row action “View”).

## Implementation Details

- Authorization matches the users table: `bypass-permissions` or `org.members.view` in the current org. For tenant users, the resolved user must belong to the current organization (404 otherwise).
- Uses Inertia `Link` for the back button to `/users`.
