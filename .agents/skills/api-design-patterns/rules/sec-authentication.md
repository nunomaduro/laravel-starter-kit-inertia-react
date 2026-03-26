---
title: Implement Secure Authentication
impact: CRITICAL
impactDescription: "Protects user data and prevents unauthorized access"
tags: security, authentication, jwt, oauth2, tokens
---

## Implement Secure Authentication

**Impact: CRITICAL (Protects user data and prevents unauthorized access)**

Use industry-standard authentication mechanisms like OAuth 2.0, JWT, or API keys with proper security practices.

## Incorrect

```javascript
// ❌ Basic auth over HTTP
app.use((req, res, next) => {
  const auth = req.headers.authorization;
  const [user, pass] = Buffer.from(auth.split(' ')[1], 'base64')
    .toString().split(':');
  // Credentials sent in plain text!
  if (user === 'admin' && pass === 'password123') {
    next();
  }
});

// ❌ Token in URL
app.get('/users?token=secret123', (req, res) => {
  // Token visible in logs, browser history, referrer headers
});

// ❌ No token expiration
const token = jwt.sign({ userId: 123 }); // No expiration!

// ❌ Weak secret
const token = jwt.sign({ userId: 123 }, 'secret'); // Easily guessable
```

```json
// ❌ Credentials in response body
{
  "user": {
    "id": 123,
    "password": "hashedpassword",
    "apiKey": "sk_live_abc123"
  }
}
```

**Problems:**
- Plain text credentials can be intercepted over HTTP
- Tokens in URLs are logged by servers, proxies, and browsers
- Non-expiring tokens remain valid forever if compromised
- Weak secrets allow token forgery through brute force
- Exposing credentials in responses enables account takeover

## Correct

```javascript
// ✅ Secure JWT authentication
const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');

// Environment-based secrets
const JWT_SECRET = process.env.JWT_SECRET; // Long, random string
const JWT_EXPIRES_IN = '15m'; // Short-lived access tokens
const REFRESH_EXPIRES_IN = '7d';

// Password hashing
async function hashPassword(password) {
  const saltRounds = 12;
  return bcrypt.hash(password, saltRounds);
}

async function verifyPassword(password, hash) {
  return bcrypt.compare(password, hash);
}

// JWT token generation
function generateTokens(user) {
  const accessToken = jwt.sign(
    {
      sub: user.id,
      email: user.email,
      roles: user.roles
    },
    JWT_SECRET,
    {
      expiresIn: JWT_EXPIRES_IN,
      issuer: 'api.example.com',
      audience: 'example.com'
    }
  );

  const refreshToken = jwt.sign(
    { sub: user.id, type: 'refresh' },
    JWT_SECRET,
    { expiresIn: REFRESH_EXPIRES_IN }
  );

  return { accessToken, refreshToken };
}

// Authentication endpoint
app.post('/auth/login', async (req, res) => {
  const { email, password } = req.body;

  const user = await db.findUserByEmail(email);
  if (!user) {
    // Don't reveal if user exists
    return res.status(401).json({
      error: {
        code: 'invalid_credentials',
        message: 'Invalid email or password'
      }
    });
  }

  const valid = await verifyPassword(password, user.passwordHash);
  if (!valid) {
    return res.status(401).json({
      error: {
        code: 'invalid_credentials',
        message: 'Invalid email or password'
      }
    });
  }

  const tokens = generateTokens(user);

  // Set refresh token as HTTP-only cookie
  res.cookie('refreshToken', tokens.refreshToken, {
    httpOnly: true,
    secure: true,
    sameSite: 'strict',
    maxAge: 7 * 24 * 60 * 60 * 1000
  });

  res.json({
    accessToken: tokens.accessToken,
    expiresIn: 900, // 15 minutes in seconds
    tokenType: 'Bearer'
  });
});

// JWT verification middleware
function authenticate(req, res, next) {
  const authHeader = req.headers.authorization;

  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return res.status(401).json({
      error: {
        code: 'auth_token_missing',
        message: 'Authorization header with Bearer token required'
      }
    });
  }

  const token = authHeader.slice(7);

  try {
    const payload = jwt.verify(token, JWT_SECRET, {
      issuer: 'api.example.com',
      audience: 'example.com'
    });

    req.user = payload;
    next();
  } catch (error) {
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        error: {
          code: 'auth_token_expired',
          message: 'Access token has expired. Please refresh.'
        }
      });
    }

    return res.status(401).json({
      error: {
        code: 'auth_token_invalid',
        message: 'Invalid access token'
      }
    });
  }
}

// Token refresh endpoint
app.post('/auth/refresh', async (req, res) => {
  const refreshToken = req.cookies.refreshToken;

  if (!refreshToken) {
    return res.status(401).json({
      error: {
        code: 'refresh_token_missing',
        message: 'Refresh token required'
      }
    });
  }

  try {
    const payload = jwt.verify(refreshToken, JWT_SECRET);

    // Check if token is revoked
    const isRevoked = await db.isTokenRevoked(refreshToken);
    if (isRevoked) {
      return res.status(401).json({
        error: {
          code: 'refresh_token_revoked',
          message: 'Refresh token has been revoked'
        }
      });
    }

    const user = await db.findUser(payload.sub);
    const tokens = generateTokens(user);

    // Rotate refresh token
    await db.revokeToken(refreshToken);

    res.cookie('refreshToken', tokens.refreshToken, {
      httpOnly: true,
      secure: true,
      sameSite: 'strict',
      maxAge: 7 * 24 * 60 * 60 * 1000
    });

    res.json({
      accessToken: tokens.accessToken,
      expiresIn: 900
    });
  } catch (error) {
    return res.status(401).json({
      error: {
        code: 'refresh_token_invalid',
        message: 'Invalid refresh token'
      }
    });
  }
});

// Protected route
app.get('/users/me', authenticate, (req, res) => {
  res.json({ userId: req.user.sub });
});
```

