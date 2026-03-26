---
title: Include Request ID in Error Responses
impact: CRITICAL
impactDescription: "Enables log correlation and efficient debugging"
tags: errors, debugging, logging, request-tracking
---

## Include Request ID in Error Responses

**Impact: CRITICAL (Enables log correlation and efficient debugging)**

Every API request should have a unique identifier that appears in both the response and server logs, enabling easy correlation for debugging.

## Incorrect

```json
// ❌ No request identifier
{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred"
  }
}
// User reports error, but support can't find it in logs
```

```javascript
// ❌ No request tracking
app.get('/users/:id', async (req, res) => {
  try {
    const user = await db.findUser(req.params.id);
    res.json(user);
  } catch (error) {
    console.log('Error:', error.message); // No way to correlate
    res.status(500).json({ error: 'Something went wrong' });
  }
});
```

**Problems:**
- Support cannot locate specific errors in logs when users report issues
- No way to correlate client-side errors with server-side logs
- Debugging requires time-based log searching which is imprecise
- Distributed systems cannot trace requests across services
- No audit trail for specific request flows

## Correct

```javascript
// ✅ Request ID middleware
const { v4: uuidv4 } = require('uuid');

app.use((req, res, next) => {
  // Use client-provided ID or generate new one
  req.id = req.headers['x-request-id'] || uuidv4();

  // Add to response headers
  res.setHeader('X-Request-ID', req.id);

  // Add to logger context
  req.log = logger.child({ requestId: req.id });

  next();
});

// Use in routes
app.get('/users/:id', async (req, res, next) => {
  req.log.info('Fetching user', { userId: req.params.id });

  try {
    const user = await db.findUser(req.params.id);
    if (!user) {
      return res.status(404).json({
        error: {
          code: 'resource_not_found',
          message: 'User not found',
          requestId: req.id
        }
      });
    }
    res.json(user);
  } catch (error) {
    req.log.error('Failed to fetch user', {
      error: error.message,
      stack: error.stack
    });
    next(error);
  }
});

// Error handler includes request ID
app.use((err, req, res, next) => {
  req.log.error('Request failed', {
    error: err.message,
    stack: err.stack,
    statusCode: err.statusCode || 500
  });

  res.status(err.statusCode || 500).json({
    error: {
      code: err.code || 'internal_error',
      message: err.message || 'An unexpected error occurred',
      requestId: req.id
    }
  });
});
```

```python
# ✅ FastAPI with request ID
import uuid
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import logging
from contextvars import ContextVar

app = FastAPI()
logger = logging.getLogger(__name__)

# Context variable for request ID
request_id_var: ContextVar[str] = ContextVar("request_id", default="")

@app.middleware("http")
async def request_id_middleware(request: Request, call_next):
    # Get or generate request ID
    request_id = request.headers.get("X-Request-ID", str(uuid.uuid4()))
    request.state.request_id = request_id
    request_id_var.set(request_id)

    # Process request
    response = await call_next(request)

    # Add request ID to response
    response.headers["X-Request-ID"] = request_id
    return response

# Custom log filter to include request ID
class RequestIdFilter(logging.Filter):
    def filter(self, record):
        record.request_id = request_id_var.get("")
        return True

# Configure logging
handler = logging.StreamHandler()
handler.addFilter(RequestIdFilter())
handler.setFormatter(logging.Formatter(
    '%(asctime)s [%(request_id)s] %(levelname)s: %(message)s'
))
logger.addHandler(handler)

@app.exception_handler(Exception)
async def error_handler(request: Request, exc: Exception):
    logger.error(f"Request failed: {exc}", exc_info=True)

    return JSONResponse(
        status_code=500,
        content={
            "error": {
                "code": "internal_error",
                "message": "An unexpected error occurred",
                "requestId": request.state.request_id
            }
        },
        headers={"X-Request-ID": request.state.request_id}
    )

@app.get("/users/{user_id}")
async def get_user(user_id: int, request: Request):
    logger.info(f"Fetching user {user_id}")

    user = await db.get_user(user_id)
    if not user:
        return JSONResponse(
            status_code=404,
            content={
                "error": {
                    "code": "resource_not_found",
                    "message": f"User {user_id} not found",
                    "requestId": request.state.request_id
                }
            }
        )
    return user
```

```json
// ✅ Error response with request ID
HTTP/1.1 500 Internal Server Error
X-Request-ID: req-550e8400-e29b-41d4-a716-446655440000

{
  "error": {
    "code": "internal_error",
    "message": "An unexpected error occurred. Please try again.",
    "requestId": "req-550e8400-e29b-41d4-a716-446655440000"
  }
}
```

```bash
# ✅ Server logs with request ID
2024-01-15 10:30:00 [req-550e8400-e29b-41d4-a716-446655440000] INFO: Fetching user 123
2024-01-15 10:30:00 [req-550e8400-e29b-41d4-a716-446655440000] ERROR: Database connection timeout
2024-01-15 10:30:00 [req-550e8400-e29b-41d4-a716-446655440000] ERROR: Request failed
```

## Distributed Tracing Integration

```javascript
// ✅ Integration with OpenTelemetry
const { trace, context } = require('@opentelemetry/api');

app.use((req, res, next) => {
  const span = trace.getActiveSpan();

  // Use trace ID as request ID for distributed tracing
  if (span) {
    const traceId = span.spanContext().traceId;
    req.id = traceId;
    req.spanContext = span.spanContext();
  } else {
    req.id = uuidv4();
  }

  res.setHeader('X-Request-ID', req.id);
  next();
});
```

```yaml
# ✅ OpenAPI documentation for request ID
components:
  headers:
    X-Request-ID:
      description: Unique identifier for the request, used for debugging and log correlation
      schema:
        type: string
        format: uuid
      example: "550e8400-e29b-41d4-a716-446655440000"

  schemas:
    Error:
      type: object
      properties:
        error:
          type: object
          properties:
            code:
              type: string
            message:
              type: string
            requestId:
              type: string
              description: Unique request identifier for support correlation
```

**Benefits:**
- Users can provide the request ID when reporting issues for instant log lookup
- Links all log entries for a single request across multiple services
- Request IDs propagate through microservices for end-to-end distributed tracing
- "Please provide the request ID" is faster than "describe what you did"
- Enables tracking individual request paths through infrastructure
- Audit trails require the ability to trace specific requests

Reference: [OpenTelemetry Tracing](https://opentelemetry.io/docs/concepts/signals/traces/)
