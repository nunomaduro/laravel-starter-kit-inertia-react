---
title: Rule Title Here
impact: HIGH
impactDescription: Optional description of impact (e.g., "10-100x query reduction")
tags: database, optimization, tag3
---

## Rule Title Here

**Impact: HIGH (optional impact description)**

Brief explanation of the rule and why it matters for database performance in Laravel 13 applications. This should be clear and concise, explaining the performance implications and when this pattern applies. Focus on measurable improvements and real-world scenarios.

## Incorrect

```php
<?php

// Incorrect approach that causes performance issues
// Explain what makes this problematic
class BadExample
{
    public function index(): View
    {
        // This demonstrates the antipattern
        $posts = Post::all();

        foreach ($posts as $post) {
            echo $post->author->name; // N+1 query
        }
    }
}
```

## Correct

```php
<?php

// Optimized approach using Laravel 13 and PHP 8.3 patterns
class GoodExample
{
    public function index(): View
    {
        // This demonstrates the correct approach
        $posts = Post::with('author')->get();

        foreach ($posts as $post) {
            echo $post->author->name; // No additional queries
        }
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

## Problems

- **Problem 1**: Specific issue with the incorrect approach
- **Problem 2**: Performance or scalability concern
- **Problem 3**: How it degrades under load

## Benefits

- **Benefit 1**: Specific performance improvement
- **Benefit 2**: Scalability or reliability gain
- **Benefit 3**: How it helps in production Laravel applications

Reference: [Laravel 13 Documentation](https://laravel.com/docs/13.x)
