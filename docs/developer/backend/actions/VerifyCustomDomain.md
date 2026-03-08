# VerifyCustomDomain

## Purpose

Performs DNS CNAME verification for a custom organization domain. Detects Cloudflare proxy conflicts, enforces a maximum attempt limit, and updates the domain's status accordingly.

## Location

`app/Actions/VerifyCustomDomain.php`

## Method Signature

```php
public function handle(OrganizationDomain $domain): bool
```

## Dependencies

None (no constructor dependencies).

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | `OrganizationDomain` | The domain record to verify |

## Return Value

Returns `true` if DNS resolves correctly to the expected CNAME target; `false` otherwise.

## Verification Flow

1. Increment `dns_check_attempts` and set `last_dns_check_at = now()`
2. If `dns_check_attempts > 144` (≈ 12 hours at 5-minute intervals), mark `status = 'error'`, `failure_reason = 'timeout'`, return `false`
3. Look up DNS CNAME records via `dns_get_record($domain->domain, DNS_CNAME)`
4. For each record:
   - If target contains `cloudflare` or `cdn-cgi`, mark `status = 'error'`, `failure_reason = 'cloudflare_proxy_detected'`, return `false`
   - If target contains `$domain->cname_target`, mark `status = 'dns_verified'`, `is_verified = true`, `verified_at = now()`, return `true`
5. If no matching record found, return `false` (caller may retry)

## Domain Status Values

| Status | Meaning |
|--------|---------|
| `pending_dns` | Waiting for DNS propagation |
| `dns_verified` | CNAME record confirmed, SSL provisioning can begin |
| `error` | Permanent failure; see `failure_reason` |

## Failure Reasons

| Reason | Meaning |
|--------|---------|
| `timeout` | Exceeded 144 check attempts without success |
| `cloudflare_proxy_detected` | CNAME points through Cloudflare proxy (orange cloud); must use DNS-only mode |

## Usage

Called by the `VerifyOrganizationDomain` job, which is dispatched from `OrgDomainsController::store()` (delayed 5 minutes) and `OrgDomainsController::verify()` (immediate).

## Related Components

- **Job**: `app/Jobs/VerifyOrganizationDomain.php`
- **Model**: `app/Models/OrganizationDomain.php`
- **Controller**: `OrgDomainsController`
- **Internal Endpoint**: `CaddyAskController` — checks `is_verified` + `status = 'dns_verified'` for Caddy TLS
