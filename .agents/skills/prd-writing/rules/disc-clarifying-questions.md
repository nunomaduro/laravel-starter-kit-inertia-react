---
title: Ask Clarifying Questions Before Writing
impact: CRITICAL
impactDescription: "Prevents assumptions that derail projects"
tags: discovery, questions, assumptions, alignment, options
---

## Ask Clarifying Questions Before Writing

**Impact: CRITICAL (Prevents assumptions that derail projects)**

Never write a PRD based on incomplete information. Ask 3-5 targeted clarifying questions with lettered options to fill knowledge gaps quickly. This lets the user respond with compact answers like "1A, 2C, 3B" instead of writing paragraphs. Mark genuinely unknown items as TBD rather than guessing.

## Incorrect

```markdown
<!-- Bad: making assumptions without asking -->
# PRD: User Notifications

## Overview
Build an email notification system for all user events.

## Requirements
- Send emails for every action (create, update, delete)
- Use SendGrid as the email provider
- Users receive notifications in real-time
- Support HTML email templates
```

**Problems:**
- Assumes email is the right channel (what about in-app, push, SMS?)
- Assumes all events need notifications (which ones matter to users?)
- Picks a vendor without discussing budget or existing infrastructure
- "Real-time" is undefined — what latency is acceptable?
- No understanding of user preferences or notification fatigue

## Correct

```markdown
<!-- Good: targeted questions with lettered options -->
# PRD: User Notifications — Discovery

Before I draft the PRD, I need to clarify a few things.
Please reply with your choices (e.g., "1A, 2C, 3B, 4A, 5C"):

**1. What problem are we solving?**
   A) Users miss critical updates (e.g., link expired, account changes)
   B) Low engagement — users create links but never return
   C) Both A and B
   D) Other: ___

**2. Which channels should we support?**
   A) Email only (simplest, lowest cost)
   B) In-app notifications only (no external service needed)
   C) Email + in-app (covers online and offline users)
   D) All channels including push/SMS (most complex)

**3. Which events trigger notifications?**
   A) Critical only (account security, link expired, admin actions)
   B) Critical + engagement (weekly summary, milestone clicks)
   C) All CRUD events (every create, update, delete)

**4. Can we ship incrementally?**
   A) Yes — start with email, add channels later
   B) No — need all channels at launch
   C) Yes — start with in-app, add email later

**5. Budget for third-party services?**
   A) $0 — use only built-in Laravel features
   B) Low ($10-50/month) — basic email provider
   C) Flexible — whatever the feature needs

---

## Known Information
- User base: 2,500 active users
- Current state: No notification system exists
- Existing infra: Laravel with database queue driver

## TBD (awaiting stakeholder input)
- Notification channels: TBD (pending answers above)
- Third-party provider: TBD (pending budget answer)
- Compliance requirements: TBD (pending legal review)
```

**Benefits:**
- User can answer all 5 questions in one message ("1C, 2A, 3A, 4A, 5A")
- Lettered options prevent ambiguous free-text answers
- Surfaces hidden assumptions before they become bugs
- TBD items create a clear action list
- "Other" option captures unexpected requirements

## Why

1. **Reduces rework**: Assumptions caught early cost minutes; caught late, they cost sprints
2. **Speeds up discovery**: Lettered options take seconds to answer vs paragraphs
3. **Builds stakeholder trust**: Asking smart questions shows rigor, not ignorance
4. **Creates accountability**: TBD items have clear owners and deadlines
5. **Prevents hallucination**: Never fabricate constraints — mark them as TBD

Reference: [Lenny's Newsletter - How to Write a Great PRD](https://www.lennysnewsletter.com/p/how-to-write-a-great-prd)
