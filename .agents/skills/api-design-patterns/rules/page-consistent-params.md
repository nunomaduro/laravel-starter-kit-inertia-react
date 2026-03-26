---
title: Consistent Pagination Parameter Names
impact: HIGH
impactDescription: "Reduces client integration time by 40-60% through predictable parameter conventions"
tags: pagination, parameters, consistency
---

## Consistent Pagination Parameter Names

**Impact: HIGH (Reduces client integration time by 40-60% through predictable parameter conventions)**

When different endpoints use different parameter names for pagination, every client integration becomes a special case. Developers waste time reading docs for each endpoint instead of applying one convention everywhere. Pick one style and enforce it across the entire API.

## Incorrect

```http
# Users endpoint uses limit/offset
GET /api/v1/users?limit=20&offset=40

# Orders endpoint uses page/per_page
GET /api/v1/orders?page=3&per_page=20

# Products endpoint uses size/number
GET /api/v1/products?size=20&number=3

# Search endpoint uses count/start
GET /api/v1/search?count=20&start=40
```

**Problems:**
- Client SDKs need endpoint-specific pagination logic instead of a shared helper
- Developers must consult documentation for every endpoint to find the right parameter names
- Generic pagination UI components cannot be reused across different resource types
- Increased chance of bugs when developers assume one convention but the endpoint uses another

## Correct

```http
# Offset-based: use "page" + "per_page" everywhere
GET /api/v1/users?page=3&per_page=20
GET /api/v1/orders?page=3&per_page=20
GET /api/v1/products?page=3&per_page=20

# Cursor-based: use "cursor" + "limit" everywhere
GET /api/v1/events?cursor=eyJpZCI6MTIzfQ&limit=20
GET /api/v1/notifications?cursor=eyJpZCI6NDU2fQ&limit=20
GET /api/v1/logs?cursor=eyJpZCI6Nzg5fQ&limit=20
```

**Benefits:**
- One pagination helper in the client SDK handles all endpoints
- Developers learn the convention once and apply it everywhere
- Generic UI components (pagers, infinite scroll) work with any resource
- API documentation is simpler — pagination is explained once, not per-endpoint

Reference: [Microsoft REST API Guidelines - Pagination](https://github.com/microsoft/api-guidelines/blob/vNext/azure/Guidelines.md#pagination)
