---
title: Identify Dependencies and Blockers
impact: HIGH
impactDescription: "Surfaces risks before they become delays"
tags: scope, dependencies, blockers, risks, planning
---

## Identify Dependencies and Blockers

**Impact: HIGH (Surfaces risks before they become delays)**

List all external dependencies, internal prerequisites, and potential blockers. Each dependency should have an owner and status. Unknown dependencies are the leading cause of missed deadlines.

## Incorrect

```markdown
<!-- Bad: dependencies not documented -->
## Timeline
- Week 1-2: Build core features
- Week 3: Build analytics
- Week 4: Testing and launch

(no dependency section)
```

**Problems:**
- Week 2: "We need the designer to create the analytics mockups first"
- Week 3: "The geolocation API requires a paid plan — who approves the budget?"
- Week 3: "We need the DevOps team to set up the queue worker"
- Every discovery is a surprise that blocks progress

## Correct

```markdown
<!-- Good: dependencies identified with owners and status -->
## Dependencies & Blockers

### Internal Dependencies
| Dependency | Owner | Status | Blocks |
|-----------|-------|--------|--------|
| Analytics page design mockups | Priya (Design) | In progress, ETA Mar 28 | US-5, US-6 |
| Queue worker deployment | Ahmad (DevOps) | Not started | Click tracking (FR-14) |
| Database migration review | Sarah (DBA) | Scheduled Mar 26 | All data model work |

### External Dependencies
| Dependency | Provider | Status | Fallback |
|-----------|----------|--------|----------|
| Geolocation API (ip-api.com) | Third-party (free tier) | Available | Record clicks without geo data |
| Image processing library | Intervention Image (Composer) | Installed | None needed |

### Potential Blockers
| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| ip-api.com rate limit (45 req/min on free) | Medium | Click geo data incomplete | Queue with backoff; upgrade to paid if needed |
| SQLite concurrency under load | Low | Write contention at scale | Acceptable for <500 users; migrate to PostgreSQL if needed |
| Designer availability (shared with mobile team) | High | Analytics UI delayed | Ship analytics with basic UI; polish in v1.1 |
```

**Benefits:**
- PM can proactively unblock dependencies before they cause delays
- Fallback plans exist for each external risk
- Timeline is realistic because it accounts for waiting time
- No surprises during sprint execution

## Why

1. **Prevents invisible delays**: Known blockers can be managed; unknown ones cause chaos
2. **Enables parallel work**: Team works on unblocked items while waiting
3. **Creates accountability**: Each dependency has an owner
4. **Provides fallbacks**: When a dependency fails, the team knows what to do
