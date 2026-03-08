# slug_redirects & organization_domains (domain columns)

## slug_redirects Table

Stores historical slug mappings so that old organization URLs redirect to the current slug.

**Migration**: `database/migrations/2026_03_08_000001_create_slug_redirects_table.php`

### Schema

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment |
| `old_slug` | varchar(63), unique | The previous slug |
| `organization_id` | bigint (FK → organizations) | Owning organization; cascades on delete |
| `redirects_to_slug` | varchar(63) | The current/active slug to redirect to |
| `created_at` | timestamp | When the redirect was created |
| `expires_at` | timestamp, nullable | Optional expiry; `null` = never expires |

### Notes

- When a slug is changed, `OrgSlugController::update()` creates or updates the row for `old_slug`.
- Existing redirects that previously pointed to `old_slug` are also updated to point at `new_slug`, preventing redirect chains.
- `ResolveDomainMiddleware` reads this table to perform 301 redirects for old subdomain requests.

---

## organization_domains — Status Columns

Added by migration `database/migrations/2026_03_08_000002_add_status_to_organization_domains_table.php` to support the full custom domain verification lifecycle.

### Base Schema (original migration)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment |
| `organization_id` | bigint (FK → organizations) | Owning organization; cascades on delete |
| `domain` | varchar, unique | The domain name |
| `type` | enum: `subdomain`, `custom` | Domain type |
| `is_verified` | boolean | `true` once CNAME confirmed |
| `verification_token` | varchar, nullable | Random token (reserved for future TXT record verification) |
| `is_primary` | boolean | Whether this is the primary domain |
| `verified_at` | timestamp, nullable | When verification succeeded |
| `created_at`, `updated_at` | timestamps | Eloquent timestamps |

### Added Status Columns

| Column | Type | Description |
|--------|------|-------------|
| `status` | enum | `pending_dns`, `dns_verified`, `ssl_provisioning`, `active`, `error`, `expired` — default `pending_dns` |
| `cname_target` | varchar, nullable | Expected CNAME target (e.g. `myorg.example.com`) |
| `failure_reason` | varchar, nullable | `timeout` or `cloudflare_proxy_detected` |
| `dns_check_attempts` | tinyint unsigned | Number of times DNS was checked; max 144 before timeout |
| `last_dns_check_at` | timestamp, nullable | Timestamp of most recent DNS check |
| `ssl_issued_at` | timestamp, nullable | When Caddy issued the SSL certificate |
| `ssl_expires_at` | timestamp, nullable | SSL certificate expiry date |

### Status Lifecycle

```
pending_dns → dns_verified → ssl_provisioning → active
           ↘ error (timeout / cloudflare_proxy_detected)
```

### Related Components

- **Action**: `VerifyCustomDomain` — drives status transitions
- **Job**: `VerifyOrganizationDomain` — calls the action on a schedule
- **Controller**: `OrgDomainsController` — creates domains and triggers verification
- **Internal**: `CaddyAskController` — reads `is_verified` + `status = 'dns_verified'` for Caddy TLS
