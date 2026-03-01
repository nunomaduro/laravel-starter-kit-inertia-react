# BulkSoftDeleteUsers

## Purpose

Soft-deletes users by id; skips the current user. Used for the users DataTable bulk action.

## Location

`app/Actions/BulkSoftDeleteUsers.php`

## Method Signature

```php
public function handle(array $ids, ?User $currentUser): int
```

## Dependencies

None.

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ids` | `array<int>` | User IDs to soft-delete |
| `$currentUser` | `User\|null` | Current user (excluded from deletion) |

## Return Value

Number of users soft-deleted.

## Usage Examples

### From Controller

```php
app(BulkSoftDeleteUsers::class)->handle($ids, $request->user());
```

## Related Components

- **Controller**: `UsersTableController` (bulk soft-delete)
- **Route**: `users.bulk-soft-delete` (POST)
- **Form Request**: `BulkSoftDeleteUsersRequest`
- **Model**: `User`

## Notes

- Runs in a DB transaction. Does not delete the current user even if selected.
