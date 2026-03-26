---
title: JSON Naming Conventions
impact: MEDIUM
impactDescription: "Eliminates field-name guessing and mapping errors"
tags: json, naming, camelCase, snake_case
---

## JSON Naming Conventions

**Impact: MEDIUM (Eliminates field-name guessing and mapping errors)**

Inconsistent naming forces developers to guess field names and write tedious mapping code. Picking one convention and applying it everywhere makes the API predictable and reduces integration bugs.

## Incorrect

```json
// ❌ Mixed conventions in the same response
{
  "userId": 123,
  "first_name": "Jane",
  "LastName": "Doe",
  "Email": "jane@example.com",
  "created_at": "2024-01-15",
  "lastLogin": "Jan 20, 2024 3:45 PM",
  "isActive": true,
  "acct_type": "premium",
  "DOB": "1990-05-20",
  "addr": {
    "str": "123 Main St",
    "ZipCode": "90210"
  }
}
```

**Problems:**
- Developers cannot predict whether a field uses camelCase, snake_case, or PascalCase
- Abbreviations like `acct`, `str`, `DOB` are ambiguous
- Date formats vary across fields, requiring per-field parsing
- Mapping between API responses and client models becomes error-prone

## Correct

### snake_case (common for Ruby, Python, PHP APIs)

```json
// ✅ Consistent snake_case
{
  "user_id": 123,
  "first_name": "Jane",
  "last_name": "Doe",
  "email": "jane@example.com",
  "created_at": "2024-01-15T10:30:00Z",
  "last_login_at": "2024-01-20T15:45:00Z",
  "is_active": true,
  "account_type": "premium",
  "date_of_birth": "1990-05-20",
  "address": {
    "street": "123 Main St",
    "zip_code": "90210"
  }
}
```

### camelCase (common for JavaScript/TypeScript APIs)

```json
// ✅ Consistent camelCase
{
  "userId": 123,
  "firstName": "Jane",
  "lastName": "Doe",
  "email": "jane@example.com",
  "createdAt": "2024-01-15T10:30:00Z",
  "lastLoginAt": "2024-01-20T15:45:00Z",
  "isActive": true,
  "accountType": "premium",
  "dateOfBirth": "1990-05-20",
  "address": {
    "street": "123 Main St",
    "zipCode": "90210"
  }
}
```

### Date and Time — Always ISO 8601

```json
// ✅ ISO 8601 with timezone
{
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-03-01T14:22:33+05:30",
  "expires_on": "2024-12-31",
  "duration_seconds": 3600
}

// ❌ Avoid non-standard date formats
{
  "created_at": "Jan 15, 2024",
  "updated_at": "03/01/2024",
  "expires_on": "1704067200"
}
```

### Null vs Omitted Fields

```json
// ✅ Use null for "set but empty" — include the field
{
  "first_name": "Jane",
  "middle_name": null,
  "last_name": "Doe"
}

// ✅ Omit fields that don't apply to this resource
// A "company" user has a company_name; a "personal" user omits it
{
  "first_name": "Jane",
  "last_name": "Doe",
  "account_type": "personal"
}
```

### Boolean Naming

```json
// ✅ Use is_, has_, can_, should_ prefixes
{
  "is_active": true,
  "is_verified": false,
  "has_two_factor": true,
  "can_edit": false,
  "should_notify": true
}

// ❌ Ambiguous boolean names
{
  "active": true,
  "verified": 1,
  "two_factor": "yes",
  "edit": false,
  "notification": true
}
```

**Benefits:**
- Developers can predict any field name without checking the docs
- Automated serialization/deserialization works without custom mappings
- ISO 8601 dates are natively parseable in every language
- Boolean prefixes make the type and intent immediately clear

Reference: [Google JSON Style Guide](https://google.github.io/styleguide/jsoncstyleguide.xml)
