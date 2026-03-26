---
title: Cursor-Based Pagination for Large Datasets
impact: HIGH
impactDescription: "Constant O(1) pagination performance regardless of dataset depth"
tags: pagination, cursor, performance, large-datasets
---

## Cursor-Based Pagination for Large Datasets

**Impact: HIGH (Constant O(1) pagination performance regardless of dataset depth)**

Offset pagination degrades linearly as page depth increases because the database must scan and discard all preceding rows. Cursor-based pagination uses an opaque pointer to the last retrieved item, enabling the database to seek directly to the next batch with constant performance.

## Incorrect

```http
GET /api/v1/orders?offset=500000&limit=20
```

```json
{
  "data": [
    { "id": 500001, "total": 49.99 },
    { "id": 500002, "total": 129.00 }
  ]
}
```

```sql
-- Behind the scenes: database scans 500,000 rows before returning 20
SELECT * FROM orders ORDER BY id LIMIT 20 OFFSET 500000;
```

**Problems:**
- Query time grows linearly with offset — page 25,000 is dramatically slower than page 1
- Database must scan and discard all rows before the offset
- Inconsistent results if rows are inserted or deleted between page requests
- Memory and CPU waste on large tables (millions of rows)

## Correct

```http
GET /api/v1/orders?cursor=eyJpZCI6NTAwMDAwfQ&limit=20
```

```json
{
  "data": [
    { "id": 500001, "total": 49.99 },
    { "id": 500002, "total": 129.00 }
  ],
  "meta": {
    "has_more": true,
    "next_cursor": "eyJpZCI6NTAwMDIwfQ"
  },
  "links": {
    "next": "/api/v1/orders?cursor=eyJpZCI6NTAwMDIwfQ&limit=20"
  }
}
```

```sql
-- Behind the scenes: index seek, constant performance
SELECT * FROM orders WHERE id > 500000 ORDER BY id LIMIT 20;
```

**Benefits:**
- Constant query time regardless of how deep into the dataset the client has paginated
- Stable results — no skipped or duplicated items when data changes between requests
- Efficient use of database indexes (seek instead of scan)
- Opaque cursor allows server-side implementation changes without breaking clients

Reference: [Slack API - Pagination](https://api.slack.com/docs/pagination)
