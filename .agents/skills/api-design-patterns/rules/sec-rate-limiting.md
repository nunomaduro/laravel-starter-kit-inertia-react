---
title: Implement Rate Limiting
impact: CRITICAL
impactDescription: "Prevents abuse and ensures service availability"
tags: security, rate-limiting, abuse-prevention, throttling
---

## Implement Rate Limiting

**Impact: CRITICAL (Prevents abuse and ensures service availability)**

Protect your API from abuse by limiting the number of requests clients can make within a time window.

## Incorrect

```javascript
// ❌ No rate limiting
app.post('/login', async (req, res) => {
  // Vulnerable to brute force attacks
  const user = await authenticate(req.body);
  res.json(user);
});

// ❌ Rate limit only on response
app.get('/api/data', async (req, res) => {
  const data = await expensiveQuery(); // Query runs every time!
  if (requestCount > 100) {
    res.status(429).json({ error: 'Too many requests' });
  }
  res.json(data);
});

// ❌ No rate limit headers
app.use((req, res, next) => {
  if (isRateLimited(req)) {
    res.status(429).send('Too many requests');
    // Client doesn't know when to retry
  }
  next();
});
```

**Problems:**
- Login endpoints without rate limits are vulnerable to brute force attacks
- Checking limits after processing wastes server resources
- Missing retry headers leave clients guessing when they can retry
- No per-user or per-endpoint differentiation allows abuse
- Expensive operations run before limits are checked

## Correct

```javascript
// ✅ Comprehensive rate limiting
const rateLimit = require('express-rate-limit');
const RedisStore = require('rate-limit-redis');
const Redis = require('ioredis');

const redis = new Redis(process.env.REDIS_URL);

// General API rate limiter
const apiLimiter = rateLimit({
  store: new RedisStore({
    client: redis,
    prefix: 'rl:api:'
  }),
  windowMs: 60 * 1000, // 1 minute
  max: 100, // 100 requests per minute
  standardHeaders: true, // Return rate limit info in headers
  legacyHeaders: false,
  message: {
    error: {
      code: 'rate_limit_exceeded',
      message: 'Too many requests. Please slow down.',
      retryAfter: 60
    }
  },
  keyGenerator: (req) => {
    // Rate limit by user ID if authenticated, otherwise by IP
    return req.user?.id || req.ip;
  }
});

// Stricter limiter for auth endpoints
const authLimiter = rateLimit({
  store: new RedisStore({
    client: redis,
    prefix: 'rl:auth:'
  }),
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 5, // 5 attempts per 15 minutes
  standardHeaders: true,
  message: {
    error: {
      code: 'rate_limit_exceeded',
      message: 'Too many login attempts. Please try again later.',
      retryAfter: 900
    }
  },
  keyGenerator: (req) => req.body.email || req.ip // Per email address
});

// Expensive endpoint limiter
const expensiveLimiter = rateLimit({
  store: new RedisStore({
    client: redis,
    prefix: 'rl:expensive:'
  }),
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 10, // 10 requests per hour
  standardHeaders: true,
  message: {
    error: {
      code: 'rate_limit_exceeded',
      message: 'Export limit reached. Try again in an hour.',
      retryAfter: 3600
    }
  }
});

// Apply rate limiters
app.use('/api/', apiLimiter);
app.use('/auth/login', authLimiter);
app.use('/auth/password-reset', authLimiter);
app.post('/api/export', expensiveLimiter, exportHandler);

// Custom sliding window implementation
class SlidingWindowRateLimiter {
  constructor(redis, options) {
    this.redis = redis;
    this.windowMs = options.windowMs;
    this.maxRequests = options.max;
  }

  async isAllowed(key) {
    const now = Date.now();
    const windowStart = now - this.windowMs;

    const pipeline = this.redis.pipeline();

    // Remove old entries
    pipeline.zremrangebyscore(key, 0, windowStart);

    // Count requests in window
    pipeline.zcard(key);

    // Add current request
    pipeline.zadd(key, now, `${now}-${Math.random()}`);

    // Set expiry
    pipeline.expire(key, Math.ceil(this.windowMs / 1000));

    const results = await pipeline.exec();
    const count = results[1][1];

    return {
      allowed: count < this.maxRequests,
      remaining: Math.max(0, this.maxRequests - count - 1),
      resetAt: new Date(now + this.windowMs)
    };
  }
}

// Tiered rate limiting based on plan
const tierLimits = {
  free: { requestsPerMinute: 60, requestsPerDay: 1000 },
  basic: { requestsPerMinute: 300, requestsPerDay: 10000 },
  pro: { requestsPerMinute: 1000, requestsPerDay: 100000 },
  enterprise: { requestsPerMinute: 5000, requestsPerDay: 1000000 }
};

async function tieredRateLimiter(req, res, next) {
  const user = req.user;
  const tier = user?.plan || 'free';
  const limits = tierLimits[tier];

  const minuteKey = `rl:${user?.id || req.ip}:minute`;
  const dayKey = `rl:${user?.id || req.ip}:day`;

  // Check minute limit
  const minuteCount = await redis.incr(minuteKey);
  if (minuteCount === 1) {
    await redis.expire(minuteKey, 60);
  }

  // Check daily limit
  const dayCount = await redis.incr(dayKey);
  if (dayCount === 1) {
    await redis.expire(dayKey, 86400);
  }

  // Set rate limit headers
  res.set('X-RateLimit-Limit', limits.requestsPerMinute);
  res.set('X-RateLimit-Remaining', Math.max(0, limits.requestsPerMinute - minuteCount));
  res.set('X-RateLimit-Reset', Math.ceil(Date.now() / 1000) + 60);

  if (minuteCount > limits.requestsPerMinute) {
    return res.status(429).json({
      error: {
        code: 'rate_limit_exceeded',
        message: 'Minute rate limit exceeded',
        limit: limits.requestsPerMinute,
        window: '1 minute',
        retryAfter: await redis.ttl(minuteKey)
      }
    });
  }

  if (dayCount > limits.requestsPerDay) {
    return res.status(429).json({
      error: {
        code: 'rate_limit_exceeded',
        message: 'Daily rate limit exceeded',
        limit: limits.requestsPerDay,
        window: '24 hours',
        upgradeUrl: '/pricing'
      }
    });
  }

  next();
}
```

```python
# ✅ FastAPI with rate limiting
from fastapi import FastAPI, Request, HTTPException
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded

limiter = Limiter(key_func=get_remote_address)
app = FastAPI()
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

@app.get("/api/data")
@limiter.limit("100/minute")
async def get_data(request: Request):
    return {"data": "value"}

@app.post("/auth/login")
@limiter.limit("5/15minutes")
async def login(request: Request):
    return {"token": "..."}

# Custom user-based rate limiting
async def get_rate_limit_key(request: Request):
    if request.user:
        return f"user:{request.user.id}"
    return f"ip:{request.client.host}"

@app.get("/api/premium")
@limiter.limit("1000/minute", key_func=get_rate_limit_key)
async def premium_endpoint(request: Request):
    return {"data": "premium"}
```

## Rate Limit Headers

```
HTTP/1.1 200 OK
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1705312800
Retry-After: 60
```

**Benefits:**
- Prevents denial-of-service attacks from overwhelming servers
- Limits brute force password guessing and credential stuffing attacks
- Ensures fair API access for all users
- Prevents runaway usage that increases infrastructure costs
- Enables tiered pricing based on usage limits
- Rate limit headers help clients implement proper backoff strategies

Reference: [IETF Rate Limiting Headers](https://datatracker.ietf.org/doc/draft-ietf-httpapi-ratelimit-headers/)
