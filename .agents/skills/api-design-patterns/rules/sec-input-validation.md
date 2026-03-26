---
title: Validate All Input Data
impact: CRITICAL
impactDescription: "Prevents injection attacks and malformed data"
tags: security, validation, input-sanitization, injection
---

## Validate All Input Data

**Impact: CRITICAL (Prevents injection attacks and malformed data)**

Never trust client input. Validate, sanitize, and constrain all incoming data to prevent security vulnerabilities.

## Incorrect

```javascript
// ❌ No validation
app.post('/users', async (req, res) => {
  // Directly using user input!
  const user = await db.createUser(req.body);
  res.json(user);
});

// ❌ SQL injection vulnerability
app.get('/users', async (req, res) => {
  const query = `SELECT * FROM users WHERE name = '${req.query.name}'`;
  const users = await db.raw(query);
  res.json(users);
});

// ❌ NoSQL injection
app.post('/login', async (req, res) => {
  const user = await db.users.findOne({
    email: req.body.email,
    password: req.body.password  // Could be { $gt: '' }
  });
});

// ❌ Path traversal
app.get('/files/:filename', (req, res) => {
  const path = `./uploads/${req.params.filename}`;
  res.sendFile(path); // Could access ../../../etc/passwd
});
```

**Problems:**
- SQL injection allows attackers to read, modify, or delete database data
- NoSQL injection bypasses authentication with operator-based payloads
- Path traversal exposes sensitive server files
- Unbounded input sizes enable memory exhaustion and DoS attacks
- Unsanitized HTML/script input enables XSS attacks
- Type confusion attacks exploit loosely typed inputs

## Correct

```javascript
// ✅ Comprehensive input validation
const { body, param, query, validationResult } = require('express-validator');
const sanitizeHtml = require('sanitize-html');
const path = require('path');

// Validation middleware
const validateUser = [
  body('email')
    .trim()
    .notEmpty().withMessage('Email is required')
    .isEmail().withMessage('Must be a valid email')
    .normalizeEmail()
    .isLength({ max: 255 }).withMessage('Email too long'),

  body('password')
    .notEmpty().withMessage('Password is required')
    .isLength({ min: 8, max: 100 }).withMessage('Password must be 8-100 characters')
    .matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/).withMessage('Password too weak'),

  body('name')
    .trim()
    .notEmpty().withMessage('Name is required')
    .isLength({ min: 1, max: 100 }).withMessage('Name must be 1-100 characters')
    .matches(/^[a-zA-Z\s'-]+$/).withMessage('Name contains invalid characters')
    .customSanitizer(value => sanitizeHtml(value, { allowedTags: [] })),

  body('age')
    .optional()
    .isInt({ min: 0, max: 150 }).withMessage('Age must be 0-150')
    .toInt(),

  body('website')
    .optional()
    .trim()
    .isURL({ protocols: ['http', 'https'] }).withMessage('Invalid URL')
];

// Validation error handler
const handleValidation = (req, res, next) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    return res.status(400).json({
      error: {
        code: 'validation_error',
        message: 'Invalid input data',
        details: errors.array().map(e => ({
          field: e.path,
          message: e.msg,
          value: e.value
        }))
      }
    });
  }
  next();
};

app.post('/users', validateUser, handleValidation, async (req, res) => {
  const user = await db.createUser(req.body);
  res.status(201).json(user);
});

// ✅ Parameterized queries (prevent SQL injection)
app.get('/users', async (req, res) => {
  const users = await db.query(
    'SELECT id, name, email FROM users WHERE name = ?',
    [req.query.name]
  );
  res.json(users);
});

// ✅ Safe MongoDB queries
app.post('/login', async (req, res) => {
  // Ensure email and password are strings
  const email = String(req.body.email || '');
  const password = String(req.body.password || '');

  const user = await db.users.findOne({ email });
  if (!user || !await bcrypt.compare(password, user.passwordHash)) {
    return res.status(401).json({ error: 'Invalid credentials' });
  }
  // ...
});

// ✅ Path traversal prevention
app.get('/files/:filename',
  param('filename')
    .matches(/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+$/)
    .withMessage('Invalid filename'),
  handleValidation,
  (req, res) => {
    const uploadsDir = path.resolve('./uploads');
    const filePath = path.join(uploadsDir, req.params.filename);

    // Ensure path is within uploads directory
    if (!filePath.startsWith(uploadsDir)) {
      return res.status(400).json({ error: 'Invalid path' });
    }

    res.sendFile(filePath);
  }
);

// ✅ Array size limits
app.post('/batch',
  body('items')
    .isArray({ min: 1, max: 100 })
    .withMessage('Items must be array of 1-100 elements'),
  body('items.*.id')
    .isUUID()
    .withMessage('Each item must have valid UUID'),
  handleValidation,
  async (req, res) => {
    const results = await processBatch(req.body.items);
    res.json(results);
  }
);

// ✅ JSON depth/size limits
const jsonParser = express.json({
  limit: '100kb', // Max request body size
  strict: true    // Only accept arrays and objects
});

app.use('/api', jsonParser);
```

