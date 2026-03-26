---
title: Define Authentication and Authorization
impact: MEDIUM
impactDescription: "Determines who can access what"
tags: technical, authentication, authorization, security, roles
---

## Define Authentication and Authorization

**Impact: MEDIUM (Determines who can access what)**

Specify how users authenticate and what authorization rules apply. Who can see what? Who can edit what? Are there roles? The PRD should define the access model at a high level.

## Incorrect

```markdown
<!-- Bad: vague or missing auth requirements -->
## Security
- Users must log in
- Admins have extra permissions
```

**Problems:**
- What login method? (email/password, OAuth, SSO?)
- "Extra permissions" — which ones specifically?
- No mention of resource-level access (can User A see User B's links?)
- No mention of public vs. authenticated routes

## Correct

```markdown
<!-- Good: clear auth and authorization model -->
## Authentication & Authorization

### Authentication
- Email/password login with session-based auth
- Email verification required before accessing features
- Optional two-factor authentication (TOTP)
- Password reset via email

### Roles
| Role | Description |
|------|------------|
| **User** | Registered, verified user |
| **Admin** | User with is_admin flag |

### Authorization Matrix
| Resource | Action | User | Admin | Public |
|----------|--------|------|-------|--------|
| Short URL | Create | Own | Own | - |
| Short URL | View | Own only | Own only | - |
| Short URL | Edit | Own only | Own only | - |
| Short URL | Delete | Own only | Own only | - |
| Generated Link | CRUD | Parent owner | Parent owner | - |
| Link Analytics | View | Link owner | Link owner | - |
| Redirect (/{slug}) | Access | - | - | Yes |
| User Management | CRUD | - | Yes | - |
| App Settings | Edit | - | Yes | - |

### Key Rules
- Users can ONLY access their own short URLs and links (enforced by policy)
- Admin role grants access to user management and app settings
- Admin does NOT grant access to other users' short URLs
- Public redirect route requires no authentication
- Admins cannot delete their own account via admin panel
```

**Benefits:**
- Engineering implements exact policies, not guesses
- QA tests every cell in the authorization matrix
- No "wait, should admins see all URLs?" debates during development
- Security review has a clear specification to audit

## Why

1. **Prevents security gaps**: Missing auth rules become vulnerabilities
2. **Guides implementation**: Matrix maps directly to policies and middleware
3. **Enables testing**: Every cell is a test case
4. **Documents decisions**: "Why can't admins see all URLs?" — it's in the PRD
