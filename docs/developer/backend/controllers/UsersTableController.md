# UsersTableController

## Purpose

Handles the users DataTable page (index), bulk soft-delete, duplicate user, and user show.

## Location

`app/Http/Controllers/UsersTableController.php`

## Methods

| Method | HTTP | Route | Purpose |
|--------|------|-------|---------|
| `index` | GET | `users.table` | Render users table (UserDataTable) |
| `bulkSoftDelete` | POST | `users.bulk-soft-delete` | Soft-delete selected users |
| `duplicate` | POST | `users.duplicate` | Duplicate a user |
| `show` | GET | `users.show` | Show single user |

## Routes

- `users.table`: GET `users` — Users table page
- `users.bulk-soft-delete`: POST `users/bulk-soft-delete` — Bulk soft-delete
- `users.duplicate`: POST `users/{user}/duplicate` — Duplicate user
- `users.show`: GET `users/{user}` — User detail

## Actions Used

- `BulkSoftDeleteUsers` — Bulk soft-delete
- `DuplicateUser` — Duplicate user

## Validation

- `BulkSoftDeleteUsersRequest` — Validates `ids` array for bulk soft-delete

## Related Components

- **Pages**: `users/table`, `users/show`
- **DataTable**: `UserDataTable`
- **Actions**: `BulkSoftDeleteUsers`, `DuplicateUser`
- **Routes**: `users.table`, `users.bulk-soft-delete`, `users.duplicate`, `users.show`
