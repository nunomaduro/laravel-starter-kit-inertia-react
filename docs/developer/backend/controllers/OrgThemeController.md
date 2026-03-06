# OrgThemeController

## Purpose

Handles saving and resetting per-organization Tailux theme overrides (dark scheme, primary color, light scheme, card skin, border radius).

## Location

`app/Http/Controllers/OrgThemeController.php`

## Methods

| Method | HTTP Method | Route | Purpose |
|--------|------------|-------|---------|
| `save` | POST | `/org/theme` | Save current theme selections as org overrides |
| `reset` | DELETE | `/org/theme` | Remove all org theme overrides, reverting to global defaults |

## Routes

- `org.theme.save`: `POST /org/theme` - Validates and persists 5 theme dimensions for the current organization
- `org.theme.reset`: `DELETE /org/theme` - Removes all 5 theme override rows for the current organization

## Actions Used

None — writes directly via `OrganizationSettingsService`.

## Validation

Inline validation in controller:

- `dark` — required, one of: `navy`, `mirage`, `mint`, `black`, `cinder`
- `primary` — required, one of: `indigo`, `blue`, `green`, `amber`, `purple`, `rose`
- `light` — required, one of: `slate`, `gray`, `neutral`
- `skin` — required, one of: `shadow`, `bordered`, `flat`, `elevated`
- `radius` — required, one of: `none`, `sm`, `default`, `md`, `lg`, `full`

## Authorization

User must satisfy `canCustomize` logic: either `isOrganizationAdmin()` OR `ThemeSettings::allow_user_theme_customization` is `true`. Returns 403 otherwise.

## Related Components

- **Frontend**: `resources/js/components/ui/theme-customizer.tsx` (calls these endpoints)
- **Settings**: `app/Settings/ThemeSettings.php` (group name used for overrides)
- **Service**: `app/Services/OrganizationSettingsService` (`setOverride` / `removeOverride`)
- **Routes**: `org.theme.save`, `org.theme.reset` (defined in `routes/web.php`, under `auth` + `tenant` middleware)
