---
title: Rule Title Here
impact: HIGH
impactDescription: Optional description of impact (e.g., "Prevents critical security vulnerability")
tags: security, owasp, tag1, tag2
---

## Rule Title Here

**Impact: HIGH (optional impact description)**

Brief explanation of the rule and why it matters in Laravel 13 + React/Inertia.js applications. Explain the security implications, what an attacker could do if this rule is violated, and which OWASP category it maps to.

## Why It Matters

- **Risk**: What can an attacker do if this rule is violated?
- **Impact**: What is the consequence for the application or its users?
- **OWASP**: Which OWASP Top 10 category this maps to

## Incorrect

```php
<?php

// ❌ Bad code example — shows the vulnerable pattern
class BadExample
{
    public function vulnerableMethod(Request $request)
    {
        // This demonstrates what NOT to do
        // Include a realistic example showing the vulnerability
    }
}
```

**Problems:**
- Specific vulnerability 1
- Specific vulnerability 2

## Correct

```php
<?php

declare(strict_types=1);

// ✅ Good code example — shows the secure pattern
class GoodExample
{
    public function secureMethod(Request $request): ReturnType
    {
        // This demonstrates the correct approach
        // Using Laravel 13 and PHP 8.3+ features
    }
}
```

**Why this is safe:**
- Specific security benefit 1
- Specific security benefit 2

## Recommended Patterns

| Pattern | Use Case |
|---------|----------|
| Pattern 1 | When to use |
| Pattern 2 | When to use |

Reference: [OWASP Laravel Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html)
