---
title: Replace Vague Terms with Quantifiable Benchmarks
impact: HIGH
impactDescription: "Eliminates subjective interpretation of requirements"
tags: metrics, specificity, benchmarks, quantifiable
---

## Replace Vague Terms with Quantifiable Benchmarks

**Impact: HIGH (Eliminates subjective interpretation of requirements)**

Never use subjective words like "fast," "intuitive," "scalable," "user-friendly," or "secure" without defining what they mean in measurable terms. Vague language creates different expectations for every reader.

## Incorrect

```markdown
<!-- Bad: vague, subjective language -->
## Requirements
- The redirect should be fast
- The UI should be intuitive and user-friendly
- The system should be scalable
- Analytics should load quickly
- The application should be secure
- Links should be easy to create
```

**Problems:**
- "Fast" to a PM means < 2s. To an engineer, it means < 50ms. To a user, it means "instant"
- "Intuitive" is unmeasurable — intuitive to whom?
- "Scalable" without a number is meaningless — 100 users? 1 million?
- Every stakeholder has a different interpretation
- QA cannot test "user-friendly"

## Correct

```markdown
<!-- Good: every vague term replaced with a number -->
## Requirements

| Vague Term | Quantified Requirement |
|-----------|----------------------|
| "Fast redirect" | Redirect response time: p95 < 100ms |
| "Intuitive UI" | New user creates first short URL within 30s without help |
| "Scalable" | Handles 1,000 concurrent redirects/second on single server |
| "Quick analytics" | Analytics page loads in < 3s for links with up to 100K clicks |
| "Secure" | All OWASP Top 10 vulnerabilities addressed; auth on all non-public routes |
| "Easy to create" | Link creation requires max 2 form fields (URL + title) |
| "Reliable" | 99.9% uptime measured monthly; zero data loss |
| "Responsive" | All pages functional on viewports 320px to 2560px |
```

**Benefits:**
- Engineering, product, and QA all share the same expectation
- Every requirement is testable with a pass/fail result
- Performance budgets guide architectural decisions
- No post-launch debates about whether the feature is "fast enough"

## Why

1. **Shared understanding**: Numbers mean the same thing to everyone
2. **Testable**: Automated tests can verify quantified requirements
3. **Architecturally informative**: "100ms redirect" rules out certain approaches
4. **Prevents scope creep**: "Make it faster" becomes "we already hit the 100ms target"

Reference: [Google Engineering Practices - Performance Budgets](https://web.dev/performance-budgets-101/)
