---
title: Never Expose Stack Traces in Production
impact: CRITICAL
impactDescription: "Prevents security vulnerabilities and information disclosure"
tags: errors, security, production, sensitive-data
---

## Never Expose Stack Traces in Production

**Impact: CRITICAL (Prevents security vulnerabilities and information disclosure)**

Stack traces and internal error details should never be exposed to API clients in production environments, as they reveal implementation details and potential vulnerabilities.

## Incorrect

```json
// ❌ Full stack trace in production response
{
  "error": "Cannot read property 'id' of undefined",
  "stack": "TypeError: Cannot read property 'id' of undefined\n    at getUserOrders (/app/src/controllers/orders.js:45:23)\n    at Layer.handle [as handle_request] (/app/node_modules/express/lib/router/layer.js:95:5)\n    at next (/app/node_modules/express/lib/router/route.js:137:13)\n    at authenticate (/app/src/middleware/auth.js:28:5)\n    at /app/node_modules/express/lib/router/index.js:284:15"
}

// ❌ Database error details exposed
{
  "error": "SequelizeConnectionError: Connection refused to host 'db.internal.company.com' port 5432",
  "sql": "SELECT * FROM users WHERE id = 1 AND deleted_at IS NULL"
}

// ❌ Internal paths and configuration
{
  "error": "ENOENT: no such file or directory, open '/var/app/config/secrets.json'"
}
```

```javascript
// ❌ Exposing all error details
app.use((err, req, res, next) => {
  res.status(500).json({
    error: err.message,
    stack: err.stack,  // Never do this in production!
    code: err.code
  });
});
```

**Problems:**
- Stack traces reveal file paths, dependencies, and code structure attackers can exploit
- Database error messages may expose schemas, connection strings, or query logic
- Internal file paths reveal server configuration and directory structure
- Framework and version information helps attackers find known vulnerabilities
- Violates security standards like PCI-DSS and SOC 2

## Correct

```javascript
// ✅ Secure error handler
const isProduction = process.env.NODE_ENV === 'production';

app.use((err, req, res, next) => {
  // Log full error internally
  logger.error('Request error', {
    error: err.message,
    stack: err.stack,
    requestId: req.id,
    path: req.path,
    method: req.method,
    userId: req.user?.id
  });

  // Determine if error is safe to expose
  const isOperationalError = err.isOperational || err.expose;
  const statusCode = err.statusCode || 500;

  // Build safe response
  const errorResponse = {
    error: {
      code: err.code || 'internal_error',
      message: isOperationalError
        ? err.message
        : 'An unexpected error occurred. Please try again later.',
      requestId: req.id
    }
  };

  // Only include details in development
  if (!isProduction && err.stack) {
    errorResponse.error._debug = {
      message: err.message,
      stack: err.stack.split('\n')
    };
  }

  res.status(statusCode).json(errorResponse);
});

// Custom error class for operational errors
class APIError extends Error {
  constructor(message, statusCode = 500, code = 'internal_error') {
    super(message);
    this.statusCode = statusCode;
    this.code = code;
    this.isOperational = true; // Safe to expose
  }
}

// Database error handling
app.get('/users/:id', async (req, res, next) => {
  try {
    const user = await db.findUser(req.params.id);
    if (!user) {
      throw new APIError('User not found', 404, 'resource_not_found');
    }
    res.json(user);
  } catch (error) {
    if (error instanceof APIError) {
      return next(error);
    }

    // Log the actual database error
    logger.error('Database error', {
      error: error.message,
      stack: error.stack,
      query: 'findUser',
      params: { id: req.params.id }
    });

    // Return generic error to client
    next(new APIError(
      'Unable to retrieve user information',
      500,
      'service_error'
    ));
  }
});
```

```python
# ✅ FastAPI with secure error handling
import logging
import traceback
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import os

app = FastAPI()
logger = logging.getLogger(__name__)
IS_PRODUCTION = os.getenv("ENVIRONMENT") == "production"

class APIError(Exception):
    def __init__(self, message: str, status_code: int = 500, code: str = "internal_error"):
        self.message = message
        self.status_code = status_code
        self.code = code
        self.is_operational = True

@app.exception_handler(APIError)
async def api_error_handler(request: Request, exc: APIError):
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "error": {
                "code": exc.code,
                "message": exc.message,
                "request_id": request.state.request_id
            }
        }
    )

@app.exception_handler(Exception)
async def generic_error_handler(request: Request, exc: Exception):
    # Log full error internally
    logger.error(
        "Unhandled exception",
        extra={
            "error": str(exc),
            "traceback": traceback.format_exc(),
            "request_id": request.state.request_id,
            "path": request.url.path,
            "method": request.method
        }
    )

    # Return safe response
    content = {
        "error": {
            "code": "internal_error",
            "message": "An unexpected error occurred. Please try again later.",
            "request_id": request.state.request_id
        }
    }

    # Include debug info only in development
    if not IS_PRODUCTION:
        content["error"]["_debug"] = {
            "type": type(exc).__name__,
            "message": str(exc),
            "traceback": traceback.format_exc().split("\n")
        }

    return JSONResponse(status_code=500, content=content)
```

```json
// ✅ Production error response (safe)
{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred. Please try again later.",
    "requestId": "req-abc123"
  }
}

// ✅ Development error response (with debug info)
{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred. Please try again later.",
    "requestId": "req-abc123",
    "_debug": {
      "type": "TypeError",
      "message": "Cannot read property 'id' of undefined",
      "traceback": [
        "Traceback (most recent call last):",
        "  File \"app.py\", line 45, in get_user",
        "    return user.id",
        "TypeError: Cannot read property 'id' of undefined"
      ]
    }
  }
}
```

## What to Log vs. What to Return

| Information | Log Internally | Return to Client |
|-------------|----------------|------------------|
| Error message | Yes | Generic only |
| Stack trace | Yes | Never in production |
| SQL queries | Yes | Never |
| File paths | Yes | Never |
| Internal IPs | Yes | Never |
| Request ID | Yes | Yes |
| Error code | Yes | Yes |
| User ID | Yes | No |
| Timestamps | Yes | Optional |

**Benefits:**
- Prevents attackers from exploiting revealed file paths, dependencies, and code structure
- Protects database schemas, API keys, and other secrets from leaking
- Hides framework versions that could expose known vulnerabilities
- Clean error messages present a professional API to consumers
- Meets compliance requirements for PCI-DSS, SOC 2, and similar standards
- Request IDs enable correlation between client reports and internal logs

Reference: [OWASP Improper Error Handling](https://owasp.org/www-community/Improper_Error_Handling)
