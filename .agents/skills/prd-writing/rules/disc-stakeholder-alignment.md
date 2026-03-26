---
title: Align with Stakeholders on Goals and Constraints
impact: CRITICAL
impactDescription: "Prevents misalignment that causes rework"
tags: discovery, stakeholders, alignment, constraints
---

## Align with Stakeholders on Goals and Constraints

**Impact: CRITICAL (Prevents misalignment that causes rework)**

Identify all stakeholders early and align on goals, constraints, timeline, and success criteria before drafting the full PRD. Different stakeholders have different priorities — surface conflicts early.

## Incorrect

```markdown
<!-- Bad: single-perspective PRD -->
# PRD: Analytics Dashboard

## Goal
Build a comprehensive analytics dashboard with real-time
charts, exportable reports, and custom date ranges.

## Timeline
4 weeks
```

**Problems:**
- Whose goal is this? Product? Engineering? Sales?
- No mention of constraints (budget, team size, existing commitments)
- "Comprehensive" means different things to different people
- Timeline stated without stakeholder buy-in
- No mention of who needs to approve or review

## Correct

```markdown
<!-- Good: multi-stakeholder alignment -->
# PRD: Analytics Dashboard

## Stakeholders
| Role | Person | Priority | Key Concern |
|------|--------|----------|-------------|
| Product Owner | Sarah | User engagement metrics | Must show daily active users |
| Engineering Lead | Ahmad | Technical feasibility | Team has 2 devs available |
| Sales | Wei | Client-facing demos | Needs export to PDF |
| Design | Priya | Usability | Mobile responsive required |

## Agreed Goals
1. Show click analytics per short URL (aligned: all stakeholders)
2. 30-day timeline chart (aligned: product + sales)
3. Device/browser breakdown (aligned: product)

## Agreed Constraints
- **Team:** 2 engineers, 1 designer (no additional headcount)
- **Timeline:** 3 weeks (hard deadline: client demo on April 14)
- **Tech:** Must use existing chart library (Recharts)
- **Budget:** No new third-party services

## Open Disagreements
- Sales wants real-time updates; Engineering says polling every 5min is realistic
  - **Decision needed by:** March 28
  - **Decision maker:** Product Owner
```

**Benefits:**
- Conflicts surface before development starts, not during
- Every constraint has a source and rationale
- Open disagreements have owners and deadlines
- Timeline is grounded in actual team capacity

## Why

1. **Prevents scope surprise**: Sales won't ask for PDF export mid-sprint if it was discussed upfront
2. **Creates shared ownership**: Stakeholders who contribute to the PRD defend it
3. **Surfaces constraints early**: Team size, budget, and deadlines shape what's realistic
4. **Resolves conflicts cheaply**: A meeting costs less than a rewrite
