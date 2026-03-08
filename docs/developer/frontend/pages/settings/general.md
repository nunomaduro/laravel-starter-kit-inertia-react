# settings/general

## Purpose

Workspace URL settings page. Allows org admins to change the organization's slug (subdomain). Includes a debounced real-time availability checker and a confirmation checkbox before the change can be submitted.

## Location

`resources/js/pages/settings/general.tsx`

## Route Information

- **URL**: `/settings/general`
- **Route Names**: `settings.general.show` (GET), `settings.general.slug.update` (PATCH)
- **Middleware**: `auth`, `tenant`, `permission:org.settings.manage`

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| `organization` | `{ id, name, slug }` \| null | Current organization data |
| `baseDomain` | string \| null | Platform base domain from `config('tenancy.domain')` |

## User Flow

1. Page loads showing the current workspace URL as `{slug}.{baseDomain}`.
2. User types a new slug — input is normalized to lowercase alphanumeric + hyphens.
3. After 500ms debounce, `/api/slug-availability?slug=...` is called.
4. Availability feedback shows below the input: green "Available", or red "Taken" with a suggestion.
5. If available and different from current slug, a warning panel appears listing consequences (webhook URLs, SSO redirect URIs).
6. User checks the confirmation checkbox, enabling the submit button.
7. Form submits `PATCH` to `settings.general.slug.update`; on success the confirmation checkbox resets.

## Key Components

- `checkAvailability` — debounced fetch to `SlugAvailabilityController` (500ms delay)
- Confirmation panel only visible when slug is changed, valid, and available
- Submit disabled until: not processing, slug changed, available, and confirmed

## Related Components

- **Controller**: `OrgSlugController`
- **API**: `SlugAvailabilityController` (`GET /api/slug-availability`)
- **Layout**: `AppLayout` + `SettingsLayout`
- **Wayfinder**: `@/routes/settings/general/slug` (`update`)
