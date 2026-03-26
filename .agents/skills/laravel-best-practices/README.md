# Laravel 13 Best Practices

Comprehensive best practices for Laravel 13 applications.

## Overview

This skill provides guidance for:
- Application architecture and structure
- Eloquent ORM and database patterns
- Controller and routing conventions
- Validation and form requests
- Security best practices
- Performance optimization
- API design patterns

## Categories

### 1. Architecture & Structure (Critical)
Service classes, actions, repositories, and folder organization.

### 2. Eloquent & Database (Critical)
Eager loading, query scopes, migrations, and indexing.

### 3. Controllers & Routing (High)
Resource controllers, route model binding, and API resources.

### 4. Validation & Requests (High)
Form request classes, custom rules, and authorization.

### 5. Security (High)
Mass assignment, SQL injection, XSS, and authentication.

### 6. Performance (Medium)
Caching, queues, and database optimization.

### 7. API Design (Medium)
Versioning, resources, pagination, and error handling.

### 8. Testing (Low-Medium)
Feature tests, unit tests, factories, and mocking.

## Quick Start

```php
// Form Request
class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}

// Controller
class PostController extends Controller
{
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create($request->validated());

        return redirect()->route('posts.show', $post);
    }
}
```

## Usage

This skill triggers automatically when:
- Creating Laravel controllers and models
- Writing migrations and queries
- Implementing validation
- Building APIs

## References

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
