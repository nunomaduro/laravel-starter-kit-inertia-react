# OrgSlugController

## Purpose

Displays and updates the organization's workspace URL slug under Settings → General.

## Location

`app/Http/Controllers/Settings/OrgSlugController.php`

## Methods

| Method | HTTP | Route | Auth | Purpose |
|--------|------|-------|------|---------|
| `show` | GET | `/settings/general` | `tenant`, `permission:org.settings.manage` | Render workspace URL settings page |
| `update` | PATCH | `/settings/general/slug` | `tenant`, `permission:org.settings.manage` | Change organization slug, create redirect |

## Routes

- `settings.general.show`: `GET /settings/general`
- `settings.general.slug.update`: `PATCH /settings/general/slug`

## Actions Used

- `RecordAuditLog` — logs `slug.changed` with old/new slug values

## Validation (`update`)

| Field | Rules |
|-------|-------|
| `slug` | required, string, `SlugAvailable` custom rule (excludes current org) |
| `confirmed` | required, accepted (checkbox confirmation) |

## Props Passed to Page (`show`)

```json
{
  "organization": { "id": int, "name": string, "slug": string },
  "baseDomain": "string from config('tenancy.domain')"
}
```

## Slug Update Flow

1. Resolve org from `TenantContext` — 404 if not found
2. Validate slug format + availability (via `SlugAvailable` rule)
3. Validate `confirmed` checkbox is accepted
4. If slug unchanged, redirect back with info message
5. Create/update `slug_redirects` row: `old_slug → new_slug`
6. Update any existing redirects that pointed to the old slug
7. Save new slug on the Organization model
8. Record `slug.changed` audit log entry
9. Redirect back with success message

## Related Components

- **Frontend**: `resources/js/pages/settings/general.tsx`
- **Rule**: `app/Rules/SlugAvailable.php`
- **Model**: `app/Models/SlugRedirect.php`
- **Action**: `app/Actions/RecordAuditLog.php`
- **Service**: `app/Services/TenantContext`
