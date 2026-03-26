# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Resource Design (rest)

**Impact:** CRITICAL
**Description:** Foundational REST principles for API endpoint design. Proper resource naming with nouns, plural collections, correct HTTP method semantics, appropriate status codes, idempotency, and HATEOAS links ensure APIs are intuitive, predictable, and follow industry standards.

## 2. Error Handling (error)

**Impact:** CRITICAL
**Description:** Consistent error response format across all endpoints. Machine-readable error codes, field-level validation details, meaningful messages, request IDs for debugging, and never exposing stack traces in production enable clients to handle errors programmatically.

## 3. Security (sec)

**Impact:** CRITICAL
**Description:** API security fundamentals. Authentication (OAuth2/JWT), authorization (RBAC), rate limiting, input validation and sanitization, CORS configuration with whitelists, HTTPS enforcement, and sensitive data protection prevent unauthorized access and common attack vectors.

## 4. Pagination & Filtering (page, filter, sort)

**Impact:** HIGH
**Description:** Efficient data retrieval for collections. Cursor pagination for large datasets, offset pagination for simple cases, consistent parameter naming, pagination metadata in responses, query parameter filtering, and flexible sorting enable clients to efficiently navigate large datasets.

## 5. Versioning (ver)

**Impact:** HIGH
**Description:** API versioning strategies for evolving APIs without breaking existing consumers. URL path versioning, header-based versioning, backward compatibility rules, and deprecation strategy with Sunset headers ensure smooth API evolution.

## 6. Response Format (resp)

**Impact:** MEDIUM
**Description:** Consistent response structure and conventions. Response envelopes, JSON naming conventions (camelCase vs snake_case), sparse fieldsets for bandwidth optimization, and response compression reduce payload sizes and improve developer experience.

## 7. Documentation (doc)

**Impact:** MEDIUM
**Description:** API documentation standards. OpenAPI/Swagger specifications, complete request/response examples, and API changelogs ensure consumers can discover, understand, and track changes to your API.
