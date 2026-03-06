# UserPreferencesController

## Purpose

Handles updates to per-user preferences such as the dark/light/system mode setting.

## Location

`app/Http/Controllers/UserPreferencesController.php`

## Methods

| Method | HTTP Method | Route | Purpose |
|--------|------------|-------|---------|
| `update` | PATCH | `/user/preferences` | Updates the user's theme mode preference |

## Routes

- `user.preferences.update`: `PATCH /user/preferences` - Saves the user's chosen theme mode to the database

## Actions Used

- `UpdateUserThemeMode` - Persists the chosen `theme_mode` value on the user record

## Validation

Inline validation in the controller:

- `theme_mode` — required, string, must be one of `dark`, `light`, `system`

## Related Components

- **Actions**: `UpdateUserThemeMode`
- **Routes**: `user.preferences.update` (defined in routes/web.php)
- **Frontend**: `resources/js/components/ui/mode-toggle.tsx` (sends PATCH to this route)
