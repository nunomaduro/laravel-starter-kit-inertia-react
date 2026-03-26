---
title: Consistent Error Response Format
impact: CRITICAL
impactDescription: "Enables predictable error handling across API"
tags: errors, consistency, response-format, client-experience
---

## Consistent Error Response Format

**Impact: CRITICAL (Enables predictable error handling across API)**

Inconsistent error formats force API consumers to handle multiple error structures, leading to fragile client code. A consistent error format makes APIs predictable, easier to debug, and simpler to integrate.

## Incorrect

```json
// ❌ Different formats across endpoints
// Endpoint A
{ "error": "Not found" }

// Endpoint B
{ "message": "Invalid email", "status": 400 }

// Endpoint C
{ "errors": ["Field required", "Invalid format"] }

// Endpoint D
{
  "success": false,
  "errorMessage": "Something went wrong"
}

// Endpoint E - just a string
"User not found"
```

**Problems:**
- Clients can't predict error structure
- Different parsing logic needed for each endpoint
- Hard to build generic error handlers
- Inconsistent developer experience

## Correct

### Standard Error Envelope

```json
// ✅ Every error follows the same structure
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable message",
    "details": [],
    "request_id": "req_abc123"
  }
}
```

### Validation Errors (422)

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
      },
      {
        "field": "password",
        "code": "TOO_SHORT",
        "message": "Password must be at least 8 characters",
        "meta": {
          "min_length": 8,
          "actual_length": 5
        }
      },
      {
        "field": "age",
        "code": "OUT_OF_RANGE",
        "message": "Age must be between 18 and 120",
        "meta": {
          "min": 18,
          "max": 120,
          "actual": 15
        }
      }
    ],
    "request_id": "req_abc123"
  }
}
```

### Not Found (404)

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "User with ID 'usr_123' not found",
    "details": [
      {
        "resource": "user",
        "field": "id",
        "value": "usr_123"
      }
    ],
    "request_id": "req_def456"
  }
}
```

### Authentication Error (401)

```json
{
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Authentication required",
    "details": [
      {
        "code": "TOKEN_EXPIRED",
        "message": "Your access token has expired"
      }
    ],
    "request_id": "req_ghi789"
  }
}
```

### Authorization Error (403)

```json
{
  "error": {
    "code": "FORBIDDEN",
    "message": "You don't have permission to access this resource",
    "details": [
      {
        "resource": "order",
        "action": "delete",
        "reason": "Only admins can delete orders"
      }
    ],
    "request_id": "req_jkl012"
  }
}
```

### Conflict Error (409)

```json
{
  "error": {
    "code": "CONFLICT",
    "message": "A user with this email already exists",
    "details": [
      {
        "field": "email",
        "code": "DUPLICATE",
        "value": "john@example.com"
      }
    ],
    "request_id": "req_mno345"
  }
}
```

### Rate Limit Error (429)

```json
{
  "error": {
    "code": "RATE_LIMITED",
    "message": "Too many requests. Please retry after 60 seconds.",
    "details": [
      {
        "limit": 100,
        "window": "1 minute",
        "retry_after": 60
      }
    ],
    "request_id": "req_pqr678"
  }
}
```

### Server Error (500)

```json
{
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "An unexpected error occurred. Please try again later.",
    "request_id": "req_stu901"
  }
}
```

**Note:** Never expose stack traces or internal details in production.

## Implementation

### TypeScript/Node.js

```typescript
// ✅ Error classes
abstract class AppError extends Error {
  abstract readonly code: string;
  abstract readonly statusCode: number;
  readonly details: ErrorDetail[];

  constructor(message: string, details: ErrorDetail[] = []) {
    super(message);
    this.details = details;
  }

  toJSON() {
    return {
      error: {
        code: this.code,
        message: this.message,
        details: this.details.length > 0 ? this.details : undefined,
      }
    };
  }
}

class ValidationError extends AppError {
  readonly code = 'VALIDATION_ERROR';
  readonly statusCode = 422;
}

class NotFoundError extends AppError {
  readonly code = 'NOT_FOUND';
  readonly statusCode = 404;
}

class UnauthorizedError extends AppError {
  readonly code = 'UNAUTHORIZED';
  readonly statusCode = 401;
}

// Error handler middleware
function errorHandler(err, req, res, next) {
  const requestId = req.id || generateRequestId();

  if (err instanceof AppError) {
    return res.status(err.statusCode).json({
      error: {
        ...err.toJSON().error,
        request_id: requestId,
      }
    });
  }

  // Log unexpected errors
  logger.error('Unexpected error', { error: err, requestId });

  // Generic response for unknown errors
  res.status(500).json({
    error: {
      code: 'INTERNAL_ERROR',
      message: 'An unexpected error occurred',
      request_id: requestId,
    }
  });
}
```

### Laravel/PHP

```php
<?php

// ✅ app/Exceptions/AppException.php
abstract class AppException extends Exception
{
    abstract public function getErrorCode(): string;
    abstract public function getStatusCode(): int;

    protected array $details = [];

    public function setDetails(array $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'details' => $this->details ?: null,
                'request_id' => request()->id(),
            ],
        ], $this->getStatusCode());
    }
}

class ValidationException extends AppException
{
    public function getErrorCode(): string
    {
        return 'VALIDATION_ERROR';
    }

    public function getStatusCode(): int
    {
        return 422;
    }

    public static function fromValidator(Validator $validator): self
    {
        $details = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $details[] = [
                'field' => $field,
                'code' => 'INVALID',
                'message' => $messages[0],
            ];
        }

        return (new self('The request contains invalid data'))
            ->setDetails($details);
    }
}

// Handler
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof AppException) {
            return $e->render();
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Resource not found',
                    'request_id' => $request->id(),
                ],
            ], 404);
        }

        // Log and return generic error
        Log::error($e->getMessage(), ['exception' => $e]);

        return response()->json([
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'An unexpected error occurred',
                'request_id' => $request->id(),
            ],
        ], 500);
    }
}
```

## Error Code Naming

```
// ✅ Use SCREAMING_SNAKE_CASE
VALIDATION_ERROR
NOT_FOUND
UNAUTHORIZED
FORBIDDEN
RATE_LIMITED
INTERNAL_ERROR

// ✅ Be specific
INVALID_EMAIL_FORMAT
PASSWORD_TOO_SHORT
DUPLICATE_EMAIL
TOKEN_EXPIRED
INSUFFICIENT_FUNDS

// ❌ Avoid vague codes
ERROR
FAILED
BAD_REQUEST
```

**Benefits:**
- Predictable API behavior across all endpoints
- Reusable client-side error handling logic
- Easier debugging with request IDs
- Clear error codes for programmatic handling
- Human-readable messages for display
- Detailed validation feedback for field-level errors

Reference: [RFC 7807 - Problem Details for HTTP APIs](https://www.rfc-editor.org/rfc/rfc7807)
