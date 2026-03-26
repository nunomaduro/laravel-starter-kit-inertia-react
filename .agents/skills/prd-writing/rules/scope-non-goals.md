---
title: List Non-Goals to Protect Timeline
impact: HIGH
impactDescription: "Clarifies intent by stating what success is NOT"
tags: scope, non-goals, focus, prioritization
---

## List Non-Goals to Protect Timeline

**Impact: HIGH (Clarifies intent by stating what success is NOT)**

Non-goals are different from out-of-scope features. Non-goals state what the feature intentionally does NOT optimize for, even if it could. They clarify design intent and prevent over-engineering.

## Incorrect

```markdown
<!-- Bad: no non-goals — everything is implicitly a goal -->
## Goals
- Create a URL shortening service
- Track click analytics
- Support SEO metadata
```

**Problems:**
- Is real-time analytics a goal? (expensive to build)
- Is 99.99% uptime a goal? (requires redundancy)
- Is supporting 1M concurrent users a goal? (requires caching infrastructure)
- Without non-goals, engineering over-engineers for imagined requirements

## Correct

```markdown
<!-- Good: explicit non-goals clarify intent -->
## Non-Goals

- **Real-time analytics:** Near-real-time (within 5 seconds via queue)
  is sufficient. We are not building a streaming analytics platform.

- **High availability:** Single-server deployment is acceptable for
  the initial user base (~500 users). Multi-region redundancy is not
  a goal for this release.

- **Sub-millisecond redirects:** Redirects under 100ms (p95) are
  sufficient. We are not competing with CDN-based shorteners.

- **Replacing existing tools:** This supplements, not replaces,
  third-party shorteners. Users may continue using Bitly for
  external campaigns.

- **Mobile-native experience:** Responsive web is sufficient.
  Building a native iOS/Android app is not a goal.
```

**Benefits:**
- Engineering doesn't build a streaming pipeline for "analytics"
- DevOps doesn't architect multi-region for 500 users
- Design doesn't spend weeks on native mobile mockups
- Team focuses effort where it matters

## Why

1. **Prevents over-engineering**: Engineers naturally optimize; non-goals set boundaries
2. **Saves time in review**: "Should we add X?" → "That's a non-goal, see PRD"
3. **Clarifies trade-offs**: "We chose simplicity over scale for this release"
4. **Documents intent**: Future readers understand why certain decisions were made
