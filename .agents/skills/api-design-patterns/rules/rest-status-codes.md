---
title: Use Appropriate HTTP Status Codes
impact: CRITICAL
impactDescription: "Enables proper client handling, caching, and monitoring"
tags: rest, http-status, errors, semantics
---

## Use Appropriate HTTP Status Codes

**Impact: CRITICAL (Enables proper client handling, caching, and monitoring)**

Return semantically correct HTTP status codes that accurately describe the result of the operation.

## Incorrect

```javascript
// ❌ Always returning 200
app.post('/users', async (req, res) => {
  try {
    const user = await db.createUser(req.body);
    res.status(200).json(user); // Should be 201 Created
  } catch (error) {
    res.status(200).json({ error: error.message }); // Error with 200!
  }
});

app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) {
    res.status(200).json({ error: 'Not found' }); // Should be 404
  }
  res.status(200).json(user);
});

app.delete('/users/:id', async (req, res) => {
  await db.deleteUser(req.params.id);
  res.status(200).json({ message: 'Deleted' }); // 204 is more appropriate
});
```

```json
// ❌ Error responses with 200 status
HTTP/1.1 200 OK
{
  "success": false,
  "error": "User not found"
}
```

**Problems:**
- Clients cannot distinguish success from failure without parsing the body
- HTTP caches will cache error responses as successful
- Monitoring and APM tools cannot track real error rates
- Retry logic cannot determine whether to retry based on status code
- Breaks HTTP standards and confuses developers

## Correct

```javascript
const express = require('express');
const router = express.Router();

// ✅ 200 OK - Successful GET, PUT, PATCH
router.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) {
    return res.status(404).json({
      error: 'not_found',
      message: 'User not found'
    });
  }
  res.status(200).json(user);
});

// ✅ 201 Created - Successful POST that creates a resource
router.post('/users', async (req, res) => {
  const user = await db.createUser(req.body);
  res.status(201)
    .location(`/users/${user.id}`)
    .json(user);
});

// ✅ 204 No Content - Successful DELETE or update with no response body
router.delete('/users/:id', async (req, res) => {
  const deleted = await db.deleteUser(req.params.id);
  if (!deleted) {
    return res.status(404).json({
      error: 'not_found',
      message: 'User not found'
    });
  }
  res.status(204).send();
});

// ✅ 400 Bad Request - Invalid input
router.post('/users', async (req, res) => {
  if (!req.body.email) {
    return res.status(400).json({
      error: 'validation_error',
      message: 'Email is required',
      field: 'email'
    });
  }
  // ...
});

// ✅ 401 Unauthorized - Not authenticated
router.use((req, res, next) => {
  if (!req.headers.authorization) {
    return res.status(401).json({
      error: 'unauthorized',
      message: 'Authentication required'
    });
  }
  next();
});

// ✅ 403 Forbidden - Authenticated but not authorized
router.delete('/users/:id', async (req, res) => {
  if (req.user.id !== req.params.id && !req.user.isAdmin) {
    return res.status(403).json({
      error: 'forbidden',
      message: 'You cannot delete other users'
    });
  }
  // ...
});

// ✅ 409 Conflict - Resource conflict
router.post('/users', async (req, res) => {
  const exists = await db.userExists(req.body.email);
  if (exists) {
    return res.status(409).json({
      error: 'conflict',
      message: 'User with this email already exists'
    });
  }
  // ...
});

// ✅ 422 Unprocessable Entity - Semantic validation error
router.post('/orders', async (req, res) => {
  const product = await db.findProduct(req.body.productId);
  if (product.stock < req.body.quantity) {
    return res.status(422).json({
      error: 'unprocessable_entity',
      message: 'Insufficient stock',
      available: product.stock
    });
  }
  // ...
});
```

## Common Status Codes Reference

### Success (2xx)
| Code | Name | Use Case |
|------|------|----------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST creating resource |
| 202 | Accepted | Request accepted for async processing |
| 204 | No Content | Successful DELETE or update with no body |

### Client Errors (4xx)
| Code | Name | Use Case |
|------|------|----------|
| 400 | Bad Request | Malformed syntax, invalid JSON |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but not authorized |
| 404 | Not Found | Resource doesn't exist |
| 405 | Method Not Allowed | HTTP method not supported |
| 409 | Conflict | Resource conflict (duplicate) |
| 422 | Unprocessable Entity | Validation/business logic error |
| 429 | Too Many Requests | Rate limit exceeded |

### Server Errors (5xx)
| Code | Name | Use Case |
|------|------|----------|
| 500 | Internal Server Error | Unexpected server error |
| 502 | Bad Gateway | Upstream service error |
| 503 | Service Unavailable | Server temporarily unavailable |
| 504 | Gateway Timeout | Upstream service timeout |

**Benefits:**
- Status codes convey meaning before clients parse the response body
- HTTP clients, browsers, and tools handle different status codes appropriately
- Correct codes enable proper caching (2xx cached, 4xx/5xx not)
- Infrastructure and APM tools use status codes to track error rates and API health
- Clients can implement smart retry logic (retry 503, don't retry 400)
- Following HTTP standards ensures interoperability with tools and services

Reference: [MDN HTTP Status Codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status)
