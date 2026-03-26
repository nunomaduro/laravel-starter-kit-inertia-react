---
title: Protect Sensitive Data in Responses
impact: CRITICAL
impactDescription: "Prevents data leaks and privacy violations"
tags: security, privacy, sensitive-data, pii
---

## Protect Sensitive Data in Responses

**Impact: CRITICAL (Prevents data leaks and privacy violations)**

Never expose sensitive information like passwords, tokens, internal IDs, or PII in API responses.

## Incorrect

```json
// ❌ Exposing password hash
{
  "id": 123,
  "email": "user@example.com",
  "passwordHash": "$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4.n",
  "name": "John Doe"
}

// ❌ Exposing API keys
{
  "user": {
    "id": 123,
    "apiKey": "sk_live_abc123xyz789",
    "secretKey": "whsec_secret_key_here"
  }
}

// ❌ Exposing internal IDs
{
  "user": {
    "id": 123,
    "internalDatabaseId": "mongo_507f1f77bcf86cd799439011",
    "stripeCustomerId": "cus_abc123"
  }
}

// ❌ Exposing other users' data
{
  "order": {
    "id": 456,
    "customer": {
      "ssn": "123-45-6789",
      "creditCard": "4111111111111111",
      "dateOfBirth": "1990-01-15"
    }
  }
}
```

```javascript
// ❌ Returning entire database record
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  res.json(user); // Exposes ALL fields including sensitive ones
});
```

**Problems:**
- Exposed password hashes enable offline brute force attacks
- API keys in responses allow unauthorized access to third-party services
- Internal IDs help attackers enumerate resources and map infrastructure
- PII exposure violates privacy regulations (GDPR, CCPA, PCI-DSS)
- Returning full database records exposes fields never intended for clients

## Correct

```javascript
// ✅ Explicit field selection per context
const userPublicFields = ['id', 'name', 'avatar', 'createdAt'];
const userPrivateFields = [...userPublicFields, 'email', 'settings'];
const userAdminFields = [...userPrivateFields, 'roles', 'lastLoginAt', 'status'];

function sanitizeUser(user, context = 'public') {
  const allowedFields = {
    public: userPublicFields,
    private: userPrivateFields,
    admin: userAdminFields
  }[context];

  return Object.fromEntries(
    Object.entries(user).filter(([key]) => allowedFields.includes(key))
  );
}

// Use explicit field selection
app.get('/users/:id', authenticate, async (req, res) => {
  const user = await db.findUser(req.params.id);

  if (!user) {
    return res.status(404).json({ error: 'User not found' });
  }

  // Determine context based on requester
  let context = 'public';
  if (req.user.id === user.id) {
    context = 'private';
  } else if (req.user.roles.includes('admin')) {
    context = 'admin';
  }

  res.json(sanitizeUser(user, context));
});

// Use DTOs/serializers
class UserResponse {
  constructor(user, includePrivate = false) {
    this.id = user.id;
    this.name = user.name;
    this.avatar = user.avatar;
    this.createdAt = user.createdAt;

    if (includePrivate) {
      this.email = user.email;
      this.settings = user.settings;
    }
  }
}

app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);
  const includePrivate = req.user?.id === user.id;
  res.json(new UserResponse(user, includePrivate));
});

// Mask sensitive data
function maskEmail(email) {
  const [local, domain] = email.split('@');
  const maskedLocal = local[0] + '*'.repeat(local.length - 2) + local.slice(-1);
  return `${maskedLocal}@${domain}`;
}

function maskPhone(phone) {
  return phone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2');
}

function maskCreditCard(number) {
  return '**** **** **** ' + number.slice(-4);
}

// Apply masking in responses
app.get('/users/:id', async (req, res) => {
  const user = await db.findUser(req.params.id);

  res.json({
    id: user.id,
    name: user.name,
    email: req.user.id === user.id ? user.email : maskEmail(user.email),
    phone: maskPhone(user.phone),
    paymentMethod: {
      type: 'card',
      last4: user.creditCard.slice(-4),
      brand: user.cardBrand
    }
  });
});
```

```python
# ✅ FastAPI with Pydantic response models
from pydantic import BaseModel, EmailStr, Field
from typing import Optional

# Internal model (full data)
class UserInternal(BaseModel):
    id: int
    email: str
    password_hash: str
    name: str
    ssn: Optional[str]
    api_key: str
    stripe_customer_id: str

# Public response model (safe to expose)
class UserPublic(BaseModel):
    id: int
    name: str
    avatar_url: Optional[str]

    class Config:
        # Only include fields defined in this model
        extra = 'forbid'

# Private response model (for account owner)
class UserPrivate(UserPublic):
    email: EmailStr
    settings: dict

# Admin response model
class UserAdmin(UserPrivate):
    status: str
    roles: list
    last_login_at: Optional[str]

@app.get("/users/{user_id}", response_model=UserPublic)
async def get_user(user_id: int, current_user = Depends(get_current_user)):
    user = await db.get_user(user_id)

    if current_user.id == user_id:
        return UserPrivate(**user.dict())
    elif "admin" in current_user.roles:
        return UserAdmin(**user.dict())
    else:
        return UserPublic(**user.dict())

# Mask sensitive data in logs
import logging

class SensitiveDataFilter(logging.Filter):
    PATTERNS = [
        (r'"password":\s*"[^"]*"', '"password": "[REDACTED]"'),
        (r'"token":\s*"[^"]*"', '"token": "[REDACTED]"'),
        (r'"apiKey":\s*"[^"]*"', '"apiKey": "[REDACTED]"'),
        (r'\b\d{3}-\d{2}-\d{4}\b', '[SSN REDACTED]'),
        (r'\b\d{16}\b', '[CARD REDACTED]'),
    ]

    def filter(self, record):
        import re
        message = record.getMessage()
        for pattern, replacement in self.PATTERNS:
            message = re.sub(pattern, replacement, message)
        record.msg = message
        return True
```

```json
// ✅ Safe user response
{
  "id": 123,
  "name": "John Doe",
  "avatar": "https://cdn.example.com/avatars/123.jpg",
  "email": "j***n@example.com",
  "memberSince": "2023-01-15"
}

// ✅ Safe payment method response
{
  "paymentMethods": [
    {
      "id": "pm_abc123",
      "type": "card",
      "brand": "visa",
      "last4": "4242",
      "expiryMonth": 12,
      "expiryYear": 2025
    }
  ]
}
```

## Sensitive Data Checklist

| Field | Action | Reason |
|-------|--------|--------|
| Password hash | Never expose | Security |
| API keys | Never expose | Security |
| SSN/Tax ID | Never/mask | PII/Compliance |
| Full credit card | Never expose | PCI-DSS |
| Internal IDs | Usually hide | Information disclosure |
| Email (other users) | Mask | Privacy |
| Phone | Mask | Privacy |
| Address | Context-dependent | Privacy |

**Benefits:**
- Prevents account takeover from exposed credentials
- Meets privacy regulation requirements (GDPR, CCPA, PCI-DSS)
- Reduces attack surface by hiding internal system identifiers
- Context-based field exposure follows the principle of least privilege
- Data masking provides useful info without exposing full values
- Simplifies compliance audits with clear data handling policies

Reference: [OWASP Sensitive Data Exposure](https://owasp.org/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/09-Testing_for_Weak_Cryptography/04-Testing_for_Weak_Encryption)
