---
title: Flexible Sorting Options
impact: HIGH
impactDescription: "Empowers clients to retrieve data in the exact order they need without server-side changes"
tags: sorting, query-params, ordering
---

## Flexible Sorting Options

**Impact: HIGH (Empowers clients to retrieve data in the exact order they need without server-side changes)**

APIs that return data in a single hardcoded order force clients to re-sort in memory, which is wasteful and breaks pagination. A flexible sorting API lets clients request the order they need, and the database handles it efficiently using indexes.

## Incorrect

```http
# No sort parameter — always returns by id ASC
GET /api/v1/products
```

```json
[
  { "id": 1, "name": "Alpha", "price": 29.99, "created_at": "2023-01-01T00:00:00Z" },
  { "id": 2, "name": "Beta", "price": 9.99, "created_at": "2024-06-15T00:00:00Z" }
]
```

```http
# Or: inconsistent sort parameters across endpoints
GET /api/v1/products?order_by=price&direction=desc
GET /api/v1/users?sortField=name&sortOrder=asc
```

**Problems:**
- Client must fetch all data and sort in memory, defeating the purpose of pagination
- No way to get "newest first" or "cheapest first" without client-side processing
- Inconsistent sort parameter names across endpoints increase integration complexity
- Hardcoded order may not match any client's primary use case

## Correct

```http
# Ascending sort (default direction)
GET /api/v1/products?sort=price

# Descending sort with "-" prefix
GET /api/v1/products?sort=-created_at

# Multiple sort fields (comma-separated)
GET /api/v1/products?sort=category,-price

# Combined with filtering and pagination
GET /api/v1/products?status=active&sort=-created_at&page=1&per_page=20
```

```json
{
  "data": [
    { "id": 47, "name": "New Widget", "price": 59.99, "created_at": "2024-06-15T00:00:00Z" },
    { "id": 32, "name": "Another Widget", "price": 39.99, "created_at": "2024-05-10T00:00:00Z" }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total_count": 84,
    "sort": "-created_at"
  }
}
```

**Benefits:**
- `-` prefix convention for descending is compact and widely adopted (JSON:API, many major APIs)
- Multiple sort fields let clients express complex ordering like "category ascending, then price descending"
- Sorting happens at the database level where indexes make it efficient
- Consistent `sort` parameter name across all endpoints simplifies client SDKs

Reference: [JSON:API - Sorting](https://jsonapi.org/format/#fetching-sorting)
