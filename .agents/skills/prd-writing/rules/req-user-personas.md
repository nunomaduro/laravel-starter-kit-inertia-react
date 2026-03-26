---
title: Define Target User Personas
impact: HIGH
impactDescription: "Ensures you build for real users, not abstractions"
tags: requirements, personas, users, empathy
---

## Define Target User Personas

**Impact: HIGH (Ensures you build for real users, not abstractions)**

Define who will use the feature with enough detail to make design and prioritization decisions. A persona is not a job title — it's a description of goals, context, and constraints.

## Incorrect

```markdown
<!-- Bad: vague or missing personas -->
## Users
- Admin users
- Regular users
- External users
```

**Problems:**
- Job titles, not personas — tells you nothing about needs
- No context about technical skill level
- No understanding of what they care about
- Can't prioritize features without knowing who benefits most
- "External users" could mean anything

## Correct

```markdown
<!-- Good: actionable personas with context -->
## User Personas

### Primary: Marketing Manager (Maya)
- **Role:** Creates 10-20 campaign links per week
- **Technical skill:** Non-technical, comfortable with web UIs
- **Goal:** Track which channels drive the most traffic
- **Pain point:** Currently uses Bitly but needs self-hosted for data privacy
- **Key need:** Quick link creation, shareable analytics reports

### Secondary: Developer (Dev)
- **Role:** Integrates short URLs into automated email campaigns
- **Technical skill:** Full-stack developer
- **Goal:** Programmatically generate and manage short URLs
- **Pain point:** Manual link creation doesn't scale for automated workflows
- **Key need:** API access (future consideration), bulk operations

### Admin: System Administrator (Amir)
- **Role:** Manages users and application settings
- **Technical skill:** Technical, manages server infrastructure
- **Goal:** Control who has access, monitor usage
- **Pain point:** No visibility into system usage or user activity
- **Key need:** User management, usage dashboards
```

**Benefits:**
- Design decisions reference specific personas ("Maya needs this to be one-click")
- Prioritization is persona-driven (Primary > Secondary > Admin)
- Engineering understands the "why" behind each requirement
- QA can test from each persona's perspective

## Why

1. **Drives prioritization**: Primary persona needs ship first
2. **Informs UX decisions**: Maya needs simplicity; Dev needs power
3. **Prevents feature bloat**: If no persona needs it, don't build it
4. **Creates shared language**: Team says "Maya's workflow" instead of "the user"
