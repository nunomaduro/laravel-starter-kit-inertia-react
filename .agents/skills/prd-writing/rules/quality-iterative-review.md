---
title: Iterate with Feedback Before Finalizing
impact: MEDIUM
impactDescription: "A PRD written in isolation misses critical context"
tags: quality, review, feedback, iteration, collaboration
---

## Iterate with Feedback Before Finalizing

**Impact: MEDIUM (A PRD written in isolation misses critical context)**

Never ship a PRD without at least one round of feedback from engineering, design, and key stakeholders. A PRD is a living document that improves through review, not a one-shot deliverable.

## Incorrect

```markdown
<!-- Bad: write-once, no review -->
# PRD: Analytics Dashboard
Status: Final

(Written by product manager alone over a weekend.
Shared with engineering on Monday morning.
"Here's what we're building. Sprint starts today.")
```

**Problems:**
- Engineering discovers impossible requirements during implementation
- Design was not consulted — UI doesn't match product vision
- Stakeholders feel blindsided and push back mid-sprint
- "Final" status discourages feedback
- Estimates are wrong because engineers didn't review technical sections

## Correct

```markdown
<!-- Good: iterative review process -->
# PRD: Analytics Dashboard
Status: Draft → Under Review → Approved

## Review History

| Version | Date | Reviewer | Changes |
|---------|------|----------|---------|
| Draft | Mar 20 | Asyraf (Product) | Initial draft |
| v0.2 | Mar 22 | Ahmad (Engineering) | Revised NFRs; added queue dependency |
| v0.3 | Mar 23 | Priya (Design) | Updated user stories with UX feedback |
| v1.0 | Mar 24 | All stakeholders | Approved for development |

## Review Checklist
- [ ] Engineering reviewed technical sections and estimates
- [ ] Design reviewed user stories and personas
- [ ] Stakeholders approved scope and timeline
- [ ] Open questions resolved or marked TBD with owners
- [ ] Success criteria agreed upon by product and engineering
- [ ] Out of scope acknowledged by all stakeholders

## Open Feedback
> "Should we support CSV export from day one?" — Wei (Sales)
> **Decision:** Deferred to v1.1. Added to Out of Scope section.

> "p95 < 100ms for redirects might be tight with geo lookup" — Ahmad
> **Decision:** Geo lookup moved to async job. Redirect itself < 100ms.
```

**Benefits:**
- Technical feasibility validated before sprint starts
- Stakeholders buy in because they contributed
- Open feedback has clear resolution and rationale
- Review history provides accountability and context

## Why

1. **Catches blind spots**: Others see what the author misses
2. **Builds buy-in**: People support what they helped create
3. **Improves estimates**: Engineering reviews before committing
4. **Creates accountability**: Review history shows who approved what
