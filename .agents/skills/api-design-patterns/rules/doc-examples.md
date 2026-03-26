---
title: Request/Response Examples
impact: MEDIUM
impactDescription: "Reduces time-to-first-successful-call from hours to minutes"
tags: documentation, examples, curl, requests
---

## Request/Response Examples

**Impact: MEDIUM (Reduces time-to-first-successful-call from hours to minutes)**

Schema definitions tell developers what is possible, but examples show them what to actually do. A developer can copy a curl command, run it, and have a working integration in minutes instead of interpreting abstract schemas for hours.

## Incorrect

```
// ❌ Documentation with only schema definitions, no examples

POST /users
  Request Body: CreateUserRequest schema
  Response: User schema (201) | Error schema (422)

Parameters:
  first_name: string, required
  last_name: string, required
  email: string, required, format: email
  password: string, required, minLength: 8

// Developer must guess: What does a real request look like?
// What headers are needed? What does the response actually contain?
// What does a validation error look like?
```

**Problems:**
- Developers must mentally construct requests from abstract schema descriptions
- No way to quickly copy-paste and test an endpoint
- Error response shapes are a mystery until they happen in production
- Onboarding new API consumers takes hours instead of minutes

## Correct

### Complete Request Example

```bash
# ✅ Create a user — complete curl example
curl -X POST https://api.example.com/v1/users \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "password": "secureP@ssw0rd"
  }'
```

### Success Response Example

```http
HTTP/1.1 201 Created
Content-Type: application/json
Location: /v1/users/123
```

```json
// ✅ 201 Created — show exact response body
{
  "data": {
    "id": 123,
    "first_name": "Jane",
    "last_name": "Doe",
    "email": "jane@example.com",
    "status": "active",
    "created_at": "2024-03-15T10:30:00Z"
  },
  "meta": {
    "request_id": "req_abc123"
  }
}
```

### Validation Error Example

```bash
# ✅ Show what happens with invalid input
curl -X POST https://api.example.com/v1/users \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "",
    "email": "not-an-email",
    "password": "short"
  }'
```

```json
// ✅ 422 Unprocessable Entity — show exact error shape
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The request contains invalid data",
    "details": [
      {
        "field": "first_name",
        "code": "REQUIRED",
        "message": "First name is required"
      },
      {
        "field": "last_name",
        "code": "REQUIRED",
        "message": "Last name is required"
      },
      {
        "field": "email",
        "code": "INVALID_FORMAT",
        "message": "Please provide a valid email address"
      },
      {
        "field": "password",
        "code": "TOO_SHORT",
        "message": "Password must be at least 8 characters"
      }
    ],
    "request_id": "req_def456"
  }
}
```

### Authentication Error Example

```bash
# ✅ Show what happens with missing/invalid token
curl -X GET https://api.example.com/v1/users \
  -H "Content-Type: application/json"
```

```json
// ✅ 401 Unauthorized
{
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required. Provide a valid Bearer token.",
    "request_id": "req_ghi789"
  }
}
```

### List Endpoint Example

```bash
# ✅ GET with query parameters
curl -X GET "https://api.example.com/v1/users?status=active&page=2&per_page=10" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Accept: application/json"
```

```json
// ✅ 200 OK — show pagination metadata
{
  "data": [
    {
      "id": 123,
      "first_name": "Jane",
      "last_name": "Doe",
      "email": "jane@example.com",
      "status": "active"
    },
    {
      "id": 456,
      "first_name": "John",
      "last_name": "Smith",
      "email": "john@example.com",
      "status": "active"
    }
  ],
  "meta": {
    "page": 2,
    "per_page": 10,
    "total": 142,
    "total_pages": 15,
    "request_id": "req_jkl012"
  }
}
```

### Documentation Checklist

```
Every endpoint should include:
  ✅ Complete curl command with headers and body
  ✅ At least one success response (with realistic data)
  ✅ At least one error response (validation or auth)
  ✅ Query parameter examples for GET endpoints
  ✅ Response headers when relevant (Location, Link, etc.)
```

**Benefits:**
- Developers copy-paste and have a working request in seconds
- Error examples set correct expectations for client-side error handling
- Realistic data in examples clarifies field formats and semantics
- Reduces support tickets from confused API consumers

Reference: [Stripe API Documentation](https://stripe.com/docs/api) — gold standard for API examples
