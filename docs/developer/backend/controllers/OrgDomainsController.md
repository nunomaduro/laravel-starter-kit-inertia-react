# OrgDomainsController

## Purpose

Manages custom domains for an organization: listing, adding, verifying, and removing domains under Settings → Domains.

## Location

`app/Http/Controllers/Settings/OrgDomainsController.php`

## Methods

| Method | HTTP | Route | Auth | Purpose |
|--------|------|-------|------|---------|
| `show` | GET | `/settings/domains` | `tenant`, `permission:org.settings.manage` | Render custom domains page |
| `store` | POST | `/settings/domains` | `tenant`, `permission:org.settings.manage` | Add a new custom domain |
| `destroy` | DELETE | `/settings/domains/{domain}` | `tenant`, `permission:org.settings.manage` | Remove a domain |
| `verify` | POST | `/settings/domains/{domain}/verify` | `tenant`, `permission:org.settings.manage` | Trigger manual DNS re-check |

## Routes

- `settings.domains.show`: `GET /settings/domains`
- `settings.domains.store`: `POST /settings/domains`
- `settings.domains.destroy`: `DELETE /settings/domains/{domain}`
- `settings.domains.verify`: `POST /settings/domains/{domain}/verify`

## Actions / Jobs Used

- `VerifyOrganizationDomain` (Job) — dispatched on `store` (delayed 5 minutes) and on `verify` (immediate)
- `RecordAuditLog` — logs `domain.added` and `domain.removed`

## Validation (`store`)

| Field | Rules |
|-------|-------|
| `domain` | required, string, max:253, regex (valid FQDN format), unique in `organization_domains` |

## Props Passed to Page (`show`)

```json
{
  "organization": { "id": int, "name": string, "slug": string },
  "domains": [OrgDomain],
  "baseDomain": "string"
}
```

Each `OrgDomain` includes: `id`, `domain`, `type`, `status`, `is_verified`, `is_primary`, `cname_target`, `failure_reason`, `dns_check_attempts`, `ssl_expires_at`, `verified_at`.

## `store` Flow

1. Resolve org from `TenantContext` — 404 if not found
2. Validate domain format and uniqueness
3. Create `OrganizationDomain` with `status = 'pending_dns'`, `cname_target = {slug}.{baseDomain}`
4. Dispatch `VerifyOrganizationDomain` job delayed 5 minutes
5. Record `domain.added` audit log
6. Redirect back with success message

## Authorization (`destroy` / `verify`)

Aborts 403 if `domain.organization_id` does not match the current org from `TenantContext`.

## Related Components

- **Frontend**: `resources/js/pages/settings/domains.tsx`
- **Job**: `app/Jobs/VerifyOrganizationDomain.php`
- **Action**: `app/Actions/VerifyCustomDomain.php`
- **Model**: `app/Models/OrganizationDomain.php`
- **Audit**: `app/Actions/RecordAuditLog.php`
