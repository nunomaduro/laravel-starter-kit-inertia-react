# BatchUpdateUsersAction

## Purpose

Updates a single column for multiple users by ID. Only allowed columns (`name`, `onboarding_completed`) are applied; used for users DataTable batch edit.

## Location

`app/Actions/BatchUpdateUsersAction.php`

## Method Signature

```php
public function handle(array $ids, string $column, mixed $value): int
```

## Dependencies

None.

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ids` | `array<int>` | User IDs to update |
| `$column` | `string` | Column name (must be in `ALLOWED_COLUMNS`) |
| `$value` | `mixed` | Value to set (cast per column) |

## Return Value

Number of users updated (0 if column not allowed).

## Related Components

- **Model**: `User`
- Used from API/DataTable batch update flows.

## Notes

- `onboarding_completed` is cast to boolean; `name` to string.
- Runs in a DB transaction.
