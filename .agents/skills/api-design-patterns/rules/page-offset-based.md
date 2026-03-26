---
title: Offset Pagination for Simple Cases
impact: HIGH
impactDescription: "Enables random page access and predictable navigation for small-to-medium datasets"
tags: pagination, offset, page-number
---

## Offset Pagination for Simple Cases

**Impact: HIGH (Enables random page access and predictable navigation for small-to-medium datasets)**

Offset-based pagination is the most intuitive model for clients that need numbered pages, total counts, and the ability to jump to arbitrary pages. It works well for small-to-medium datasets and should always include metadata and navigation links so clients never have to guess the pagination state.

## Incorrect

```http
GET /api/v1/articles?page=2
```

```json
[
  { "id": 21, "title": "Introduction to REST" },
  { "id": 22, "title": "API Versioning" }
]
```

**Problems:**
- Bare array provides no pagination context — client cannot determine total pages or current position
- No navigation links — client must construct URLs manually and guess when to stop
- No indication of page size — unclear how many items per page the server returned
- Client cannot build a page selector UI without total count information

## Correct

```http
GET /api/v1/articles?page=2&per_page=20
```

```json
{
  "data": [
    { "id": 21, "title": "Introduction to REST" },
    { "id": 22, "title": "API Versioning" }
  ],
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total_pages": 10,
    "total_count": 195
  },
  "links": {
    "first": "/api/v1/articles?page=1&per_page=20",
    "prev": "/api/v1/articles?page=1&per_page=20",
    "next": "/api/v1/articles?page=3&per_page=20",
    "last": "/api/v1/articles?page=10&per_page=20"
  }
}
```

**Benefits:**
- Full pagination envelope lets clients render page selectors, "showing X of Y" indicators, and navigation controls
- Navigation links follow HATEOAS principles — clients follow links rather than constructing URLs
- `prev` and `next` are null-safe (omitted on first and last pages respectively)
- `per_page` parameter gives clients control over batch size within server-enforced limits

Reference: [JSON:API - Pagination](https://jsonapi.org/format/#fetching-pagination)
