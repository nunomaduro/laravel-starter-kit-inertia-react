---
title: Use Machine-Readable Error Codes
impact: CRITICAL
impactDescription: "Enables programmatic error handling and client recovery"
tags: errors, error-codes, automation, monitoring
---

## Use Machine-Readable Error Codes

**Impact: CRITICAL (Enables programmatic error handling and client recovery)**

Include standardized, machine-readable error codes alongside human-readable messages to enable programmatic error handling.

## Incorrect

```json
// ❌ Only human-readable messages
{
  "error": "The user was not found"
}

// ❌ HTTP status codes only
{
  "status": 404
}

// ❌ Inconsistent or vague codes
{
  "error_code": "ERR001"
}

{
  "code": 1234
}

{
  "error_type": "bad_thing_happened"
}
```

```javascript
// ❌ No error codes for programmatic handling
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  if (!user) {
    // Client can't reliably detect "not found" vs other 404s
    res.status(404).json({ message: 'User not found' });
  }
});
```

**Problems:**
- Clients must parse human-readable messages which change with localization
- No stable identifier for programmatic error handling
- Numeric codes are meaningless without a reference table
- Inconsistent code formats across endpoints
- Cannot implement targeted recovery strategies per error type
- Monitoring dashboards cannot categorize errors precisely

## Correct

```javascript
// ✅ Define error codes as constants
const ErrorCodes = {
  // Authentication & Authorization
  AUTH_TOKEN_MISSING: 'auth_token_missing',
  AUTH_TOKEN_INVALID: 'auth_token_invalid',
  AUTH_TOKEN_EXPIRED: 'auth_token_expired',
  AUTH_INSUFFICIENT_PERMISSIONS: 'auth_insufficient_permissions',

  // Validation
  VALIDATION_ERROR: 'validation_error',
  VALIDATION_REQUIRED_FIELD: 'validation_required_field',
  VALIDATION_INVALID_FORMAT: 'validation_invalid_format',
  VALIDATION_OUT_OF_RANGE: 'validation_out_of_range',

  // Resources
  RESOURCE_NOT_FOUND: 'resource_not_found',
  RESOURCE_ALREADY_EXISTS: 'resource_already_exists',
  RESOURCE_CONFLICT: 'resource_conflict',
  RESOURCE_DELETED: 'resource_deleted',

  // Rate Limiting
  RATE_LIMIT_EXCEEDED: 'rate_limit_exceeded',

  // Business Logic
  INSUFFICIENT_FUNDS: 'insufficient_funds',
  INVENTORY_UNAVAILABLE: 'inventory_unavailable',
  ORDER_CANNOT_BE_CANCELLED: 'order_cannot_be_cancelled',
  SUBSCRIPTION_EXPIRED: 'subscription_expired',

  // Server Errors
  INTERNAL_ERROR: 'internal_error',
  SERVICE_UNAVAILABLE: 'service_unavailable',
  DEPENDENCY_ERROR: 'dependency_error'
};

// Error factory
class APIError extends Error {
  constructor(code, message, statusCode = 400, details = null) {
    super(message);
    this.code = code;
    this.statusCode = statusCode;
    this.details = details;
  }
}

// Usage in routes
app.get('/users/:id', async (req, res, next) => {
  try {
    const user = await db.findUser(req.params.id);
    if (!user) {
      throw new APIError(
        ErrorCodes.RESOURCE_NOT_FOUND,
        'User not found',
        404,
        { resourceType: 'user', resourceId: req.params.id }
      );
    }
    res.json(user);
  } catch (error) {
    next(error);
  }
});

app.post('/orders', async (req, res, next) => {
  try {
    const product = await db.findProduct(req.body.productId);

    if (product.stock < req.body.quantity) {
      throw new APIError(
        ErrorCodes.INVENTORY_UNAVAILABLE,
        'Not enough items in stock',
        422,
        {
          requested: req.body.quantity,
          available: product.stock,
          productId: req.body.productId
        }
      );
    }

    // Process order...
  } catch (error) {
    next(error);
  }
});

// Error handler
app.use((err, req, res, next) => {
  if (err instanceof APIError) {
    return res.status(err.statusCode).json({
      error: {
        code: err.code,
        message: err.message,
        details: err.details
      }
    });
  }

  // Unknown error
  res.status(500).json({
    error: {
      code: ErrorCodes.INTERNAL_ERROR,
      message: 'An unexpected error occurred'
    }
  });
});
```

