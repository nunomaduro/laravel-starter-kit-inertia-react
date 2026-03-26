---
title: Explore the Codebase Before Drafting
impact: CRITICAL
impactDescription: "Grounds PRD in technical reality"
tags: discovery, codebase, technical-context, architecture, greenfield
---

## Explore the Codebase Before Drafting

**Impact: CRITICAL (Grounds PRD in technical reality)**

Before writing technical sections, assess the project state and adapt your approach. An existing codebase should be explored to understand architecture, models, routes, and patterns. An empty project requires a different discovery approach — gather planned stack and architectural intent from the user.

## Incorrect

```markdown
<!-- Bad: writing requirements without knowing current state -->
## Technical Requirements
- Create a new `urls` table with columns: id, url, slug, user_id
- Build a REST API at /api/v1/urls
- Use JWT authentication
- Deploy as a microservice
```

**Problems:**
- May duplicate existing tables or conflict with current schema
- Ignores existing authentication mechanism (maybe it's session-based)
- Proposes architecture (microservice) without understanding current setup
- Route structure may conflict with existing conventions

## Correct

### Scenario 1: Existing Project (code exists)

```markdown
<!-- Good: grounded in codebase exploration -->
## Current State (from codebase review)

**Existing Architecture:**
- Laravel 13 monolith with Inertia.js/React frontend
- Session-based auth via Laravel Fortify (not JWT)
- SQLite database with Eloquent ORM
- Database queue driver for async jobs

**Existing Models:**
- User (has is_admin flag, 2FA support)
- No URL-related models exist yet

**Existing Patterns:**
- Controllers use Form Request validation
- Policies handle authorization (owner-only access)
- Routes follow RESTful resource conventions

## Technical Requirements (aligned with current architecture)
- Add `short_urls` table following existing migration conventions
- Create ShortUrlController as a resource controller
- Use ShortUrlPolicy for owner-based authorization
- Reuse existing session auth — no new auth mechanism needed
- Follow existing Form Request pattern for validation
```

### Scenario 2: Empty/Greenfield Project (no code yet)

```markdown
<!-- Good: gathering architectural intent -->
## Planned Architecture (from stakeholder input)

**Decisions Made:**
- Framework: Laravel 13 with Inertia.js/React
- Database: SQLite for development, PostgreSQL for production
- Auth: Laravel Fortify (email/password + 2FA)
- Deployment: Single server, self-hosted

**Decisions TBD:**
- Queue driver: TBD (database vs Redis — depends on expected load)
- Cache strategy: TBD (evaluate after MVP)
- File storage: TBD (local vs S3)

## Technical Requirements (based on planned stack)
- Follow Laravel resource controller conventions
- Use Eloquent ORM with migrations for all schema changes
- Implement Form Request validation from day one
- Set up policy-based authorization early
```

**Benefits:**
- Requirements are immediately actionable by engineering
- No surprises during implementation
- Empty projects get intentional architecture instead of accidental
- Accurate effort estimation based on real complexity

## Why

1. **Prevents impossible requirements**: Can't require JWT when the app uses sessions
2. **Reduces estimation errors**: Knowing the codebase reveals true complexity
3. **Handles both project states**: Empty and existing projects need different approaches
4. **Identifies reuse opportunities**: Existing services and patterns save development time

Reference: [Shape Up - Basecamp](https://basecamp.com/shapeup)
