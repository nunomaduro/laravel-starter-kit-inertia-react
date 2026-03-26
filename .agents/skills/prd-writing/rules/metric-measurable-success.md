---
title: Define Measurable Success Criteria
impact: HIGH
impactDescription: "Without measurement, you cannot declare success or failure"
tags: metrics, success-criteria, measurement, outcomes
---

## Define Measurable Success Criteria

**Impact: HIGH (Without measurement, you cannot declare success or failure)**

Every PRD must define how success will be measured. Success criteria should be specific, measurable, achievable, relevant, and time-bound (SMART). Define them before building so the team knows what they're aiming for.

## Incorrect

```markdown
<!-- Bad: unmeasurable success criteria -->
## Success Criteria
- Users like the feature
- The system performs well
- Adoption is good
- Fewer support tickets
```

**Problems:**
- "Users like it" — how do you measure liking?
- "Performs well" — compared to what baseline?
- "Adoption is good" — what percentage is "good"?
- "Fewer tickets" — fewer than what? By how much?
- Post-launch: nobody can agree whether the feature succeeded

## Correct

```markdown
<!-- Good: SMART success criteria -->
## Success Criteria

### Primary (must achieve for launch to be considered successful)
1. **Adoption:** 80% of active users create at least one short URL
   within 30 days of launch
2. **Core task completion:** Users create a short URL in < 30 seconds
   (measured from page load to link copied)
3. **Redirect reliability:** 99.9% of redirects succeed without error
   over the first 30 days

### Secondary (monitored, not gating)
4. **Analytics engagement:** 50% of short URL owners view analytics
   at least once per week
5. **Click tracking:** 95% of clicks are recorded with full metadata
   (device, browser, OS)

### Guardrail (must NOT regress)
6. **Page load time:** Dashboard load time does not increase by
   more than 200ms after adding analytics widgets
7. **Error rate:** Application error rate stays below 0.5%

### Measurement Plan
| Metric | Tool | Baseline | Target | Check Date |
|--------|------|----------|--------|------------|
| Adoption rate | DB query (users with >=1 URL) | 0% | 80% | April 24 |
| Task completion time | Manual timing test | N/A | < 30s | Pre-launch |
| Redirect success rate | Error monitoring | N/A | 99.9% | April 24 |
| Analytics engagement | DB query (weekly active) | 0% | 50% | May 24 |
```

**Benefits:**
- Launch decision is data-driven, not opinion-driven
- Guardrail metrics prevent the new feature from hurting existing ones
- Measurement plan specifies exactly how and when to check
- Team celebrates or course-corrects based on real numbers

## Why

1. **Defines "done" objectively**: No debate about whether the feature succeeded
2. **Focuses effort**: Team optimizes for the metrics that matter
3. **Enables iteration**: Miss a target? Now you know what to improve
4. **Builds confidence**: Stakeholders trust data over gut feelings

Reference: [Lenny's Newsletter - How to Write a Great PRD](https://www.lennysnewsletter.com/p/how-to-write-a-great-prd)
