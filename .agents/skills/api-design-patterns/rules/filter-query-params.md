---
title: Filter via Query Parameters
impact: HIGH
impactDescription: "Cacheable, bookmarkable filtering that leverages HTTP semantics correctly"
tags: filtering, query-params, search
---

## Filter via Query Parameters

**Impact: HIGH (Cacheable, bookmarkable filtering that leverages HTTP semantics correctly)**

Filtering is a read operation and belongs in GET requests with query parameters. Using POST bodies for filtering breaks HTTP cacheability, makes URLs non-shareable, and violates the semantic contract of HTTP methods. Standard query parameters are combinable, cacheable, and immediately understandable.

## Incorrect

```http
POST /api/v1/users/search
Content-Type: application/json

{
  "filters": {
    "status": "active",
    "role": "admin",
    "created_after": "2024-01-01"
  }
}
```

```http
# Or: custom filter syntax that requires a parser
GET /api/v1/users?filter=status:eq:active|role:eq:admin|created:gt:2024-01-01
```

**Problems:**
- POST for read operations breaks HTTP caching at every layer (CDN, browser, proxy)
- URLs cannot be bookmarked, shared, or logged meaningfully
- Custom filter syntax requires client-side query builders and server-side parsers
- Violates REST semantics — POST implies resource creation or mutation, not retrieval

## Correct

```http
GET /api/v1/users?status=active&role=admin&created_after=2024-01-01
```

```json
{
  "data": [
    {
      "id": 42,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "status": "active",
      "role": "admin",
      "created_at": "2024-03-15T10:30:00Z"
    }
  ],
  "meta": {
    "total_count": 12,
    "filters_applied": {
      "status": "active",
      "role": "admin",
      "created_after": "2024-01-01"
    }
  }
}
```

```http
# Multiple values for the same field (OR logic)
GET /api/v1/users?status=active&status=pending

# Range filters with clear suffixes
GET /api/v1/orders?total_min=100&total_max=500&created_after=2024-01-01

# Combine with search
GET /api/v1/users?role=admin&q=smith
```

**Benefits:**
- Fully cacheable by CDNs, reverse proxies, and browsers
- URLs are bookmarkable and shareable — useful for dashboards and saved views
- Filters are self-documenting and combinable with standard `&` syntax
- No custom parser needed — standard query string parsing libraries handle it

Reference: [Google API Design Guide - Standard Methods](https://cloud.google.com/apis/design/standard_methods)
