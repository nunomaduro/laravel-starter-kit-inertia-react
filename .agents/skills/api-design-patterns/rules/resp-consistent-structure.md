---
title: Consistent Response Envelope
impact: MEDIUM
impactDescription: "Reduces client-side parsing complexity by 40-60%"
tags: response, envelope, structure, consistency
---

## Consistent Response Envelope

**Impact: MEDIUM (Reduces client-side parsing complexity by 40-60%)**

A consistent response envelope allows API consumers to build reusable parsing logic that works across every endpoint. Without it, clients must special-case each endpoint's response shape, leading to fragile integration code and slower onboarding.

## Incorrect

```json
// ❌ Different shapes for different endpoints

// GET /users/123 — bare object
{
  "id": 123,
  "name": "Jane Doe",
  "email": "jane@example.com"
}

// GET /users — bare array
[
  { "id": 123, "name": "Jane Doe" },
  { "id": 456, "name": "John Smith" }
]

// GET /orders — nested differently
{
  "orders": [
    { "id": 1, "total": 99.99 }
  ],
  "count": 1
}

// GET /products/42 — yet another shape
{
  "product": {
    "id": 42,
    "title": "Widget"
  },
  "status": "ok"
}
```

**Problems:**
- Clients cannot predict the response structure for new endpoints
- Every endpoint requires unique parsing logic
- Impossible to build a generic API client or SDK
- Adding metadata (pagination, rate limits) requires breaking changes

## Correct

### Simple Envelope Style

```json
// ✅ Single resource — GET /users/123
{
  "data": {
    "id": 123,
    "name": "Jane Doe",
    "email": "jane@example.com"
  },
  "meta": {
    "request_id": "req_abc123"
  }
}

// ✅ Collection — GET /users?page=1&per_page=20
{
  "data": [
    { "id": 123, "name": "Jane Doe" },
    { "id": 456, "name": "John Smith" }
  ],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 142,
    "total_pages": 8,
    "request_id": "req_def456"
  }
}

// ✅ Empty collection — GET /users?status=banned
{
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 0,
    "total_pages": 0,
    "request_id": "req_ghi789"
  }
}
```

### JSON:API Style

```json
// ✅ Single resource — GET /users/123
{
  "data": {
    "type": "users",
    "id": "123",
    "attributes": {
      "name": "Jane Doe",
      "email": "jane@example.com"
    },
    "relationships": {
      "company": {
        "data": { "type": "companies", "id": "7" }
      }
    }
  },
  "included": [
    {
      "type": "companies",
      "id": "7",
      "attributes": {
        "name": "Acme Corp"
      }
    }
  ]
}

// ✅ Collection — GET /users
{
  "data": [
    {
      "type": "users",
      "id": "123",
      "attributes": { "name": "Jane Doe" }
    },
    {
      "type": "users",
      "id": "456",
      "attributes": { "name": "John Smith" }
    }
  ],
  "meta": {
    "total": 142,
    "page": 1,
    "per_page": 20
  },
  "links": {
    "self": "/users?page=1",
    "next": "/users?page=2",
    "last": "/users?page=8"
  }
}
```

**Benefits:**
- Clients build one parser that works for every endpoint
- Metadata (pagination, request IDs, rate limits) has a predictable location
- New metadata can be added to `meta` without breaking existing clients
- SDKs and generic API wrappers become straightforward to implement

Reference: [JSON:API Specification](https://jsonapi.org/format/)
