---
title: Version in URL Path
impact: HIGH
impactDescription: "Explicit version visibility prevents accidental breaking-change consumption"
tags: versioning, url-path, api-version
---

## Version in URL Path

**Impact: HIGH (Explicit version visibility prevents accidental breaking-change consumption)**

URL path versioning is the most explicit and widely adopted approach to API versioning. The version is visible in every request, making it impossible to accidentally hit the wrong version. It works naturally with routing, caching, load balancing, and documentation tools.

## Incorrect

```http
# No version — all consumers share one contract
GET /api/users
```

```json
// v1 response shape
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

```json
// After a breaking change — same URL, different shape
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "name": null
}
```

**Problems:**
- Breaking changes immediately affect all consumers with no migration path
- No way to run old and new versions side-by-side during transition periods
- Clients cannot pin to a known-good contract — any deploy can break them
- Rollback requires reverting the entire API, not just routing rules

## Correct

```http
# Version 1 — original contract
GET /api/v1/users/1
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

```http
# Version 2 — new contract, coexists with v1
GET /api/v2/users/1
```

```json
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "profile": {
    "avatar_url": "https://cdn.example.com/avatars/1.jpg"
  }
}
```

**Benefits:**
- Version is immediately visible in URLs, logs, and documentation
- Old and new versions run simultaneously — consumers migrate at their own pace
- Load balancers and API gateways can route versions to different backends
- CDNs and proxies cache each version independently without conflict

Reference: [Stripe API - Versioning](https://stripe.com/docs/api/versioning)
