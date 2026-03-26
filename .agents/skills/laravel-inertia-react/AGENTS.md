# Laravel + Inertia.js + React Skill for AI Agents

This document provides guidance for AI agents on how to effectively use the Laravel + Inertia.js + React skill patterns.

## Overview

This skill provides comprehensive patterns for building modern monolithic applications with Laravel backend, Inertia.js adapter, and React frontend. It covers 24 rules across 6 key categories.

## When to Apply This Skill

Apply this skill when:

- Building Laravel applications with Inertia.js and React
- Creating or modifying Inertia page components
- Implementing forms with the useForm hook
- Setting up navigation with Inertia's Link component
- Configuring shared data through HandleInertiaRequests
- Implementing persistent layouts
- Working with TypeScript in Inertia React apps
- Handling file uploads, validation, or flash messages

## Skill Categories

### 1. Page Components (CRITICAL)
**Priority**: CRITICAL | **Rules**: 6

Core patterns for structuring Inertia page components:
- Component structure with TypeScript interfaces
- Props typing extending PageProps
- Head management for SEO and meta tags
- Layout assignment using the layout property
- Scroll preservation during navigation
- Partial reloads for performance

**When to reference**: Creating new pages, typing props, managing document head, or optimizing page loads.

### 2. Forms & Validation (CRITICAL)
**Priority**: CRITICAL | **Rules**: 8

Complete form handling with Inertia's useForm hook:
- Basic useForm setup and methods
- Displaying Laravel validation errors
- File uploads with progress tracking
- Form state management (dirty, processing, errors)
- Data transformation before submission
- Reset and cleanup patterns

**When to reference**: Building forms, handling validation, file uploads, or managing form state.

### 3. Navigation (CRITICAL-HIGH)
**Priority**: CRITICAL to HIGH | **Rules**: 5

SPA-like navigation without full page reloads:
- Link component for internal navigation
- Programmatic navigation with router
- External links and download handling
- State preservation during navigation
- History management with replace option

**When to reference**: Implementing navigation, links, routing, or programmatic page transitions.

### 4. Shared Data (CRITICAL-HIGH)
**Priority**: CRITICAL to HIGH | **Rules**: 4

Global props shared across all pages:
- Authentication user data
- Flash messages from Laravel
- Ziggy routes for type-safe routing
- App configuration and feature flags

**When to reference**: Accessing user data, displaying flash messages, using Laravel routes in JS, or sharing global config.

### 5. Layouts (CRITICAL)
**Priority**: CRITICAL | **Rules**: 1

Persistent layout implementation:
- Layout property pattern
- Nested layouts
- State preservation across navigation
- Performance benefits

**When to reference**: Setting up layouts, preventing layout re-renders, or optimizing navigation performance.

### 6. Advanced Patterns (MEDIUM)
**Priority**: MEDIUM | **Rules**: Covered in other sections

Advanced techniques integrated into other categories:
- Partial reloads (Page Components)
- Scroll preservation (Page Components)
- Progress indicators (Forms)
- Dirty tracking (Forms)

## Integration Patterns

### Laravel Controller → Inertia Page

```php
// Laravel Controller
public function index(): Response
{
    return Inertia::render('Users/Index', [
        'users' => User::paginate(10),
        'filters' => request()->only('search', 'role'),
    ]);
}
```

```tsx
// React Page Component
interface Props extends PageProps {
    users: PaginatedData<User>;
    filters: { search: string; role: string };
}

export default function Index({ users, filters }: Props) {
    // Implementation
}
```

### HandleInertiaRequests → Shared Props

```php
// Laravel Middleware
public function share(Request $request): array
{
    return [
        'auth' => ['user' => $request->user()],
        'flash' => ['success' => session('success')],
    ];
}
```

```tsx
// React Usage
const { auth, flash } = usePage<PageProps>().props;
```

### Form Submission → Laravel Validation

```tsx
// React Form
const { data, setData, post, errors } = useForm({
    name: '',
    email: '',
});

post(route('users.store'));
```

```php
// Laravel Controller
public function store(StoreUserRequest $request)
{
    User::create($request->validated());
    return redirect()->route('users.index')
        ->with('success', 'User created!');
}
```

## Common Patterns to Recommend

### 1. Type-Safe Page Components

Always extend PageProps and define interfaces:

```tsx
interface Props extends PageProps {
    users: User[];
    stats: Stats;
}

export default function Dashboard({ auth, users, stats }: Props) {
    // auth comes from PageProps (shared data)
    // users and stats are page-specific props
}
```

### 2. Form Handling with useForm

Use the useForm hook for all forms:

```tsx
const { data, setData, post, processing, errors } = useForm({
    // initial values
});
```

### 3. Navigation with Link

Use Link for internal navigation:

```tsx
<Link href={route('users.show', user.id)}>View User</Link>
```

### 4. Programmatic Navigation

Use router for programmatic navigation:

```tsx
router.post(route('users.store'), data, {
    onSuccess: () => reset(),
});
```

### 5. Persistent Layouts

Assign layouts using the layout property:

```tsx
Dashboard.layout = (page) => <AppLayout>{page}</AppLayout>;
```

## Best Practices for AI Agents

1. **Always Type Props**: Use TypeScript interfaces extending PageProps
2. **Use route() Helper**: Never hardcode URLs, always use Ziggy's route() function
3. **Handle Errors**: Display validation errors inline with proper UX
4. **Preserve State**: Use preserveState for filters and preserveScroll for pagination
5. **Lazy Load**: Use Inertia::lazy() for expensive props on Laravel side
6. **Flash Messages**: Set up flash message handling in layouts
7. **File Uploads**: Use progress tracking for file uploads
8. **External Links**: Use regular <a> tags, not Link component
9. **Dirty Tracking**: Warn users about unsaved changes
10. **Replace History**: Use replace: true for filters and search

## Rule File Structure

Each rule file follows this pattern:

```markdown
---
section: [category]
priority: [level]
description: [one-line description]
keywords: [relevant, keywords]
---

# Rule Title

Explanation

## Bad Example
(anti-patterns)

## Good Example
(best practices with TypeScript and Laravel)

## Why
(benefits and reasoning)
```

## Quick Reference

| Task | Reference Rules |
|------|----------------|
| Create page component | page-component-structure, page-props-typing |
| Add form | form-useform-basic, form-validation-errors |
| Handle file upload | form-file-uploads, form-progress-indicator |
| Set up navigation | nav-link-component, nav-programmatic |
| Display flash messages | shared-flash-messages |
| Access current user | shared-auth-user |
| Use Laravel routes | shared-ziggy-routes |
| Create layout | layout-persistent |
| Partial reload | page-partial-reloads |
| Preserve scroll | page-scroll-preservation |

## Tech Stack Requirements

- **PHP**: >= 8.1
- **Laravel**: >= 10.0
- **inertiajs/inertia-laravel**: >= 0.6
- **@inertiajs/react**: >= 1.0
- **React**: >= 18.0
- **TypeScript**: >= 5.0

## Official Documentation

- [Inertia.js](https://inertiajs.com/) - Core concepts and API
- [Laravel](https://laravel.com/docs) - Backend framework
- [React](https://react.dev/) - Frontend library
- [Ziggy](https://github.com/tighten/ziggy) - Laravel routes in JavaScript

## Support

For issues or questions about this skill:
- Review the rule files in the `rules/` directory
- Check the examples in SKILL.md
- Refer to official documentation links above

---

**Version**: 1.0.0
**Last Updated**: 2026-01-17
**Maintainer**: Asyraf Hussin
