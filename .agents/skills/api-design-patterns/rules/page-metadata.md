---
title: Include Pagination Metadata in Responses
impact: HIGH
impactDescription: "Eliminates guesswork pagination and reduces unnecessary API calls by 30-50%"
tags: pagination, metadata, total-count
---

## Include Pagination Metadata in Responses

**Impact: HIGH (Eliminates guesswork pagination and reduces unnecessary API calls by 30-50%)**

Without pagination metadata, clients must make an extra request to discover there are no more results, or blindly paginate until they receive an empty response. Including metadata in every paginated response gives clients everything they need to render UI controls and make efficient decisions about fetching more data.

## Incorrect

```http
GET /api/v1/products?page=2&per_page=20
```

```json
[
  { "id": 21, "name": "Widget A", "price": 9.99 },
  { "id": 22, "name": "Widget B", "price": 14.99 }
]
```

**Problems:**
- Client cannot distinguish "page has fewer items than per_page" from "this is the last page"
- No total count means page selector UIs and "X results found" labels are impossible
- Client must request the next page to discover it is empty — wasting a round trip
- No navigation links forces clients to manually construct pagination URLs

## Correct

```http
GET /api/v1/products?page=2&per_page=20
```

```json
{
  "data": [
    { "id": 21, "name": "Widget A", "price": 9.99 },
    { "id": 22, "name": "Widget B", "price": 14.99 }
  ],
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total_count": 195,
    "total_pages": 10,
    "has_more": true
  },
  "links": {
    "first": "/api/v1/products?page=1&per_page=20",
    "prev": "/api/v1/products?page=1&per_page=20",
    "next": "/api/v1/products?page=3&per_page=20",
    "last": "/api/v1/products?page=10&per_page=20"
  }
}
```

**Benefits:**
- `has_more` lets infinite-scroll UIs know when to stop fetching without an extra empty request
- `total_count` and `total_pages` enable "Showing 21-40 of 195 results" display
- Navigation links let clients follow links instead of constructing URLs, reducing coupling
- Consistent envelope structure makes client-side deserialization predictable across all endpoints

Reference: [GitHub REST API - Pagination](https://docs.github.com/en/rest/using-the-rest-api/using-pagination-in-the-rest-api)
