# DuplicateUser

## Purpose

Duplicates a user with a new name (suffix " (copy)"), unique email, and same organization memberships. Used for the users DataTable replicate action.

## Location

`app/Actions/DuplicateUser.php`

## Method Signature

```php
public function handle(User $user): User
```

## Dependencies

None.

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$user` | `User` | User to duplicate |

## Return Value

The newly created `User` (the copy).

## Usage Examples

### From Controller

```php
app(DuplicateUser::class)->handle($user);
```

## Related Components

- **Controller**: `UsersTableController` (duplicate)
- **Route**: `users.duplicate` (POST)
- **Model**: `User`

## Notes

- Runs in a DB transaction. Copy gets a random email (copy-{id}-{random}@example.com) and same org attachments.
