# branding

## Purpose

Organization branding settings: logo upload, theme preset (default/vega/nova), radius, font, and whether users can override appearance. Requires `org.settings.manage`.

## Location

`resources/js/pages/settings/branding.tsx`

## Route Information

- **URL**: `/settings/branding` (within tenant)
- **Route Name**: `org.settings.branding` (or equivalent under org settings)
- **HTTP Method**: GET (show), PUT/PATCH (update)
- **Middleware**: `auth`, `tenant`

## Props (from Controller)

| Prop     | Type   | Description                                                                 |
|----------|--------|-----------------------------------------------------------------------------|
| branding | object | Current: `logoUrl`, `themePreset`, `themeRadius`, `themeFont`, `allowUserCustomization` |
| ...      |        | Flash and any form defaults from BrandingController                        |

## User Flow

1. User with org.settings.manage opens Settings → Branding.
2. Can upload/remove logo; set theme preset, radius, font; toggle allow user customization.
3. Saves; HandleInertiaRequests shares updated `branding` (lazy) so AppLogo and theme props reflect changes.

## Related Components

- **Controller**: `BrandingController`
- **Backend**: `OrganizationSettingsService::getBranding()`, org settings stored per organization
- **Theme**: `ThemeFromProps`, `app-logo.tsx` use `branding` when present
