# API Design Patterns v2.0.0

RESTful API design principles for building consistent, developer-friendly APIs.

## Overview

- Resource design with proper HTTP methods and status codes
- Consistent error handling with machine-readable codes
- Security (authentication, authorization, rate limiting, CORS)
- Cursor and offset pagination with filtering and sorting
- API versioning strategies and deprecation
- Response format conventions and compression
- OpenAPI documentation and changelog
- 38 rules across 7 categories

## Categories

### 1. Resource Design (Critical)
Nouns over verbs, plural resources, proper nesting, HTTP methods, status codes, idempotency, HATEOAS.

### 2. Error Handling (Critical)
Consistent error format, meaningful messages, validation details, error codes, request IDs.

### 3. Security (Critical)
Authentication (OAuth2/JWT), authorization (RBAC), rate limiting, input validation, CORS, HTTPS.

### 4. Pagination & Filtering (High)
Cursor-based and offset pagination, consistent parameters, filtering, sorting.

### 5. Versioning (High)
URL path versioning, header versioning, backward compatibility, deprecation strategy.

### 6. Response Format (Medium)
Consistent envelope, JSON naming conventions, sparse fieldsets, compression.

### 7. Documentation (Medium)
OpenAPI/Swagger specification, request/response examples, API changelog.

## Usage

```
Review my API design
Check REST best practices for these endpoints
Design error responses for my API
Set up pagination for this endpoint
```

## References

- [RESTful API Guidelines](https://restfulapi.net)
- [Zalando RESTful API Guidelines](https://zalando.github.io/restful-api-guidelines)
- [Microsoft API Guidelines](https://github.com/microsoft/api-guidelines)
- [OpenAPI Specification](https://swagger.io/specification)
