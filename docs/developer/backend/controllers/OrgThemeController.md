# OrgThemeController

## Purpose

Handles saving, resetting, and AI-driven theme suggestion for per-organization Tailux theme overrides.

## Location

`app/Http/Controllers/OrgThemeController.php`

## Methods

| Method | HTTP | Route | Auth | Purpose |
|--------|------|-------|------|---------|
| `save` | POST | `/org/theme` | `canCustomize` | Save current theme selections as org overrides |
| `reset` | DELETE | `/org/theme` | `canCustomize` | Remove all org theme overrides, reverting to global defaults |
| `analyzeLogo` | POST | `/org/theme/analyze-logo` | `org.settings.manage` | Upload logo → Gemini vision → return suggested theme + logo URL |

## Routes

- `org.theme.save`: `POST /org/theme`
- `org.theme.reset`: `DELETE /org/theme`
- `org.theme.analyze-logo`: `POST /org/theme/analyze-logo` — returns JSON `{ suggestion, logoUrl }`

## Actions Used

- `SuggestThemeFromLogo` — called by `analyzeLogo()` to get AI theme suggestion from logo image

## Validation (`analyzeLogo`)

- `logo` — required, mimes: `jpg,jpeg,png,gif,webp`, max 2 MB

## Authorization

| Method | Rule |
|--------|------|
| `save` / `reset` | `isOrganizationAdmin()` OR `allow_user_theme_customization = true` |
| `analyzeLogo` | `org.settings.manage` permission only (stricter — same as BrandingController) |

`analyzeLogo` returns 422 JSON if no organization context (single-tenant mode).

## `analyzeLogo` Flow

1. Resolve org from `TenantContext` — 422 if not an `Organization`
2. Abort 403 unless `org.settings.manage`
3. Validate file (mimes + max 2 MB)
4. Delete old logo from `public` disk if one exists in `organization_settings`
5. Store new logo → `branding/logos/` on `public` disk
6. Persist path via `OrganizationSettingsService::setOverride()`
7. Call `SuggestThemeFromLogo::handle($file)`
8. Return `{ suggestion: {...themeValues, reason}, logoUrl }`

## Related Components

- **Frontend**: `resources/js/components/ui/theme-customizer.tsx` (`handleLogoUpload` uses native `fetch()` to call `analyzeLogo`)
- **Action**: `app/Actions/SuggestThemeFromLogo.php`
- **Settings**: `app/Settings/ThemeSettings.php`
- **Service**: `app/Services/OrganizationSettingsService`
- **Routes**: `routes/web.php`, under `auth` middleware group
