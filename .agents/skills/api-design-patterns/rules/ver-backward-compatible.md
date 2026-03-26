---
title: Maintain Backward Compatibility
impact: HIGH
impactDescription: "Prevents breaking existing integrations and avoids costly emergency client fixes"
tags: versioning, backward-compatibility, breaking-changes
---

## Maintain Backward Compatibility

**Impact: HIGH (Prevents breaking existing integrations and avoids costly emergency client fixes)**

Breaking changes in a live API force every consumer to update simultaneously or face outages. Maintaining backward compatibility within a version means clients continue working after deployments, and new features are delivered through additive changes only. Reserve breaking changes for new major versions.

## Incorrect

```http
# Before: GET /api/v1/users/1
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "role": "admin"
}
```

```http
# After deploy (same v1): field renamed, field removed, type changed
```

```json
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "roles": ["admin", "editor"]
}
```

**Problems:**
- Renaming `name` to `full_name` breaks every client reading `response.name`
- Renaming `email` to `email_address` breaks form bindings and display logic
- Changing `role` (string) to `roles` (array) causes type errors in client deserialization
- Removing fields with no notice gives consumers zero time to adapt

## Correct

```http
# Additive changes only within v1: new fields added, old fields preserved
GET /api/v1/users/1
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "full_name": "Jane Smith",
  "email": "jane@example.com",
  "email_address": "jane@example.com",
  "role": "admin",
  "roles": ["admin", "editor"],
  "avatar_url": "https://cdn.example.com/avatars/1.jpg"
}
```

```http
# Deprecation communicated via response headers
HTTP/1.1 200 OK
Content-Type: application/json
X-Deprecated-Fields: name, email, role
```

```http
# New endpoints are always safe to add
GET /api/v1/users/1/preferences    # new endpoint, no existing contract
```

**Benefits:**
- Existing clients continue working without any code changes after every deploy
- New clients can adopt new field names immediately while old names remain available
- Deprecation headers give automated tooling a way to detect and flag stale usage
- New endpoints and new fields never conflict with existing client expectations

Reference: [Stripe API - Backward Compatibility](https://stripe.com/docs/upgrades)
