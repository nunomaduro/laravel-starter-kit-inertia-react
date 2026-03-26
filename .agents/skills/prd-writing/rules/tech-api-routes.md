---
title: Document API and Route Structure
impact: MEDIUM
impactDescription: "Defines the interface between frontend and backend"
tags: technical, api, routes, endpoints, interface
---

## Document API and Route Structure

**Impact: MEDIUM (Defines the interface between frontend and backend)**

List the key routes or API endpoints the feature requires. Include the HTTP method, path, purpose, and auth requirement. This is not a full API spec — it's enough to understand the interface.

## Incorrect

```markdown
<!-- Bad: no route information -->
## Technical Details
The backend will handle CRUD operations for URLs.
```

**Problems:**
- Frontend team doesn't know what endpoints to call
- No understanding of URL structure
- No auth requirements per route
- Can't plan frontend work in parallel with backend

## Correct

```markdown
<!-- Good: clear route table -->
## Routes

### Short URL Management (auth required)
| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/short-urls` | List user's short URLs (paginated) |
| GET | `/short-urls/create` | Show create form |
| POST | `/short-urls` | Store new short URL |
| GET | `/short-urls/{id}` | View short URL + generated links |
| GET | `/short-urls/{id}/edit` | Show edit form |
| PUT | `/short-urls/{id}` | Update short URL |
| DELETE | `/short-urls/{id}` | Delete short URL (cascades) |

### Generated Links (auth required)
| Method | Route | Purpose |
|--------|-------|---------|
| POST | `/short-urls/{id}/links` | Create generated link |
| PUT | `/short-urls/{id}/links/{linkId}` | Update link |
| DELETE | `/short-urls/{id}/links/{linkId}` | Delete link |
| PATCH | `/short-urls/{id}/links/{linkId}/toggle` | Toggle active state |

### Analytics (auth required)
| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/dashboard` | Global analytics dashboard |
| GET | `/links/{linkId}/analytics` | Per-link detailed analytics |

### Public
| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/{slug}` | Redirect to destination URL |

### Admin (admin role required)
| Method | Route | Purpose |
|--------|-------|---------|
| GET/POST/PUT/DELETE | `/users/*` | User management CRUD |
| GET/PUT | `/app-settings` | Application settings |
| POST | `/app-settings/logo` | Upload app logo |

**Note:** `/{slug}` must be the last registered route to avoid
conflicts with named routes. Slug constraint: 4-10 alphanumeric chars.
```

**Benefits:**
- Frontend and backend teams can work in parallel
- Route structure reveals the full scope of work
- Auth requirements are visible per route
- Edge cases (slug vs named route conflict) are documented

## Why

1. **Parallel development**: Frontend builds against the route contract
2. **Scope clarity**: Counting routes gives a realistic view of work
3. **Security review**: Auth requirements are visible at a glance
4. **Integration testing**: Each route is a test target
