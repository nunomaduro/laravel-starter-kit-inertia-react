# CaddyAskController

## Purpose

Internal endpoint used by Caddy's `on_demand` TLS feature to determine whether a domain should be issued an SSL certificate. Returns 200 if the domain is verified, 403 otherwise.

## Location

`app/Http/Controllers/Internal/CaddyAskController.php`

## Method

| Method | HTTP | Route | Auth | Purpose |
|--------|------|-------|------|---------|
| `__invoke` | GET | `/internal/caddy/ask` | IP allowlist middleware | Authorize on-demand TLS for a domain |

## Route

- `internal.caddy.ask`: `GET /internal/caddy/ask?domain={domain}`

## Middleware

The route is protected by an IP allowlist middleware that only permits requests from the Caddy server's IP. It is not publicly accessible.

## Request

| Query Parameter | Description |
|----------------|-------------|
| `domain` | The domain name Caddy wants to issue a certificate for |

## Response

| Status | Condition |
|--------|-----------|
| `200` | Domain exists in `organization_domains` with `is_verified = true` AND `status = 'dns_verified'` |
| `400` | `domain` query parameter is empty |
| `403` | Domain not found or not yet verified |

Caddy proceeds with certificate provisioning on 200, and skips it on any non-2xx response.

## Related Components

- **Model**: `app/Models/OrganizationDomain.php`
- **Action**: `app/Actions/VerifyCustomDomain.php` — sets `is_verified` and `status = 'dns_verified'`
- **Job**: `app/Jobs/VerifyOrganizationDomain.php`
- **Docs**: [Caddy on_demand TLS](https://caddyserver.com/docs/caddyfile/options#on-demand-tls)
