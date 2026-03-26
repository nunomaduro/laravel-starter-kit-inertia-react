---
title: Configure CORS Properly
impact: CRITICAL
impactDescription: "Prevents unauthorized cross-origin access"
tags: security, cors, cross-origin, browsers
---

## Configure CORS Properly

**Impact: CRITICAL (Prevents unauthorized cross-origin access)**

Cross-Origin Resource Sharing (CORS) must be configured correctly to allow legitimate cross-origin requests while preventing unauthorized access.

## Incorrect

```javascript
// ❌ Allow all origins
app.use(cors({
  origin: '*',
  credentials: true  // DANGEROUS: Can't use * with credentials!
}));

// ❌ Reflecting origin without validation
app.use((req, res, next) => {
  // Allows ANY origin - security vulnerability
  res.header('Access-Control-Allow-Origin', req.headers.origin);
  res.header('Access-Control-Allow-Credentials', 'true');
  next();
});

// ❌ Overly permissive headers
app.use(cors({
  origin: '*',
  methods: '*',
  allowedHeaders: '*',
  exposedHeaders: '*'
}));

// ❌ Missing CORS entirely for API
app.get('/api/data', (req, res) => {
  // Browser will block cross-origin requests
  res.json({ data: 'value' });
});
```

**Problems:**
- Wildcard origin with credentials allows any website to make authenticated requests
- Reflecting the Origin header blindly defeats the purpose of CORS
- Overly permissive headers expose the API to cross-origin attacks
- Missing CORS configuration blocks legitimate frontend applications
- Enables CSRF attacks that rely on cross-origin requests

## Correct

```javascript
// ✅ Properly configured CORS
const cors = require('cors');

// Allowed origins whitelist
const allowedOrigins = [
  'https://myapp.com',
  'https://www.myapp.com',
  'https://admin.myapp.com'
];

// Development origins (only in non-production)
if (process.env.NODE_ENV !== 'production') {
  allowedOrigins.push(
    'http://localhost:3000',
    'http://localhost:8080'
  );
}

// CORS configuration
const corsOptions = {
  origin: (origin, callback) => {
    // Allow requests with no origin (mobile apps, curl, etc.)
    if (!origin) {
      return callback(null, true);
    }

    if (allowedOrigins.includes(origin)) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
  allowedHeaders: [
    'Content-Type',
    'Authorization',
    'X-Request-ID',
    'X-Requested-With'
  ],
  exposedHeaders: [
    'X-Request-ID',
    'X-RateLimit-Limit',
    'X-RateLimit-Remaining',
    'X-RateLimit-Reset'
  ],
  credentials: true,
  maxAge: 86400, // 24 hours - cache preflight requests
  optionsSuccessStatus: 204
};

app.use(cors(corsOptions));

// Handle CORS errors
app.use((err, req, res, next) => {
  if (err.message === 'Not allowed by CORS') {
    return res.status(403).json({
      error: {
        code: 'cors_error',
        message: 'Cross-origin request blocked',
        origin: req.headers.origin
      }
    });
  }
  next(err);
});

// Route-specific CORS (for public endpoints)
const publicCors = cors({
  origin: '*',
  methods: ['GET'],
  maxAge: 86400
});

app.get('/api/public/health', publicCors, (req, res) => {
  res.json({ status: 'ok' });
});

// Strict CORS for sensitive endpoints
const strictCors = cors({
  origin: 'https://admin.myapp.com',
  credentials: true,
  methods: ['GET', 'POST', 'DELETE']
});

app.use('/api/admin', strictCors, adminRouter);
```

```python
# ✅ FastAPI CORS configuration
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import os

app = FastAPI()

# Define allowed origins
allowed_origins = [
    "https://myapp.com",
    "https://www.myapp.com",
    "https://admin.myapp.com"
]

# Add development origins
if os.getenv("ENVIRONMENT") != "production":
    allowed_origins.extend([
        "http://localhost:3000",
        "http://localhost:8080"
    ])

app.add_middleware(
    CORSMiddleware,
    allow_origins=allowed_origins,
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "PATCH", "DELETE"],
    allow_headers=[
        "Content-Type",
        "Authorization",
        "X-Request-ID",
        "X-Requested-With"
    ],
    expose_headers=[
        "X-Request-ID",
        "X-RateLimit-Limit",
        "X-RateLimit-Remaining"
    ],
    max_age=86400
)

# For public endpoints, create a sub-application
public_app = FastAPI()
public_app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Public API
    allow_methods=["GET"],
    max_age=86400
)

app.mount("/public", public_app)
```

```typescript
// ✅ Dynamic CORS based on subdomain pattern
const corsOptions = {
  origin: (origin: string | undefined, callback: Function) => {
    if (!origin) {
      return callback(null, true);
    }

    // Allow all subdomains of myapp.com
    const allowedPattern = /^https:\/\/([a-z0-9-]+\.)?myapp\.com$/;

    if (allowedPattern.test(origin)) {
      callback(null, true);
    } else {
      callback(new Error('CORS not allowed'));
    }
  },
  credentials: true
};
```

## CORS Headers Reference

| Header | Purpose | Example |
|--------|---------|---------|
| Access-Control-Allow-Origin | Allowed origins | `https://myapp.com` |
| Access-Control-Allow-Methods | Allowed HTTP methods | `GET, POST, PUT` |
| Access-Control-Allow-Headers | Allowed request headers | `Content-Type, Authorization` |
| Access-Control-Expose-Headers | Headers client can read | `X-Request-ID` |
| Access-Control-Allow-Credentials | Allow cookies/auth | `true` |
| Access-Control-Max-Age | Preflight cache time | `86400` |

## Common Patterns

```javascript
// ✅ Pattern 1: Subdomain allowlist
const isAllowedOrigin = (origin) => {
  if (!origin) return true;
  const url = new URL(origin);
  return url.hostname.endsWith('.myapp.com');
};

// ✅ Pattern 2: Environment-based
const origins = {
  production: ['https://myapp.com'],
  staging: ['https://staging.myapp.com'],
  development: ['http://localhost:3000']
};
const allowedOrigins = origins[process.env.NODE_ENV];

// ✅ Pattern 3: Database-driven (for multi-tenant)
const corsOptions = {
  origin: async (origin, callback) => {
    const tenant = await db.findTenantByDomain(origin);
    callback(null, tenant?.corsEnabled ?? false);
  }
};
```

**Benefits:**
- Prevents malicious websites from making unauthorized API calls
- Proper configuration prevents credential leakage to untrusted origins
- Blocks CSRF attacks that rely on cross-origin requests
- Explicitly controls which domains can access your API
- Preflight caching reduces OPTIONS request overhead
- Route-specific CORS allows different policies for different endpoints

Reference: [MDN CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