```python
# ✅ FastAPI with Pydantic validation
from fastapi import FastAPI, Query, Path, Body, HTTPException
from pydantic import BaseModel, EmailStr, Field, validator, HttpUrl
from typing import Optional, List
import re
import bleach

app = FastAPI()

class UserCreate(BaseModel):
    email: EmailStr
    password: str = Field(..., min_length=8, max_length=100)
    name: str = Field(..., min_length=1, max_length=100)
    age: Optional[int] = Field(None, ge=0, le=150)
    website: Optional[HttpUrl] = None

    @validator('password')
    def password_strength(cls, v):
        if not re.search(r'[A-Z]', v):
            raise ValueError('Must contain uppercase letter')
        if not re.search(r'[a-z]', v):
            raise ValueError('Must contain lowercase letter')
        if not re.search(r'\d', v):
            raise ValueError('Must contain digit')
        return v

    @validator('name')
    def sanitize_name(cls, v):
        # Only allow letters, spaces, hyphens, apostrophes
        if not re.match(r"^[a-zA-Z\s'-]+$", v):
            raise ValueError('Name contains invalid characters')
        # Sanitize HTML
        return bleach.clean(v, tags=[], strip=True)

class BatchRequest(BaseModel):
    items: List[str] = Field(..., min_items=1, max_items=100)

    @validator('items', each_item=True)
    def validate_uuid(cls, v):
        if not re.match(r'^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$', v, re.I):
            raise ValueError('Invalid UUID format')
        return v

@app.post("/users")
async def create_user(user: UserCreate):
    return await db.create_user(user.dict())

@app.get("/users/{user_id}")
async def get_user(
    user_id: int = Path(..., ge=1, le=2147483647, description="User ID")
):
    return await db.get_user(user_id)

@app.get("/search")
async def search(
    q: str = Query(..., min_length=1, max_length=100),
    page: int = Query(1, ge=1, le=1000),
    limit: int = Query(20, ge=1, le=100)
):
    # q is automatically validated and constrained
    return await db.search(q, page, limit)
```

## Input Validation Checklist

| Check | Why |
|-------|-----|
| Type validation | Prevent type confusion attacks |
| Length limits | Prevent buffer overflows, DoS |
| Character whitelist | Prevent injection attacks |
| Range validation | Ensure business logic integrity |
| Format validation | Email, URL, UUID patterns |
| Sanitization | Remove/escape dangerous content |
| Array size limits | Prevent memory exhaustion |
| Nested depth limits | Prevent stack overflow |

**Benefits:**
- Prevents SQL/NoSQL injection attacks from manipulating queries
- Sanitizing input stops cross-site scripting (XSS) attacks
- Filename validation prevents unauthorized file access via path traversal
- Size limits prevent memory exhaustion and denial-of-service attacks
- Ensures only valid data enters your system, maintaining data integrity
- Catches bad data at the API boundary before it causes downstream errors

Reference: [OWASP Input Validation Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html)
