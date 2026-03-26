---
name: api-design-patterns
description: RESTful API design, error handling, versioning, and best practices. Use when designing APIs, reviewing endpoints, implementing error responses, or setting up API structure. Triggers on "design API", "review API", "REST best practices", or "API patterns".
license: MIT
metadata:
  author: agent-skills
  version: "2.0.0"
---

# API Design Patterns

RESTful API design principles for building consistent, developer-friendly APIs. Contains 38 rules across 7 categories covering resource design, error handling, security, pagination, versioning, response format, and documentation.

## Metadata

- **Version:** 2.0.0
- **Rule Count:** 38 rules across 7 categories
- **License:** MIT

## When to Apply

Reference these guidelines when:
- Designing new API endpoints
- Reviewing existing API structure
- Implementing error handling and validation
- Setting up pagination, filtering, and sorting
- Planning API versioning strategy
- Configuring API security (auth, CORS, rate limiting)
- Writing API documentation (OpenAPI/Swagger)

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Resource Design | CRITICAL | `rest-` |
| 2 | Error Handling | CRITICAL | `error-` |
| 3 | Security | CRITICAL | `sec-` |
| 4 | Pagination & Filtering | HIGH | `page-`, `filter-`, `sort-` |
| 5 | Versioning | HIGH | `ver-` |
| 6 | Response Format | MEDIUM | `resp-` |
| 7 | Documentation | MEDIUM | `doc-` |

## Quick Reference

### 1. Resource Design (CRITICAL)

- `rest-nouns-not-verbs` - Use nouns for endpoints, not verbs
- `rest-plural-resources` - Use plural resource names
- `rest-http-methods` - Correct HTTP method usage (GET, POST, PUT, PATCH, DELETE)
- `rest-nested-resources` - Proper resource nesting (max 2 levels)
- `rest-status-codes` - Appropriate HTTP status codes
- `rest-idempotency` - Idempotent operations with idempotency keys
- `rest-hateoas` - Hypermedia links for discoverability
- `rest-resource-actions` - Non-CRUD actions as sub-resources

### 2. Error Handling (CRITICAL)

- `error-consistent-format` - Consistent error response structure
- `error-meaningful-messages` - Helpful, actionable error messages
- `error-validation-details` - Field-level validation errors
- `error-error-codes` - Machine-readable error codes
- `error-no-stack-traces` - Never expose stack traces in production
- `error-request-id` - Include request IDs for debugging

### 3. Security (CRITICAL)

- `sec-authentication` - Proper auth implementation (OAuth2/JWT)
- `sec-authorization` - Resource-level permissions (RBAC)
- `sec-rate-limiting` - Prevent abuse with rate limiting
- `sec-input-validation` - Validate and sanitize all input
- `sec-cors-config` - CORS configuration with whitelists
- `sec-https-only` - Enforce HTTPS for all traffic
- `sec-sensitive-data` - Protect passwords, tokens, PII

### 4. Pagination & Filtering (HIGH)

- `page-cursor-based` - Cursor pagination for large datasets
- `page-offset-based` - Offset pagination for simple cases
- `page-consistent-params` - Consistent parameter naming
- `page-metadata` - Include pagination metadata in responses
- `filter-query-params` - Filter via query parameters
- `sort-flexible` - Flexible sorting with `-` prefix for descending

### 5. Versioning (HIGH)

- `ver-url-path` - Version in URL path (/api/v1/)
- `ver-header-based` - Version via Accept header
- `ver-backward-compatible` - Maintain backward compatibility
- `ver-deprecation` - Deprecation strategy with Sunset header

### 6. Response Format (MEDIUM)

- `resp-consistent-structure` - Consistent response envelope
- `resp-json-conventions` - JSON naming conventions
- `resp-partial-responses` - Field selection (sparse fieldsets)
- `resp-compression` - Response compression (gzip/Brotli)

### 7. Documentation (MEDIUM)

- `doc-openapi` - OpenAPI/Swagger specification
- `doc-examples` - Request/response examples
- `doc-changelog` - API changelog

## Essential Guidelines

### Resource Naming

```
# ❌ Verbs in URLs
GET    /getUsers
POST   /createUser

# ✅ Nouns with HTTP methods
GET    /users          # List users
POST   /users          # Create user
GET    /users/123      # Get user
PUT    /users/123      # Update user (full)
PATCH  /users/123      # Update user (partial)
DELETE /users/123      # Delete user
```

### Error Response Format

```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The request contains invalid data",
    "details": [
      {
        "field": "email",
        "code": "INVALID_FORMAT",
        "message": "Please provide a valid email address"
      }
    ],
    "request_id": "req_abc123"
  }
}
```

### Pagination

```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "per_page": 20,
    "total_pages": 10,
    "total_count": 195
  },
  "links": {
    "first": "/users?page=1&per_page=20",
    "prev": "/users?page=1&per_page=20",
    "next": "/users?page=3&per_page=20",
    "last": "/users?page=10&per_page=20"
  }
}
```

### Rate Limiting Headers

```
HTTP/1.1 200 OK
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 998
X-RateLimit-Reset: 1640995200
```

## How to Use

Read individual rule files for detailed explanations:

```
rules/rest-http-methods.md
rules/error-consistent-format.md
rules/page-cursor-based.md
rules/sec-authentication.md
rules/ver-url-path.md
rules/doc-openapi.md
```

## References

- [RESTful API Guidelines](https://restfulapi.net)
- [Zalando RESTful API Guidelines](https://zalando.github.io/restful-api-guidelines)
- [Microsoft API Guidelines](https://github.com/microsoft/api-guidelines)
- [Google API Design Guide](https://cloud.google.com/apis/design)
- [OpenAPI Specification](https://swagger.io/specification)

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
