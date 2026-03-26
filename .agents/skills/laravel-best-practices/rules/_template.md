---
title: Rule Title Here
impact: MEDIUM
impactDescription: Optional description of impact (e.g., "2-5× performance improvement")
tags: tag1, tag2, tag3
---

## Rule Title Here

**Impact: MEDIUM (optional impact description)**

Brief explanation of the rule and why it matters in Laravel 13 applications. This should be clear and concise, explaining the performance, maintainability, or security implications. Focus on Laravel-specific context and patterns.

## Bad Example

```php
<?php

// Bad code example here
// Shows the antipattern or incorrect approach
class BadExample
{
    public function badMethod()
    {
        // This demonstrates what NOT to do
    }
}
```

## Good Example

```php
<?php

// Good code example here
// Shows the recommended Laravel 13 pattern
class GoodExample
{
    public function __construct(
        private readonly DependencyService $service,
    ) {}

    public function goodMethod(): ReturnType
    {
        // This demonstrates the correct approach
        // Using modern PHP 8.3 and Laravel 13 features
    }
}
```

**Additional context or variations (optional):**

```php
<?php

// Alternative patterns or edge cases
// Advanced usage examples
// Laravel 13 specific features
```

## Why It Matters

- **Benefit 1**: Specific advantage
- **Benefit 2**: Performance/security/maintainability improvement
- **Benefit 3**: How it helps in real-world Laravel applications

Reference: [Laravel 13 Documentation](https://laravel.com/docs/13.x)