```python
# ✅ FastAPI with OAuth2 and JWT
from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer, OAuth2PasswordRequestForm
from jose import JWTError, jwt
from passlib.context import CryptContext
from datetime import datetime, timedelta
import os

app = FastAPI()

SECRET_KEY = os.getenv("JWT_SECRET")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 15

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="auth/token")

def create_access_token(data: dict, expires_delta: timedelta = None):
    to_encode = data.copy()
    expire = datetime.utcnow() + (expires_delta or timedelta(minutes=15))
    to_encode.update({"exp": expire, "iss": "api.example.com"})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)

async def get_current_user(token: str = Depends(oauth2_scheme)):
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail={"code": "auth_token_invalid", "message": "Invalid token"},
        headers={"WWW-Authenticate": "Bearer"},
    )
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id: str = payload.get("sub")
        if user_id is None:
            raise credentials_exception
    except JWTError:
        raise credentials_exception

    user = await db.get_user(user_id)
    if user is None:
        raise credentials_exception
    return user

@app.post("/auth/token")
async def login(form_data: OAuth2PasswordRequestForm = Depends()):
    user = await authenticate_user(form_data.username, form_data.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail={"code": "invalid_credentials", "message": "Invalid credentials"}
        )

    access_token = create_access_token(
        data={"sub": str(user.id)},
        expires_delta=timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    )
    return {"access_token": access_token, "token_type": "bearer"}

@app.get("/users/me")
async def read_users_me(current_user = Depends(get_current_user)):
    return current_user
```

**Benefits:**
- Short-lived tokens and refresh rotation limit damage from token theft
- Bcrypt hashing protects passwords even if the database is compromised
- HTTP-only cookies prevent XSS attacks on refresh tokens
- OAuth 2.0/JWT are well-understood, widely supported standards
- Stateless JWT tokens work well in distributed systems
- Token-based auth provides clear user identification for audit logging

Reference: [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
