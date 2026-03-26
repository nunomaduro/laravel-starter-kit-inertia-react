---
title: Include Validation Error Details
impact: CRITICAL
impactDescription: "Enables field-level error feedback for better UX"
tags: errors, validation, form-handling, user-experience
---

## Include Validation Error Details

**Impact: CRITICAL (Enables field-level error feedback for better UX)**

When validation fails, provide specific details about which fields failed and why, enabling clients to display targeted error messages.

## Incorrect

```json
// ❌ Single vague validation error
{
  "error": "Validation failed"
}

// ❌ List without field association
{
  "errors": [
    "Invalid email",
    "Password too short",
    "Name required"
  ]
}

// ❌ Boolean flags without messages
{
  "valid": false,
  "emailValid": false,
  "passwordValid": false
}
```

```javascript
// ❌ Unhelpful validation response
app.post('/users', (req, res) => {
  const errors = validate(req.body);
  if (errors.length > 0) {
    res.status(400).json({ error: 'Validation failed' });
  }
});
```

**Problems:**
- Clients cannot highlight specific form fields with their errors
- Users must guess which fields need attention
- Multiple form submissions required to discover all errors
- Frontend validation libraries cannot map errors to form fields
- No constraint context (e.g., min length, allowed range) for UI feedback

## Correct

```javascript
// ✅ Detailed validation errors
const { body, validationResult } = require('express-validator');

const validateUser = [
  body('email')
    .notEmpty().withMessage('Email is required')
    .isEmail().withMessage('Must be a valid email address')
    .normalizeEmail(),

  body('password')
    .notEmpty().withMessage('Password is required')
    .isLength({ min: 8 }).withMessage('Password must be at least 8 characters')
    .matches(/[A-Z]/).withMessage('Password must contain an uppercase letter')
    .matches(/[a-z]/).withMessage('Password must contain a lowercase letter')
    .matches(/[0-9]/).withMessage('Password must contain a number'),

  body('age')
    .optional()
    .isInt({ min: 0, max: 150 }).withMessage('Age must be between 0 and 150'),

  body('username')
    .notEmpty().withMessage('Username is required')
    .isLength({ min: 3, max: 30 }).withMessage('Username must be 3-30 characters')
    .matches(/^[a-zA-Z0-9_]+$/).withMessage('Username can only contain letters, numbers, and underscores')
];

app.post('/users', validateUser, (req, res) => {
  const errors = validationResult(req);

  if (!errors.isEmpty()) {
    return res.status(400).json({
      error: {
        code: 'validation_error',
        message: 'One or more fields have invalid values',
        details: errors.array().map(err => ({
          field: err.path,
          message: err.msg,
          value: err.value,
          location: err.location  // body, query, params
        }))
      }
    });
  }

  // Create user...
});
```

```json
// ✅ Detailed validation error response
{
  "error": {
    "code": "validation_error",
    "message": "One or more fields have invalid values",
    "details": [
      {
        "field": "email",
        "message": "Must be a valid email address",
        "value": "not-an-email",
        "location": "body"
      },
      {
        "field": "password",
        "message": "Password must be at least 8 characters",
        "value": "short",
        "location": "body",
        "constraints": {
          "minLength": 8,
          "actualLength": 5
        }
      },
      {
        "field": "age",
        "message": "Age must be between 0 and 150",
        "value": -5,
        "location": "body",
        "constraints": {
          "min": 0,
          "max": 150
        }
      }
    ]
  }
}
```

```python
# ✅ FastAPI with detailed validation
from fastapi import FastAPI, HTTPException, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from pydantic import BaseModel, EmailStr, validator, Field
from typing import Optional, List

app = FastAPI()

class UserCreate(BaseModel):
    email: EmailStr
    password: str = Field(..., min_length=8, max_length=100)
    username: str = Field(..., min_length=3, max_length=30, regex=r'^[a-zA-Z0-9_]+$')
    age: Optional[int] = Field(None, ge=0, le=150)

    @validator('password')
    def password_complexity(cls, v):
        errors = []
        if not any(c.isupper() for c in v):
            errors.append('must contain an uppercase letter')
        if not any(c.islower() for c in v):
            errors.append('must contain a lowercase letter')
        if not any(c.isdigit() for c in v):
            errors.append('must contain a number')
        if errors:
            raise ValueError(f"Password {', '.join(errors)}")
        return v

@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request: Request, exc: RequestValidationError):
    details = []
    for error in exc.errors():
        field = '.'.join(str(loc) for loc in error['loc'] if loc != 'body')
        details.append({
            'field': field,
            'message': error['msg'],
            'type': error['type'],
            'context': error.get('ctx', {})
        })

    return JSONResponse(
        status_code=422,
        content={
            'error': {
                'code': 'validation_error',
                'message': f'{len(details)} validation error(s) found',
                'details': details
            }
        }
    )

@app.post("/users")
async def create_user(user: UserCreate):
    return {"id": 1, **user.dict()}
```

```typescript
// ✅ TypeScript/Zod validation with detailed errors
import { z } from 'zod';
import express from 'express';

const UserSchema = z.object({
  email: z.string()
    .min(1, 'Email is required')
    .email('Must be a valid email address'),

  password: z.string()
    .min(8, 'Password must be at least 8 characters')
    .regex(/[A-Z]/, 'Password must contain an uppercase letter')
    .regex(/[a-z]/, 'Password must contain a lowercase letter')
    .regex(/[0-9]/, 'Password must contain a number'),

  username: z.string()
    .min(3, 'Username must be at least 3 characters')
    .max(30, 'Username cannot exceed 30 characters')
    .regex(/^[a-zA-Z0-9_]+$/, 'Username can only contain letters, numbers, and underscores'),

  age: z.number()
    .int('Age must be a whole number')
    .min(0, 'Age cannot be negative')
    .max(150, 'Age cannot exceed 150')
    .optional()
});

app.post('/users', (req, res) => {
  const result = UserSchema.safeParse(req.body);

  if (!result.success) {
    return res.status(400).json({
      error: {
        code: 'validation_error',
        message: 'Validation failed',
        details: result.error.errors.map(err => ({
          field: err.path.join('.'),
          message: err.message,
          code: err.code
        }))
      }
    });
  }

  // Create user with result.data
});
```

## Nested Object Validation

```json
// ✅ Validation errors for nested objects
{
  "error": {
    "code": "validation_error",
    "message": "Validation failed",
    "details": [
      {
        "field": "address.zipCode",
        "message": "ZIP code must be 5 digits",
        "value": "123"
      },
      {
        "field": "address.country",
        "message": "Country is required"
      },
      {
        "field": "contacts[0].email",
        "message": "Invalid email format",
        "value": "bad-email"
      },
      {
        "field": "contacts[1].phone",
        "message": "Phone number must include country code",
        "value": "555-1234"
      }
    ]
  }
}
```

**Benefits:**
- Clients can highlight specific form fields with their errors
- Users see exactly which fields need attention without guessing
- Developers can quickly identify validation issues during development
- Users can fix all issues at once instead of submitting multiple times
- Frontend validation libraries can map errors directly to form fields
- Clear validation responses help document expected input formats

Reference: [JSON:API Error Objects](https://jsonapi.org/format/#error-objects)
