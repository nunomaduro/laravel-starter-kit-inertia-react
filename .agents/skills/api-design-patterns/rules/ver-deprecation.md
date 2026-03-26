---
title: Deprecation Strategy
impact: HIGH
impactDescription: "Structured deprecation prevents surprise outages and gives consumers a clear migration path"
tags: versioning, deprecation, sunset, migration
---

## Deprecation Strategy

**Impact: HIGH (Structured deprecation prevents surprise outages and gives consumers a clear migration path)**

Removing an API version or endpoint without advance notice breaks consumer trust and causes production outages. A proper deprecation strategy uses standard HTTP headers, documented timelines, and migration guides to give consumers the time and information they need to transition safely.

## Incorrect

```http
# Monday: API v1 is working
GET /api/v1/users
HTTP/1.1 200 OK

# Tuesday: API v1 is gone without warning
GET /api/v1/users
HTTP/1.1 404 Not Found

{
  "error": "This endpoint no longer exists"
}
```

**Problems:**
- Consumers discover the removal through production failures, not proactive communication
- No migration period means every consumer must update simultaneously or break
- No documentation of what changed or where to go creates confusion and support burden
- Erodes trust — consumers cannot rely on the API for production workloads

## Correct

```http
# Phase 1: Announce deprecation (minimum 6 months before removal)
GET /api/v1/users
HTTP/1.1 200 OK
Deprecation: true
Sunset: Sat, 01 Jun 2025 00:00:00 GMT
Link: </api/v2/migration-guide>; rel="deprecation"
```

```json
{
  "data": [
    { "id": 1, "name": "Jane Smith" }
  ],
  "_deprecation": {
    "message": "API v1 is deprecated. Please migrate to v2 by June 1, 2025.",
    "migration_guide": "https://api.example.com/docs/v1-to-v2-migration",
    "sunset_date": "2025-06-01T00:00:00Z"
  }
}
```

```http
# Phase 2: After sunset date — return 410 Gone (not 404)
GET /api/v1/users
HTTP/1.1 410 Gone

{
  "error": {
    "code": "API_VERSION_SUNSET",
    "message": "API v1 was removed on June 1, 2025. Please use /api/v2/users.",
    "migration_guide": "https://api.example.com/docs/v1-to-v2-migration",
    "current_version": "/api/v2/users"
  }
}
```

```
Deprecation Timeline:
  T-6 months: Add Deprecation + Sunset headers, publish migration guide
  T-3 months: Send email/notification to API consumers
  T-1 month:  Final reminder, log consumers still using deprecated version
  T-0:        Return 410 Gone with redirect information
  T+3 months: Remove deprecated code from codebase
```

**Benefits:**
- `Sunset` header (RFC 8594) is a machine-readable standard that monitoring tools can detect automatically
- `410 Gone` correctly signals permanent removal (unlike `404` which implies the resource may return)
- Migration guide link in both headers and body ensures consumers can find upgrade instructions
- Minimum 6-month window gives teams of all sizes enough time to plan and execute migration

Reference: [RFC 8594 - The Sunset HTTP Header Field](https://datatracker.ietf.org/doc/html/rfc8594)