```python
# ✅ Python with error codes
from enum import Enum
from fastapi import FastAPI, HTTPException
from typing import Optional, Any

class ErrorCode(str, Enum):
    # Authentication
    AUTH_TOKEN_MISSING = "auth_token_missing"
    AUTH_TOKEN_INVALID = "auth_token_invalid"
    AUTH_TOKEN_EXPIRED = "auth_token_expired"
    AUTH_INSUFFICIENT_PERMISSIONS = "auth_insufficient_permissions"

    # Validation
    VALIDATION_ERROR = "validation_error"
    VALIDATION_REQUIRED_FIELD = "validation_required_field"

    # Resources
    RESOURCE_NOT_FOUND = "resource_not_found"
    RESOURCE_ALREADY_EXISTS = "resource_already_exists"
    RESOURCE_CONFLICT = "resource_conflict"

    # Business Logic
    INSUFFICIENT_FUNDS = "insufficient_funds"
    INVENTORY_UNAVAILABLE = "inventory_unavailable"

    # Server
    INTERNAL_ERROR = "internal_error"
    SERVICE_UNAVAILABLE = "service_unavailable"

class APIError(Exception):
    def __init__(
        self,
        code: ErrorCode,
        message: str,
        status_code: int = 400,
        details: Optional[dict] = None
    ):
        self.code = code
        self.message = message
        self.status_code = status_code
        self.details = details

app = FastAPI()

@app.exception_handler(APIError)
async def api_error_handler(request, exc: APIError):
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "error": {
                "code": exc.code.value,
                "message": exc.message,
                "details": exc.details
            }
        }
    )

@app.get("/users/{user_id}")
async def get_user(user_id: int):
    user = await db.get_user(user_id)
    if not user:
        raise APIError(
            code=ErrorCode.RESOURCE_NOT_FOUND,
            message=f"User with ID {user_id} not found",
            status_code=404,
            details={"resource_type": "user", "resource_id": user_id}
        )
    return user
```

```json
// ✅ Error response with code
{
  "error": {
    "code": "inventory_unavailable",
    "message": "Not enough items in stock to fulfill your order",
    "details": {
      "productId": "prod_123",
      "productName": "Widget Pro",
      "requested": 10,
      "available": 3
    }
  }
}
```

```typescript
// ✅ Client-side error handling
async function createOrder(orderData: OrderData): Promise<Order> {
  const response = await fetch('/api/orders', {
    method: 'POST',
    body: JSON.stringify(orderData)
  });

  if (!response.ok) {
    const error = await response.json();

    // Programmatic handling based on error code
    switch (error.error.code) {
      case 'inventory_unavailable':
        showInventoryWarning(error.error.details);
        break;

      case 'insufficient_funds':
        redirectToPaymentUpdate();
        break;

      case 'auth_token_expired':
        await refreshToken();
        return createOrder(orderData); // Retry

      case 'rate_limit_exceeded':
        await delay(error.error.details.retryAfter * 1000);
        return createOrder(orderData); // Retry

      default:
        showGenericError(error.error.message);
    }

    throw new APIError(error);
  }

  return response.json();
}
```

## Error Code Naming Conventions

| Category | Pattern | Examples |
|----------|---------|----------|
| Auth | `auth_*` | `auth_token_expired`, `auth_invalid_credentials` |
| Validation | `validation_*` | `validation_error`, `validation_invalid_email` |
| Resource | `resource_*` | `resource_not_found`, `resource_conflict` |
| Business | Domain-specific | `insufficient_funds`, `inventory_unavailable` |
| Rate Limit | `rate_limit_*` | `rate_limit_exceeded` |
| Server | `internal_*` or `service_*` | `internal_error`, `service_unavailable` |

**Benefits:**
- Code can switch on error codes to take appropriate recovery action
- Error codes remain stable even when messages change or are localized
- Error codes can be documented and referenced in API docs
- Precise alerting and monitoring dashboards based on error categories
- Tests can assert on specific error codes rather than message strings
- Messages can be translated while codes stay constant across locales

Reference: [Google Cloud API Error Model](https://cloud.google.com/apis/design/errors)
