---
title: Implement Idempotency for Safe Retries
impact: CRITICAL
impactDescription: "Prevents duplicate operations and enables safe retries"
tags: rest, idempotency, reliability, retries
---

## Implement Idempotency for Safe Retries

**Impact: CRITICAL (Prevents duplicate operations and enables safe retries)**

Idempotent operations produce the same result regardless of how many times they're executed. Implement idempotency keys for non-idempotent operations to enable safe retries.

## Incorrect

```javascript
// ❌ Non-idempotent POST without protection
app.post('/payments', async (req, res) => {
  // Each retry creates a duplicate payment!
  const payment = await db.createPayment({
    amount: req.body.amount,
    customerId: req.body.customerId
  });
  await chargeCard(payment);
  res.status(201).json(payment);
});

// ❌ No idempotency key checking
app.post('/orders', async (req, res) => {
  // Network timeout after processing = client retries = duplicate order
  const order = await db.createOrder(req.body);
  await processOrder(order);
  res.status(201).json(order);
});
```

```json
// ❌ Client retries without idempotency key
POST /payments
{
  "amount": 100,
  "customerId": "cust_123"
}
// Timeout... retry... duplicate payment created!
```

**Problems:**
- Duplicate payments or orders when clients retry after network timeouts
- No way for the server to detect repeated requests
- Financial losses from double-charging customers
- Data inconsistency in distributed systems with message retries
- Clients must implement complex tracking logic to avoid duplicates

## Correct

```javascript
// ✅ Idempotency key middleware
const express = require('express');
const router = express.Router();

const idempotencyStore = new Map(); // Use Redis in production

async function idempotencyMiddleware(req, res, next) {
  const idempotencyKey = req.headers['idempotency-key'];

  if (!idempotencyKey) {
    return res.status(400).json({
      error: 'missing_idempotency_key',
      message: 'Idempotency-Key header is required for this operation'
    });
  }

  const cacheKey = `${req.path}:${idempotencyKey}`;
  const cached = idempotencyStore.get(cacheKey);

  if (cached) {
    // Return cached response
    return res.status(cached.status).json(cached.body);
  }

  // Store original json function
  const originalJson = res.json.bind(res);

  // Override to cache response
  res.json = function(body) {
    idempotencyStore.set(cacheKey, {
      status: res.statusCode,
      body: body
    });
    // Set TTL (24 hours typical)
    setTimeout(() => idempotencyStore.delete(cacheKey), 24 * 60 * 60 * 1000);
    return originalJson(body);
  };

  next();
}

// ✅ Apply to non-idempotent operations
router.post('/payments', idempotencyMiddleware, async (req, res) => {
  const payment = await db.createPayment({
    amount: req.body.amount,
    customerId: req.body.customerId,
    idempotencyKey: req.headers['idempotency-key']
  });

  await chargeCard(payment);
  res.status(201).json(payment);
});

// ✅ Idempotent by design using upsert
router.put('/users/:id/preferences', async (req, res) => {
  // PUT is idempotent - same request always produces same result
  const preferences = await db.upsertPreferences(
    req.params.id,
    req.body
  );
  res.json(preferences);
});

// ✅ Natural idempotency with unique constraints
router.post('/subscriptions', async (req, res) => {
  try {
    const subscription = await db.createSubscription({
      userId: req.body.userId,
      planId: req.body.planId
    });
    res.status(201).json(subscription);
  } catch (error) {
    if (error.code === 'UNIQUE_VIOLATION') {
      // Return existing subscription
      const existing = await db.findSubscription(
        req.body.userId,
        req.body.planId
      );
      return res.status(200).json(existing);
    }
    throw error;
  }
});
```

```python
# ✅ FastAPI with idempotency
from fastapi import FastAPI, Header, HTTPException
from functools import wraps
import redis

app = FastAPI()
redis_client = redis.Redis()

def idempotent(ttl_seconds: int = 86400):
    def decorator(func):
        @wraps(func)
        async def wrapper(*args, idempotency_key: str = Header(...), **kwargs):
            cache_key = f"idempotency:{func.__name__}:{idempotency_key}"

            # Check cache
            cached = redis_client.get(cache_key)
            if cached:
                return json.loads(cached)

            # Execute operation
            result = await func(*args, **kwargs)

            # Cache result
            redis_client.setex(cache_key, ttl_seconds, json.dumps(result))

            return result
        return wrapper
    return decorator

@app.post("/payments")
@idempotent(ttl_seconds=86400)
async def create_payment(payment: PaymentCreate):
    result = await process_payment(payment)
    return {"id": result.id, "status": result.status}
```

```json
// ✅ Client request with idempotency key
POST /payments HTTP/1.1
Host: api.example.com
Content-Type: application/json
Idempotency-Key: unique-request-id-12345

{
  "amount": 100,
  "customerId": "cust_123"
}

// Response (same for retries)
HTTP/1.1 201 Created
Idempotency-Key: unique-request-id-12345

{
  "id": "pay_789",
  "amount": 100,
  "customerId": "cust_123",
  "status": "completed"
}
```

## Idempotency by HTTP Method

| Method | Naturally Idempotent | Notes |
|--------|---------------------|-------|
| GET | Yes | Always safe to retry |
| HEAD | Yes | Always safe to retry |
| OPTIONS | Yes | Always safe to retry |
| PUT | Yes | Full replacement is idempotent |
| DELETE | Yes | Deleting twice = same result |
| POST | No | Needs idempotency key |
| PATCH | Usually | Depends on implementation |

**Benefits:**
- Clients can safely retry requests without causing duplicate operations
- Prevents duplicate payments or orders that cause financial and data issues
- Users can safely click "submit" multiple times without fear
- Works well in distributed systems with at-least-once delivery guarantees
- Idempotency keys provide request correlation across systems
- Simplifies client code by removing complex success-tracking logic

Reference: [Stripe Idempotency Guide](https://stripe.com/docs/api/idempotent_requests)
