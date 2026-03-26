---
title: Enforce HTTPS Only
impact: CRITICAL
impactDescription: "Protects data in transit from interception"
tags: security, https, encryption, tls
---

## Enforce HTTPS Only

**Impact: CRITICAL (Protects data in transit from interception)**

All API traffic must use HTTPS to encrypt data in transit. Never allow unencrypted HTTP connections for APIs.

## Incorrect

```javascript
// ❌ HTTP server without TLS
const http = require('http');
const app = require('./app');

http.createServer(app).listen(80, () => {
  console.log('API running on http://localhost:80');
  // Credentials transmitted in plain text!
});

// ❌ Optional HTTPS
if (process.env.USE_HTTPS === 'true') {
  // HTTPS is optional, not enforced
}

// ❌ No redirect from HTTP to HTTPS
app.get('/', (req, res) => {
  // Allows HTTP access
  res.json({ message: 'Welcome' });
});

// ❌ Insecure cookie settings
res.cookie('session', token, {
  secure: false,  // Sent over HTTP!
  httpOnly: true
});
```

```json
// ❌ HTTP URLs in responses
{
  "user": {
    "id": 123,
    "avatar": "http://cdn.example.com/avatar.jpg",
    "profile": "http://api.example.com/users/123"
  }
}
```

**Problems:**
- Credentials and tokens transmitted in plain text can be intercepted
- Man-in-the-middle attacks can modify data in transit
- Cookies without the secure flag are sent over unencrypted connections
- HTTP URLs in responses encourage insecure client behavior
- Violates compliance requirements (PCI-DSS, HIPAA, GDPR)

## Correct

```javascript
// ✅ HTTPS with security headers
const https = require('https');
const fs = require('fs');
const express = require('express');
const helmet = require('helmet');

const app = express();

// Security headers including HSTS
app.use(helmet({
  hsts: {
    maxAge: 31536000, // 1 year
    includeSubDomains: true,
    preload: true
  }
}));

// Force HTTPS redirect (for direct access)
app.use((req, res, next) => {
  if (!req.secure && req.get('x-forwarded-proto') !== 'https') {
    return res.redirect(301, `https://${req.get('host')}${req.url}`);
  }
  next();
});

// Secure cookie settings
app.use((req, res, next) => {
  res.cookie = function(name, value, options = {}) {
    options.secure = true;      // HTTPS only
    options.httpOnly = true;    // No JavaScript access
    options.sameSite = 'strict'; // CSRF protection
    return res.cookie.call(this, name, value, options);
  };
  next();
});

// HTTPS server
const options = {
  key: fs.readFileSync('/path/to/private.key'),
  cert: fs.readFileSync('/path/to/certificate.crt'),
  ca: fs.readFileSync('/path/to/ca-bundle.crt'),
  minVersion: 'TLSv1.2',  // Minimum TLS version
  ciphers: [
    'ECDHE-ECDSA-AES128-GCM-SHA256',
    'ECDHE-RSA-AES128-GCM-SHA256',
    'ECDHE-ECDSA-AES256-GCM-SHA384',
    'ECDHE-RSA-AES256-GCM-SHA384'
  ].join(':')
};

https.createServer(options, app).listen(443, () => {
  console.log('Secure API running on https://localhost:443');
});

// Also listen on HTTP just to redirect
const http = require('http');
http.createServer((req, res) => {
  res.writeHead(301, { Location: `https://${req.headers.host}${req.url}` });
  res.end();
}).listen(80);
```

```python
# ✅ FastAPI with HTTPS enforcement
from fastapi import FastAPI, Request
from fastapi.middleware.httpsredirect import HTTPSRedirectMiddleware
from fastapi.middleware.trustedhost import TrustedHostMiddleware
from starlette.middleware.base import BaseHTTPMiddleware

app = FastAPI()

# Redirect HTTP to HTTPS
app.add_middleware(HTTPSRedirectMiddleware)

# Only allow specific hosts
app.add_middleware(
    TrustedHostMiddleware,
    allowed_hosts=["api.example.com", "*.example.com"]
)

# Add HSTS header
class HSTSMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        response = await call_next(request)
        response.headers["Strict-Transport-Security"] = (
            "max-age=31536000; includeSubDomains; preload"
        )
        return response

app.add_middleware(HSTSMiddleware)

# Secure cookie response
from fastapi.responses import JSONResponse

@app.post("/auth/login")
async def login(credentials: Credentials):
    token = create_token(credentials)
    response = JSONResponse({"status": "logged_in"})
    response.set_cookie(
        key="session",
        value=token,
        httponly=True,
        secure=True,
        samesite="strict",
        max_age=86400
    )
    return response
```

```nginx
# ✅ Nginx HTTPS configuration
server {
    listen 80;
    server_name api.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.example.com;

    ssl_certificate /etc/ssl/certs/certificate.crt;
    ssl_certificate_key /etc/ssl/private/private.key;

    # Modern TLS configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers on;

    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header X-Forwarded-Proto https;
    }
}
```

```yaml
# ✅ Docker Compose with Traefik for automatic HTTPS
version: '3.8'
services:
  traefik:
    image: traefik:v2.10
    command:
      - "--providers.docker=true"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--entrypoints.web.http.redirections.entryPoint.to=websecure"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
      - "--certificatesresolvers.letsencrypt.acme.email=admin@example.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - letsencrypt:/letsencrypt

  api:
    image: my-api
    labels:
      - "traefik.http.routers.api.rule=Host(`api.example.com`)"
      - "traefik.http.routers.api.tls.certresolver=letsencrypt"
      - "traefik.http.middlewares.hsts.headers.stsSeconds=31536000"
      - "traefik.http.routers.api.middlewares=hsts"
```

## TLS Configuration Checklist

| Setting | Recommendation |
|---------|----------------|
| Minimum TLS | TLSv1.2 |
| Preferred TLS | TLSv1.3 |
| HSTS max-age | 31536000 (1 year) |
| includeSubDomains | Yes |
| HSTS preload | Yes (after testing) |
| Certificate | Valid, not self-signed |
| Certificate chain | Complete |

**Benefits:**
- HTTPS encrypts all data in transit, protecting credentials and sensitive data
- TLS certificates verify server identity, preventing MITM attacks
- Data integrity ensures content is not modified in transit
- Meets compliance requirements for PCI-DSS, HIPAA, and GDPR
- HTTP/2 and many modern web APIs require HTTPS
- Secure cookies only work over HTTPS connections

Reference: [OWASP Transport Layer Protection](https://cheatsheetseries.owasp.org/cheatsheets/Transport_Layer_Security_Cheat_Sheet.html)
