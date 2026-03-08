# settings/domains

## Purpose

Custom domain management page. Displays the org's platform subdomain and any added custom domains with their verification status. Allows admins to add new domains, trigger DNS re-verification, and delete domains.

## Location

`resources/js/pages/settings/domains.tsx`

## Route Information

- **URL**: `/settings/domains`
- **Route Names**: `settings.domains.show` (GET), `settings.domains.store` (POST), `settings.domains.destroy` (DELETE), `settings.domains.verify` (POST)
- **Middleware**: `auth`, `tenant`, `permission:org.settings.manage`

## Props (from Controller)

| Prop | Type | Description |
|------|------|-------------|
| `organization` | `{ id, name, slug }` \| null | Current organization |
| `domains` | `OrgDomain[]` | List of custom domains for this org |
| `baseDomain` | string \| null | Platform base domain |

`OrgDomain` type includes: `id`, `domain`, `type`, `status`, `is_verified`, `is_primary`, `cname_target`, `failure_reason`, `dns_check_attempts`, `ssl_expires_at`, `verified_at`.

## Domain Status Values

| Status | Badge Color | Description |
|--------|-------------|-------------|
| `pending_dns` | Yellow | Waiting for DNS record to propagate |
| `dns_verified` | Blue | CNAME confirmed; SSL provisioning next |
| `ssl_provisioning` | Blue | Caddy obtaining SSL certificate |
| `active` | Green | Domain fully active with SSL |
| `error` | Red | Verification failed; see `failure_reason` |
| `expired` | Gray | SSL certificate expired |

## User Flow

1. Page loads showing the platform subdomain (always active) and a list of custom domains.
2. Each domain row shows status badge, and for `pending_dns` / `error` status exposes "Setup" and "Verify" actions.
3. "Setup" expands a CNAME instruction panel with Type/Name/Value fields.
4. "Verify" dispatches `VerifyOrganizationDomain` job immediately.
5. "Delete" (trash icon) prompts `confirm()` then sends `DELETE` request.
6. Add domain form at the bottom accepts an FQDN and calls `POST /settings/domains`.

## Key Sub-components

- `StatusBadge` — renders colored pill with icon based on domain status
- `DomainRow` — expandable row with CNAME instructions; shows Cloudflare-specific guidance if `failure_reason = 'cloudflare_proxy_detected'`

## Related Components

- **Controller**: `OrgDomainsController`
- **Action**: `VerifyCustomDomain`
- **Layout**: `AppLayout` + `SettingsLayout`
- **Wayfinder**: `@/routes/settings/domains` (`store`, `destroy`, `verify`)
- **Types**: `OrgDomain` in `@/types`
