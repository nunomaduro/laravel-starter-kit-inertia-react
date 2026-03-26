---
title: Field Selection (Sparse Fieldsets)
impact: MEDIUM
impactDescription: "Reduces payload size by 50-90% for field-heavy resources"
tags: fields, sparse-fieldsets, partial-response, bandwidth
---

## Field Selection (Sparse Fieldsets)

**Impact: MEDIUM (Reduces payload size by 50-90% for field-heavy resources)**

When an endpoint returns 50+ fields but the client only needs 3, the wasted bandwidth slows mobile apps, increases server serialization time, and drives up data transfer costs. Field selection lets clients request only what they need.

## Incorrect

```http
// ❌ Client only needs name and avatar, but gets everything
GET /api/users/123

// Response: 2.4 KB
{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "phone": "+1-555-0100",
    "avatar_url": "https://cdn.example.com/avatars/123.jpg",
    "date_of_birth": "1990-05-20",
    "bio": "Software engineer with 10 years of experience...",
    "address": {
      "street": "123 Main St",
      "city": "Springfield",
      "state": "IL",
      "zip_code": "62701",
      "country": "US"
    },
    "preferences": {
      "language": "en",
      "timezone": "America/Chicago",
      "theme": "dark",
      "notifications": {
        "email": true,
        "sms": false,
        "push": true
      }
    },
    "social_links": {
      "twitter": "https://twitter.com/janedoe",
      "linkedin": "https://linkedin.com/in/janedoe",
      "github": "https://github.com/janedoe"
    },
    "created_at": "2023-01-15T10:30:00Z",
    "updated_at": "2024-03-01T14:22:33Z",
    "last_login_at": "2024-03-10T09:15:00Z"
  }
}
```

**Problems:**
- Wasted bandwidth — client discards 90% of the response
- Slower responses on mobile or low-bandwidth connections
- Server serializes and queries for unused data
- Higher data transfer costs at scale

## Correct

### Query Parameter Approach

```http
// ✅ Client requests only the fields it needs
GET /api/users/123?fields=id,first_name,last_name,avatar_url

// Response: 180 bytes (93% smaller)
{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "last_name": "Doe",
    "avatar_url": "https://cdn.example.com/avatars/123.jpg"
  }
}
```

### Nested Fields

```http
// ✅ Dot notation for nested field selection
GET /api/users/123?fields=id,first_name,address.city,address.country

{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "address": {
      "city": "Springfield",
      "country": "US"
    }
  }
}
```

### Collections with Field Selection

```http
// ✅ Sparse fieldsets on collections — big savings at scale
GET /api/users?fields=id,first_name,avatar_url&per_page=50

// Instead of 50 × 2.4 KB = 120 KB
// Now 50 × 120 bytes = 6 KB
{
  "data": [
    { "id": 123, "first_name": "Jane", "avatar_url": "https://cdn.example.com/avatars/123.jpg" },
    { "id": 456, "first_name": "John", "avatar_url": "https://cdn.example.com/avatars/456.jpg" }
  ],
  "meta": {
    "total": 142,
    "page": 1,
    "per_page": 50
  }
}
```

### JSON:API Sparse Fieldsets

```http
// ✅ JSON:API uses typed field selection
GET /api/users?fields[users]=first_name,avatar_url&fields[companies]=name

{
  "data": [
    {
      "type": "users",
      "id": "123",
      "attributes": {
        "first_name": "Jane",
        "avatar_url": "https://cdn.example.com/avatars/123.jpg"
      },
      "relationships": {
        "company": { "data": { "type": "companies", "id": "7" } }
      }
    }
  ],
  "included": [
    {
      "type": "companies",
      "id": "7",
      "attributes": { "name": "Acme Corp" }
    }
  ]
}
```

**Benefits:**
- Payload size drops 50-90% for typical use cases
- Faster responses, especially on mobile networks
- Server can optimize database queries to fetch only requested columns
- Reduced data transfer costs at high request volumes

Reference: [Google API Design Guide — Standard Fields](https://cloud.google.com/apis/design/standard_fields)
