# UpdateUserThemeMode

## Purpose

Updates the authenticated user's dark/light/system mode preference in the database.

## Location

`app/Actions/UpdateUserThemeMode.php`

## Method Signature

```php
public function handle(User $user, string $mode): void
```

## Dependencies

None

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$user` | `User` | The user whose theme mode should be updated |
| `$mode` | `string` | The mode to apply: `'dark'`, `'light'`, or `'system'` |

## Return Value

`void`

## Usage Examples

### From Controller

```php
app(UpdateUserThemeMode::class)->handle($user, 'dark');
```

## Related Components

- **Controller**: `UserPreferencesController`
- **Route**: `user.preferences.update` (PATCH /user/preferences)
- **Model**: `User`

## Notes

Sets `theme_mode` directly on the model and calls `save()` to avoid mass assignment issues.
