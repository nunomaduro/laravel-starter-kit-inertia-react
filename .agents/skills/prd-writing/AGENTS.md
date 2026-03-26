# PRD Writing - Complete Reference

**Version:** 1.0.0
**Date:** March 2026
**License:** MIT

## Abstract

Step-by-step workflow for writing clear, actionable Product Requirements Documents. Follow the 6-step process: (1) assess project state, (2) ask clarifying questions, (3) draft using the PRD template, (4) present for review, (5) revise based on feedback, (6) save to docs/prd/. Contains 25 rules across 7 categories as supporting knowledge. Each rule includes incorrect and correct examples with problems and benefits.

## References

- [Lenny's Newsletter - How to Write a Great PRD](https://www.lennysnewsletter.com/p/how-to-write-a-great-prd)
- [Silicon Valley Product Group - Product Discovery](https://www.svpg.com/product-discovery/)
- [Shape Up - Basecamp](https://basecamp.com/shapeup)
- [Writing Great Specifications - Kamil Nicieja](https://www.manning.com/books/writing-great-specifications)
- [Atlassian - Product Requirements Document](https://www.atlassian.com/agile/product-management/requirements)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Discovery (disc)

**Impact:** CRITICAL
**Description:** The foundation of a good PRD. Before writing anything, clarify the problem, ask targeted questions to fill knowledge gaps, explore the existing codebase to understand current state, and align with stakeholders on goals and constraints. Never assume context — discover it.

## 2. Structure (struct)

**Impact:** CRITICAL
**Description:** A well-structured PRD is scannable and complete. Use standardized sections so readers know where to find information. Lead with an executive summary, save to a consistent file location, and ensure the PRD serves as the single source of truth for the feature.

## 3. Requirements (req)

**Impact:** HIGH
**Description:** Translate discovered needs into concrete requirements. Define who the users are (personas), what they need (user stories), what the system must do (functional requirements), and what quality attributes it must meet (non-functional requirements). Every requirement must be specific and testable.

## 4. Scope (scope)

**Impact:** HIGH
**Description:** Protect the timeline by explicitly defining boundaries. List what is out of scope to prevent feature creep, state non-goals to clarify intent, and identify dependencies and blockers that could delay delivery. Ambiguous scope is the leading cause of project overruns.

## 5. Metrics (metric)

**Impact:** HIGH
**Description:** Define how success will be measured before building anything. Replace vague language like "fast" or "intuitive" with quantifiable benchmarks. Identify 3-5 key performance indicators that tie directly to the problem being solved. If you can't measure it, you can't ship it with confidence.

## 6. Technical (tech)

**Impact:** MEDIUM
**Description:** Bridge the gap between product and engineering. Document the data model and entity relationships, define authentication and authorization requirements, outline API and route structure, and identify third-party integrations. Keep it high-level — implementation details belong in technical design docs.

## 7. Quality (quality)

**Impact:** MEDIUM
**Description:** Ensure the PRD is actionable and validated. Write testable acceptance criteria using Given/When/Then or checklist format so QA and engineering know exactly what "done" means. Iterate with stakeholder feedback before finalizing — a PRD written in isolation misses critical context.


---

## Start with the Problem, Not the Solution

**Impact: CRITICAL (Foundation of effective product thinking)**

Every PRD must begin by articulating the problem clearly before proposing any solution. Solution-first PRDs lead to building features nobody needs or solving the wrong problem entirely.

## Incorrect

```markdown
<!-- Bad: jumping straight to a solution -->
# PRD: Add Redis Caching

## Overview
We need to implement Redis caching across all API endpoints
with a 5-minute TTL to improve performance.

## Requirements
- Install Redis 7.x
- Cache all GET responses
- Add cache invalidation on write operations
- Configure TTL to 300 seconds
```

**Problems:**
- No explanation of what problem caching solves
- No evidence that caching is the right solution
- No measurement of current performance
- Prescribes implementation before understanding the need
- Impossible to evaluate success without knowing the starting point

## Correct

```markdown
<!-- Good: problem-first approach -->
# PRD: Dashboard Performance Optimization

## Problem
Dashboard page load time averages 4.2s (p95). Users abandon
after 3s, resulting in 35% bounce rate on the dashboard page.
Support tickets about "slow dashboard" increased 3x last quarter.

**Root Cause:** 80% of latency comes from repeated identical
database queries across concurrent users viewing the same data.

## Proposed Solution
Introduce a query-level caching layer for dashboard endpoints.

**Target:**
- Reduce p95 load time from 4.2s to < 1.5s
- Reduce dashboard bounce rate from 35% to < 15%
- Reduce database load by 60% for dashboard queries

## Why Now
- User complaints trending upward (12 tickets last week)
- Database CPU at 78% during peak hours
- New enterprise client onboarding next month with 500 users
```

**Benefits:**
- Clear problem enables the team to evaluate whether the solution fits
- Measurable current state provides a baseline for success
- "Why now" creates urgency and justifies prioritization
- Engineering can propose alternative solutions if caching isn't optimal

## Why

1. **Prevents wasted effort**: Building the wrong thing is more expensive than building nothing
2. **Enables better solutions**: When the problem is clear, the team can brainstorm alternatives
3. **Aligns stakeholders**: Everyone agrees on *what* to solve before debating *how*
4. **Provides evaluation criteria**: You can measure whether the solution actually solved the problem

Reference: [Silicon Valley Product Group - Product Discovery](https://www.svpg.com/product-discovery/)


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


---

## Explore the Codebase Before Drafting

**Impact: CRITICAL (Grounds PRD in technical reality)**

Before writing technical sections, assess the project state and adapt your approach. An existing codebase should be explored to understand architecture, models, routes, and patterns. An empty project requires a different discovery approach — gather planned stack and architectural intent from the user.

## Incorrect

```markdown
<!-- Bad: writing requirements without knowing current state -->
## Technical Requirements
- Create a new `urls` table with columns: id, url, slug, user_id
- Build a REST API at /api/v1/urls
- Use JWT authentication
- Deploy as a microservice
```

**Problems:**
- May duplicate existing tables or conflict with current schema
- Ignores existing authentication mechanism (maybe it's session-based)
- Proposes architecture (microservice) without understanding current setup
- Route structure may conflict with existing conventions

## Correct

### Scenario 1: Existing Project (code exists)

```markdown
<!-- Good: grounded in codebase exploration -->
## Current State (from codebase review)

**Existing Architecture:**
- Laravel 13 monolith with Inertia.js/React frontend
- Session-based auth via Laravel Fortify (not JWT)
- SQLite database with Eloquent ORM
- Database queue driver for async jobs

**Existing Models:**
- User (has is_admin flag, 2FA support)
- No URL-related models exist yet

**Existing Patterns:**
- Controllers use Form Request validation
- Policies handle authorization (owner-only access)
- Routes follow RESTful resource conventions

## Technical Requirements (aligned with current architecture)
- Add `short_urls` table following existing migration conventions
- Create ShortUrlController as a resource controller
- Use ShortUrlPolicy for owner-based authorization
- Reuse existing session auth — no new auth mechanism needed
- Follow existing Form Request pattern for validation
```

### Scenario 2: Empty/Greenfield Project (no code yet)

```markdown
<!-- Good: gathering architectural intent -->
## Planned Architecture (from stakeholder input)

**Decisions Made:**
- Framework: Laravel 13 with Inertia.js/React
- Database: SQLite for development, PostgreSQL for production
- Auth: Laravel Fortify (email/password + 2FA)
- Deployment: Single server, self-hosted

**Decisions TBD:**
- Queue driver: TBD (database vs Redis — depends on expected load)
- Cache strategy: TBD (evaluate after MVP)
- File storage: TBD (local vs S3)

## Technical Requirements (based on planned stack)
- Follow Laravel resource controller conventions
- Use Eloquent ORM with migrations for all schema changes
- Implement Form Request validation from day one
- Set up policy-based authorization early
```

**Benefits:**
- Requirements are immediately actionable by engineering
- No surprises during implementation
- Empty projects get intentional architecture instead of accidental
- Accurate effort estimation based on real complexity

## Why

1. **Prevents impossible requirements**: Can't require JWT when the app uses sessions
2. **Reduces estimation errors**: Knowing the codebase reveals true complexity
3. **Handles both project states**: Empty and existing projects need different approaches
4. **Identifies reuse opportunities**: Existing services and patterns save development time

Reference: [Shape Up - Basecamp](https://basecamp.com/shapeup)


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


---

## Use Standardized PRD Sections

**Impact: CRITICAL (Ensures completeness and scannability)**

Every PRD should follow a consistent section structure. This ensures no critical information is missed and readers know where to find what they need. The order matters — lead with context, then requirements, then details.

## Incorrect

```markdown
<!-- Bad: unstructured, missing key sections -->
# URL Shortener

We need a URL shortener. Users paste long URLs and get short ones.
It should be fast and easy to use. We need analytics too.

Tech stack: Laravel + React.

Let me know if you have questions.
```

**Problems:**
- No problem statement — why do we need this?
- No user personas — who is this for?
- No success criteria — how do we know it works?
- No scope boundaries — what are we NOT building?
- No technical details — what's the data model?
- Unstructured — impossible to review systematically

## Correct

```markdown
<!-- Good: standardized sections -->
# PRD: [Feature Name]

## 1. Executive Summary
One paragraph: problem, solution, expected impact.

## 2. Problem Statement
What problem exists, who it affects, evidence it matters, why now.

## 3. Goals & Success Metrics
3-5 measurable KPIs tied to the problem.

## 4. User Personas
Who are the target users, what are their characteristics.

## 5. User Stories & Acceptance Criteria
"As a [persona], I want [action], so that [benefit]"
with testable acceptance criteria per story.

## 6. Functional Requirements
What the system must do — specific, unambiguous, numbered.

## 7. Non-Functional Requirements
Performance, security, accessibility, scalability constraints.

## 8. Technical Specifications
Data model, auth model, routes/API, integrations.

## 9. Out of Scope & Non-Goals
What we are explicitly NOT building and why.

## 10. Dependencies & Risks
External blockers, technical risks, mitigation strategies.

## 11. Timeline & Milestones
Phased delivery plan (MVP → v1.1 → v2.0).

## 12. Open Questions
Unresolved items with owners and deadlines.
```

**Benefits:**
- Reviewers can jump to the section they care about
- Checklist ensures nothing is forgotten
- Consistent format across all PRDs in the organization
- New team members know what to expect

## Why

1. **Completeness**: A template prevents critical omissions
2. **Scannability**: Stakeholders find relevant sections instantly
3. **Consistency**: All PRDs in the project follow the same structure
4. **Review efficiency**: Reviewers know exactly what to check

Reference: [Atlassian - Product Requirements Document](https://www.atlassian.com/agile/product-management/requirements)


---

## Write a Concise Executive Summary

**Impact: CRITICAL (First thing stakeholders read — sets the tone)**

The executive summary is a single paragraph (3-5 sentences) that captures the problem, proposed solution, and expected impact. A busy stakeholder should understand the entire PRD from this section alone.

## Incorrect

```markdown
<!-- Bad: too long, too vague, solution-focused -->
## Executive Summary

We are going to build a URL shortening service. This will involve
creating a new database table, a controller, views for CRUD operations,
and an analytics dashboard. We'll use Laravel for the backend and
React for the frontend. The project will take approximately 4 weeks.
We need this because competitors have it. The service will support
custom slugs, expiry dates, SEO metadata, and click tracking with
geolocation. We'll also need admin functionality for user management.
Redis might be useful for caching. We should consider rate limiting too.
```

**Problems:**
- Reads like a feature list, not a summary
- No problem statement
- No measurable impact
- Implementation details don't belong here
- Too long — loses the reader

## Correct

```markdown
<!-- Good: problem → solution → impact in one paragraph -->
## Executive Summary

Marketing teams currently share long, unbranded URLs that reduce
click-through rates by 23% compared to short, branded links (source:
Q4 analytics). We propose a self-hosted URL shortening service that
allows authenticated users to create trackable short links with
custom slugs, expiry dates, and SEO metadata. Success is measured by
achieving >90% adoption among marketing team members within 30 days
and a 15% improvement in campaign click-through rates.
```

**Benefits:**
- Problem is clear in the first sentence
- Solution is specific but not prescriptive
- Impact is measurable and time-bound
- Entire PRD context in 4 sentences

## Why

1. **Respects reader time**: Executives and stakeholders skim — give them the essence upfront
2. **Forces clarity**: If you can't summarize it in a paragraph, you don't understand it well enough
3. **Enables quick decisions**: Approvers can say yes/no based on the summary alone
4. **Sets evaluation criteria**: The impact statement becomes the benchmark for success


---

## Provide a Ready-to-Use PRD Template

**Impact: CRITICAL (Ensures consistent, complete output every time)**

Every PRD should follow a concrete template that can be filled in directly. The template ensures completeness, consistency across PRDs, and gives the author a clear starting point instead of a blank page.

## Incorrect

```markdown
<!-- Bad: vague section list without structure -->
The PRD should include:
- Some kind of summary
- Requirements
- Technical stuff
- Timeline maybe
```

**Problems:**
- Author stares at a blank page
- Every PRD ends up with different sections
- Easy to forget critical sections
- No consistent format for reviewers

## Correct

````markdown
<!-- Good: copy-paste template ready to fill in -->
---
title: [Feature Name]
status: draft
author: [Your Name]
created: [YYYY-MM-DD]
updated: [YYYY-MM-DD]
---

# [Feature Name]

## 1. Executive Summary

[1 paragraph: problem, proposed solution, expected impact.]

## 2. Problem Statement

**What:** [What problem exists?]
**Who:** [Who is affected?]
**Evidence:** [Data, support tickets, user feedback proving it matters.]
**Why now:** [Why this is urgent or timely.]

## 3. Goals & Success Metrics

| # | KPI | Baseline | Target | Measure Date |
|---|-----|----------|--------|-------------|
| 1 | [Primary metric] | [Current] | [Target] | [Date] |
| 2 | [Secondary metric] | [Current] | [Target] | [Date] |
| 3 | [Guardrail metric] | [Current] | [Must not regress] | [Date] |

## 4. User Personas

### Primary: [Persona Name]
- **Role:** [What they do]
- **Technical skill:** [Level]
- **Goal:** [What they want to achieve]
- **Pain point:** [Current frustration]

### Secondary: [Persona Name]
- **Role / Goal / Pain point**

## 5. User Stories & Acceptance Criteria

### US-1: [Story Title]
**As a** [persona], **I want to** [action], **so that** [benefit].

**Acceptance Criteria:**
- [ ] [Criterion 1]
- [ ] [Criterion 2]
- [ ] [Criterion 3]

### US-2: [Story Title]
...

## 6. Functional Requirements

- **FR-1:** [System shall...]
- **FR-2:** [System shall...]
- **FR-3:** [System shall...]

## 7. Non-Functional Requirements

### Performance
- **NFR-1:** [Specific measurable requirement]

### Security
- **NFR-2:** [Specific measurable requirement]

### Accessibility
- **NFR-3:** [Specific measurable requirement]

## 8. Technical Specifications

### Data Model
```
[Entity] (1) ──→ (N) [Entity] (1) ──→ (N) [Entity]
```

### Authentication & Authorization
[Auth method, roles, authorization matrix]

### Routes / API
| Method | Route | Purpose | Auth |
|--------|-------|---------|------|
| GET | /resource | List | Yes |
| POST | /resource | Create | Yes |

### Third-Party Integrations
| Service | Purpose | Cost | Fallback |
|---------|---------|------|----------|
| [Name] | [Why] | [$$] | [What if unavailable] |

## 9. Out of Scope

| Feature | Reason | When |
|---------|--------|------|
| [Feature] | [Why excluded] | [Future version or backlog] |

## 10. Non-Goals

- [What we are NOT optimizing for and why]

## 11. Dependencies & Risks

| Dependency/Risk | Owner | Status | Mitigation |
|----------------|-------|--------|------------|
| [Item] | [Who] | [Status] | [Plan B] |

## 12. Open Questions

| # | Question | Owner | Due Date |
|---|----------|-------|----------|
| 1 | [Question] | [Who] | [When] |
````

**Benefits:**
- Author fills in blanks instead of inventing structure
- Every PRD has the same sections in the same order
- Reviewers know exactly where to find information
- Missing sections are visible (empty placeholders stand out)
- Template is copy-paste ready — no reformatting needed

## Why

1. **Eliminates blank page paralysis**: Template gives a clear starting point
2. **Ensures completeness**: Placeholder sections remind the author what's needed
3. **Enables comparison**: PRDs with the same structure are easy to compare
4. **Speeds up review**: Reviewers develop muscle memory for where to look

Reference: [Atlassian - Product Requirements Document](https://www.atlassian.com/agile/product-management/requirements)


---

## Save PRDs to a Consistent File Location

**Impact: CRITICAL (Enables discovery and prevents document sprawl)**

PRDs should be saved in a predictable location within the repository. When team members need to find requirements, they should know exactly where to look without searching.

## Incorrect

```
<!-- Bad: PRDs scattered across the project -->
/README.md                          (PRD buried in README)
/notes/url-shortener-idea.md        (informal notes directory)
/docs/random-feature.md             (generic docs folder)
~/Desktop/prd-draft-v3-final2.md    (local machine)
Google Docs link in Slack            (external, not versioned)
```

**Problems:**
- No consistent location — everyone guesses differently
- Not version-controlled with the code
- External docs get lost or go stale
- Multiple copies with unclear which is current
- New team members can't find existing PRDs

## Correct

```
<!-- Good: consistent, discoverable location -->
/docs/prd/
├── url-shortener.md
├── analytics-dashboard.md
├── user-management.md
└── notification-system.md

# Alternative: organized by epic
/docs/prd/
├── core/
│   ├── url-shortener.md
│   └── redirect-engine.md
├── analytics/
│   ├── click-tracking.md
│   └── dashboard.md
└── admin/
    ├── user-management.md
    └── app-settings.md
```

**Naming convention:** `kebab-case.md` matching the feature name.

**File header:**

```markdown
---
title: URL Shortener
status: approved | draft | deprecated
author: Asyraf Hussin
created: 2026-03-24
updated: 2026-03-24
---
```

**Benefits:**
- `ls docs/prd/` shows all product requirements at a glance
- Version-controlled alongside the code it describes
- Status field prevents confusion about which PRDs are current
- Git blame shows who wrote and modified each section

## Why

1. **Discoverability**: New team members find all PRDs in one place
2. **Version control**: PRD changes are tracked in git history
3. **Co-location**: Requirements live next to the code they describe
4. **Single source of truth**: No competing copies in Slack, Google Docs, or Notion


---

## PRD is the Definitive Reference for a Feature

**Impact: CRITICAL (Eliminates conflicting information sources)**

The PRD should be the single, authoritative document for a feature's requirements. All other artifacts (tickets, designs, tech specs) should reference the PRD, not duplicate its content.

## Incorrect

```markdown
<!-- Bad: requirements scattered across multiple sources -->

# Slack message (March 10):
"Hey team, let's add click analytics with country breakdown"

# Jira ticket PROJ-123:
"Build analytics page showing total clicks"

# Google Doc "Analytics Requirements v2":
"Show clicks by device, browser, OS, and country"

# PRD in repo:
"Analytics dashboard with click tracking"

# Design file in Figma:
Shows referrer breakdown (not mentioned anywhere else)
```

**Problems:**
- Five sources with five different scopes
- No single place to check what's actually required
- Figma has requirements not in the PRD
- Slack message has context that's already lost
- Which source wins when they conflict?

## Correct

```markdown
<!-- Good: PRD is the authority, others reference it -->

# /docs/prd/click-analytics.md (single source of truth)
## Requirements
- FR-1: Show total clicks and unique clicks per link
- FR-2: Device breakdown (mobile, tablet, desktop)
- FR-3: Browser breakdown (top 10)
- FR-4: OS breakdown (top 10)
- FR-5: Country breakdown (top 10, via geolocation)
- FR-6: Referrer breakdown (top 10)
- FR-7: 30-day timeline chart

# Jira ticket PROJ-123:
"Implement click analytics per PRD /docs/prd/click-analytics.md (FR-1 through FR-4)"

# Jira ticket PROJ-124:
"Implement geo analytics per PRD /docs/prd/click-analytics.md (FR-5 through FR-7)"

# Figma design:
"Visual spec for PRD /docs/prd/click-analytics.md"
```

**Benefits:**
- One place to check for the complete picture
- Tickets reference specific requirement IDs
- Design files implement the PRD, not the other way around
- Conflicts are resolved by updating the PRD

## Why

1. **Eliminates ambiguity**: When sources conflict, the PRD wins
2. **Enables traceability**: Every ticket and design traces back to a requirement
3. **Simplifies updates**: Change the PRD once, not five documents
4. **Onboards efficiently**: New team members read one document, not five threads


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


---

## Write User Stories with Acceptance Criteria

**Impact: HIGH (Bridges product intent and engineering execution)**

User stories describe features from the user's perspective using the "As a / I want / So that" format. Every story must have testable acceptance criteria that define "done."

## Incorrect

```markdown
<!-- Bad: vague requirements -->
## Requirements
- Users can create short URLs
- The system should track clicks
- Links can expire
- SEO support needed
```

**Problems:**
- No user perspective — who needs this and why?
- No acceptance criteria — when is "create short URLs" done?
- Ambiguous scope — "SEO support" could mean anything
- Untestable — QA can't write test cases from this

## Correct

```markdown
<!-- Good: structured stories with acceptance criteria -->
## User Stories

### US-1: Create a Short URL
**As a** registered user,
**I want to** submit a destination URL and receive a short link,
**so that** I can share a memorable, trackable link.

**Acceptance Criteria:**
- [ ] User enters a valid URL (max 2048 characters) and a title
- [ ] System generates a unique 6-character alphanumeric slug
- [ ] Short URL is displayed with a copy-to-clipboard button
- [ ] Invalid URLs show a validation error without page reload
- [ ] Created URL appears in the user's short URL list

### US-2: Track Link Clicks
**As a** short URL owner,
**I want to** see how many times my links are clicked,
**so that** I can measure campaign effectiveness.

**Acceptance Criteria:**
- [ ] Each redirect records: IP, user agent, referer, timestamp
- [ ] Click recording is async (does not slow down redirect)
- [ ] Analytics page shows: total clicks, unique clicks, today's clicks
- [ ] 30-day timeline chart displays daily click counts
- [ ] Device breakdown shows mobile/tablet/desktop percentages

### US-3: Set Link Expiry
**As a** short URL owner,
**I want to** set an expiry date on generated links,
**so that** time-sensitive campaigns don't receive traffic after they end.

**Acceptance Criteria:**
- [ ] User can set an optional expiry date (must be in the future)
- [ ] Expired links show a "link expired" page instead of redirecting
- [ ] Link list shows expiry status (active, expired, no expiry)
- [ ] Parent short URL can set a default expiry (1-365 days)
```

**Benefits:**
- Engineering knows exactly what to build and when it's done
- QA converts acceptance criteria directly into test cases
- Product can prioritize stories independently
- Stories are estimable — team can size the work

## Why

1. **Testable**: Each criterion is a pass/fail check
2. **User-centered**: Keeps focus on who benefits and why
3. **Decomposable**: Large features break into independent stories
4. **Traceable**: Each story maps to specific code changes

Reference: [Writing Great Specifications - Kamil Nicieja](https://www.manning.com/books/writing-great-specifications)


---

## Define Specific Functional Requirements

**Impact: HIGH (Eliminates ambiguity in what the system must do)**

Functional requirements describe what the system must do. Each requirement should be specific, numbered, and testable. Avoid compound requirements — one requirement, one behavior.

## Incorrect

```markdown
<!-- Bad: vague, compound, untestable -->
## Requirements
- The system should handle URLs and provide analytics
- Users need to manage their links with full CRUD and sharing
- Support for custom slugs and SEO with image handling
- Admin functionality for managing the application
```

**Problems:**
- Compound requirements (CRUD AND sharing in one bullet)
- "Handle URLs" means nothing specific
- "Support for" is vague — what exactly?
- Not numbered — can't reference in tickets or tests
- Untestable — what does "manage the application" mean?

## Correct

```markdown
<!-- Good: specific, numbered, one behavior each -->
## Functional Requirements

### Short URL Management
- **FR-1:** System shall accept a destination URL (max 2048 chars) and title (max 255 chars)
- **FR-2:** System shall validate destination URL format before saving
- **FR-3:** System shall allow the owner to edit title and destination URL
- **FR-4:** System shall allow the owner to delete a short URL (cascades to all generated links)
- **FR-5:** System shall display a paginated list of the user's short URLs

### Generated Links
- **FR-6:** System shall auto-generate a unique 6-character alphanumeric slug per link
- **FR-7:** System shall accept an optional custom slug (4-10 chars, alphanumeric, unique)
- **FR-8:** System shall reject custom slugs that are already taken
- **FR-9:** System shall accept an optional expiry date (must be in the future)
- **FR-10:** System shall allow toggling a link between active and inactive states

### Redirect
- **FR-11:** System shall redirect `/{slug}` to the destination URL
- **FR-12:** System shall return a 404 page for non-existent slugs
- **FR-13:** System shall show an "expired" page for expired or inactive links
- **FR-14:** System shall record click data asynchronously (not blocking the redirect)
```

**Benefits:**
- Each FR maps to a test case (FR-7 → "test custom slug creation")
- Tickets reference specific FRs ("Implement FR-6 through FR-10")
- Scope changes are visible ("Added FR-15, removed FR-4")
- No room for interpretation

## Why

1. **Traceability**: Every line of code traces to a numbered requirement
2. **Testability**: QA writes one test per FR
3. **Estimability**: Engineering can size individual requirements
4. **Change management**: Adding or removing a requirement is an explicit decision


---

## Define Non-Functional Requirements

**Impact: HIGH (Quality attributes that determine production readiness)**

Non-functional requirements (NFRs) define how the system should behave — performance, security, accessibility, reliability, and scalability. Missing NFRs lead to production surprises.

## Incorrect

```markdown
<!-- Bad: vague or missing NFRs -->
## Non-Functional Requirements
- The system should be fast
- It should be secure
- Must handle lots of users
- Should work on mobile
```

**Problems:**
- "Fast" is not measurable — fast compared to what?
- "Secure" with no specifics is meaningless
- "Lots of users" is not a number
- "Work on mobile" doesn't specify what "work" means

## Correct

```markdown
<!-- Good: specific, measurable NFRs -->
## Non-Functional Requirements

### Performance
- **NFR-1:** Redirect (/{slug}) response time: p95 < 100ms
- **NFR-2:** Dashboard page load: < 2s for up to 10,000 short URLs
- **NFR-3:** Analytics page load: < 3s for links with up to 100K clicks
- **NFR-4:** Click recording job completes within 5s of redirect

### Security
- **NFR-5:** All routes except redirect require authentication
- **NFR-6:** Users can only access their own short URLs (policy-enforced)
- **NFR-7:** Admin routes require is_admin flag
- **NFR-8:** CSRF protection on all form submissions
- **NFR-9:** Security headers: CSP, X-Frame-Options, X-Content-Type-Options

### Reliability
- **NFR-10:** Failed click recording does not block redirect
- **NFR-11:** Geolocation lookup failure does not fail the click recording job
- **NFR-12:** System operates normally with database queue driver

### Accessibility
- **NFR-13:** All pages meet WCAG 2.1 AA compliance
- **NFR-14:** Full keyboard navigation support
- **NFR-15:** Forms have associated labels and error messages

### Compatibility
- **NFR-16:** Responsive design: functional on viewport widths 320px to 2560px
- **NFR-17:** Supported browsers: Chrome, Firefox, Safari, Edge (latest 2 versions)
```

**Benefits:**
- Performance NFRs become automated test assertions
- Security NFRs guide middleware and policy implementation
- Accessibility NFRs are auditable with tools like axe
- Every NFR has a clear pass/fail threshold

## Why

1. **Prevents production surprises**: "It's slow" is caught before launch, not after
2. **Defines done**: A feature isn't complete until NFRs pass
3. **Guides architecture**: NFR-1 (100ms redirect) rules out certain approaches
4. **Enables SLAs**: NFRs become the basis for service level agreements


---

## Explicitly Define What is Out of Scope

**Impact: HIGH (The most effective defense against scope creep)**

Every PRD must include an "Out of Scope" section listing features, capabilities, or improvements that are deliberately excluded from this release. Each exclusion should include a brief reason.

## Incorrect

```markdown
<!-- Bad: no scope boundaries -->
# PRD: URL Shortener

## Features
- Create short URLs
- Click analytics
- Custom slugs
- SEO metadata
- User management

(no out-of-scope section)
```

**Problems:**
- Mid-sprint: "Can we add QR codes too?"
- Mid-sprint: "What about a public API?"
- Mid-sprint: "Custom domains would be nice"
- Every suggestion feels reasonable because no boundaries exist
- Timeline expands invisibly

## Correct

```markdown
<!-- Good: explicit boundaries with reasons -->
## Out of Scope

| Feature | Reason | When |
|---------|--------|------|
| **Public REST API** | Internal use only for MVP; revisit based on demand | v2.0 |
| **Custom domains** | Requires DNS configuration complexity; not needed for initial users | v2.0 |
| **QR code generation** | Nice-to-have; can be added as a standalone feature | Backlog |
| **Bulk URL import** | No evidence of demand yet; defer until user feedback | Backlog |
| **Team/organization workspaces** | Single-tenant is sufficient for launch | v3.0 |
| **Webhook notifications** | No integration partners identified yet | Backlog |
| **Link password protection** | Low demand signal; adds UX complexity | Not planned |

These items are intentionally excluded to protect the timeline
and focus on core value delivery.
```

**Benefits:**
- "Can we add QR codes?" → "It's listed as backlog in the PRD"
- Stakeholders see their requests acknowledged, not ignored
- "When" column gives visibility into future plans
- Timeline stays protected

## Why

1. **Prevents scope creep**: The #1 cause of project delays
2. **Creates a negotiation tool**: Adding scope requires removing scope
3. **Acknowledges future work**: Stakeholders see their ideas aren't lost
4. **Protects the team**: Engineering can point to the PRD when asked for extras


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


---

## Define 3-5 Key Performance Indicators

**Impact: HIGH (Focuses the team on what matters most)**

Identify 3-5 KPIs that directly measure whether the feature solves the stated problem. More than 5 KPIs dilute focus. Fewer than 3 may miss important dimensions. Each KPI should tie back to the problem statement.

## Incorrect

```markdown
<!-- Bad: too many, unfocused, or no KPIs -->
## Metrics
- Number of users
- Page views
- Server uptime
- Database size
- API response time
- Number of short URLs created
- Number of clicks
- Bounce rate
- Session duration
- Error count
- CPU usage
- Memory usage
```

**Problems:**
- 12 metrics — team doesn't know which ones matter
- Mix of vanity metrics (page views) and infrastructure metrics (CPU)
- No connection to the problem being solved
- No targets — just things to measure
- Monitoring everything means prioritizing nothing

## Correct

```markdown
<!-- Good: 3-5 KPIs tied to the problem -->
## Key Performance Indicators

**Problem:** Marketing team has no way to track campaign link performance,
resulting in blind budget allocation across channels.

| # | KPI | Baseline | Target | Why This Matters |
|---|-----|----------|--------|-----------------|
| 1 | **Short URLs created per week** | 0 | 50+ | Measures adoption — are users actually using it? |
| 2 | **Click-through rate improvement** | 2.3% (long URLs) | 3.5%+ | Core value prop — do short URLs perform better? |
| 3 | **Analytics page views per user per week** | 0 | 3+ | Engagement — are users checking their data? |
| 4 | **Time to create a short URL** | N/A | < 30 seconds | Usability — is the workflow efficient? |
| 5 | **Redirect success rate** | N/A | > 99.9% | Reliability — does the core function work? |

### What We're NOT Tracking as KPIs
- Server CPU/memory: Infrastructure concern, not product success
- Total page views: Vanity metric, doesn't indicate value
- Database size: Ops metric, not relevant to user outcomes
```

**Benefits:**
- Team knows exactly which 5 numbers to watch
- Each KPI connects back to the problem statement
- "What we're NOT tracking" prevents metric creep
- Baselines enable before/after comparison

## Why

1. **Focus**: 5 KPIs are manageable; 15 are noise
2. **Alignment**: Everyone optimizes for the same outcomes
3. **Decision-making**: "Will this change improve KPI #2?" guides trade-offs
4. **Accountability**: Post-launch review checks specific numbers, not feelings

Reference: [Amplitude - North Star Metric](https://amplitude.com/blog/north-star-metric)


---

## Document the Data Model and Relationships

**Impact: MEDIUM (Bridges product requirements and database design)**

Include a high-level data model showing the key entities, their relationships, and important attributes. This is not a full schema — it's enough for engineering to understand the data structure before implementation.

## Incorrect

```markdown
<!-- Bad: no data model or too implementation-specific -->
## Technical Details
We need a database.

<!-- Or the opposite extreme: -->
## Database Schema
CREATE TABLE short_urls (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  destination_url VARCHAR(2048) NOT NULL,
  ...40 more columns...
);
```

**Problems:**
- "We need a database" tells engineering nothing
- Full SQL DDL is too prescriptive — that's implementation, not requirements
- No relationships shown — how do entities connect?
- Engineer can't see the big picture

## Correct

````markdown
<!-- Good: high-level model with relationships and key fields -->
## Data Model

### Entity Relationship

```
User (1) ──→ (N) ShortUrl (1) ──→ (N) GeneratedLink (1) ──→ (N) LinkClick
```

### Entities

**User**
- name, email, password, is_admin
- Has many ShortUrls

**ShortUrl**
- title, destination_url, is_active
- SEO fields: seo_title, seo_description, seo_image
- default_expiry_days (1-365, optional)
- Belongs to User, has many GeneratedLinks

**GeneratedLink**
- slug (unique, 4-10 chars alphanumeric)
- label, expires_at, is_active
- SEO overrides: seo_title, seo_description, seo_image
- Belongs to ShortUrl, has many LinkClicks

**LinkClick**
- ip_address, user_agent, referer
- device_type, browser, os, country, city
- clicked_at timestamp
- Belongs to GeneratedLink

### Key Behaviors
- Deleting a ShortUrl cascades to GeneratedLinks and LinkClicks
- GeneratedLink inherits SEO from parent ShortUrl if not overridden
- Slug generation: 6 chars default, retry with longer length on collision
````

**Benefits:**
- Engineering sees the full picture in 30 seconds
- Relationship diagram prevents missing foreign keys
- Key behaviors catch cascade and inheritance logic early
- Detailed enough to start, not so detailed it constrains

## Why

1. **Shared understanding**: Product and engineering agree on the data shape
2. **Reveals complexity**: Relationships expose features that seem simple but aren't
3. **Guides estimation**: More entities = more work; relationships add complexity
4. **Prevents rework**: Catching a missing relationship in the PRD is cheaper than in code


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


---

## Identify Third-Party Integrations

**Impact: MEDIUM (External dependencies introduce risk and cost)**

List all third-party services, APIs, and libraries the feature depends on. For each, document the purpose, cost, rate limits, and what happens if it's unavailable.

## Incorrect

```markdown
<!-- Bad: undocumented external dependencies -->
## Technical Notes
We'll use some API for geolocation.
We might need an image processing library.
```

**Problems:**
- "Some API" — which one? What does it cost?
- "Might need" — is it required or not?
- No fallback plan if the API goes down
- No rate limit awareness — could fail silently in production

## Correct

```markdown
<!-- Good: documented with fallbacks -->
## Third-Party Integrations

| Service | Purpose | Tier | Rate Limit | Cost | Fallback |
|---------|---------|------|-----------|------|----------|
| **ip-api.com** | Geolocation (country/city from IP) | Free | 45 req/min | $0 | Record click without geo data |
| **Intervention Image** | OG image resize/crop to 1200x630 | Composer package | N/A | $0 | Require pre-sized images |

### ip-api.com Details
- **Used for:** Resolving visitor IP → country and city in click tracking
- **Called from:** RecordLinkClick queued job (async, not blocking redirect)
- **Timeout:** 3 seconds, best-effort
- **Failure handling:** Log warning, save click without geo fields
- **Upgrade path:** If free tier limit hit, paid plan at $13/month for 5K req/min

### Intervention Image Details
- **Used for:** Resizing uploaded OG images to 1200x630 (JPEG, 85% quality)
- **Called from:** ImageService on file upload
- **Failure handling:** Reject upload with validation error
- **Dependency:** Requires PHP GD or Imagick extension

### No Integration Required
- **Email:** Not needed for MVP (no notification system)
- **CDN:** Not needed (single-server deployment)
- **Payment:** No paid features in this release
```

**Benefits:**
- Budget is known before development starts
- Rate limits inform architecture (async vs sync)
- Fallback plans prevent production outages
- "No integration required" prevents unnecessary work

## Why

1. **Cost visibility**: Stakeholders approve budget before development
2. **Risk management**: Every external call is a potential failure point
3. **Architecture decisions**: Rate limits drive async/queue patterns
4. **Deployment readiness**: Required extensions and API keys are known upfront


---

## Write Testable Acceptance Criteria

**Impact: MEDIUM (Defines 'done' in a way that QA can verify)**

Every requirement and user story needs acceptance criteria that are binary (pass/fail), specific, and testable. Use checklist format for simple criteria and Given/When/Then for behavioral scenarios.

## Incorrect

```markdown
<!-- Bad: vague, untestable criteria -->
## Acceptance Criteria
- The feature works correctly
- Users can do what they need to do
- No major bugs
- Good performance
- Looks nice on mobile
```

**Problems:**
- "Works correctly" — according to whom?
- "What they need to do" — what specifically?
- "No major bugs" — what's major vs minor?
- "Good performance" — see metric-no-vague-language rule
- QA cannot write test cases from this

## Correct

````markdown
<!-- Good: testable with checklist format -->
## Acceptance Criteria — Short URL Creation

### Checklist Format
- [ ] User enters a valid URL and title, clicks "Create"
- [ ] System generates a 6-char alphanumeric slug
- [ ] New short URL appears in the user's list immediately
- [ ] Copy button copies the full short URL to clipboard
- [ ] Invalid URL (not http/https) shows inline error "Please enter a valid URL"
- [ ] URL exceeding 2048 chars shows "URL is too long (max 2048 characters)"
- [ ] Empty title shows "Title is required"

### Given/When/Then Format (for complex scenarios)

**Scenario: Creating a link with a custom slug**
```
Given I am on the short URL detail page
When I enter custom slug "my-link" (with hyphen)
Then I see validation error "Slug must be alphanumeric only"

Given I am on the short URL detail page
When I enter custom slug "mylink" (valid, 6 chars)
And the slug "mylink" is not taken
Then a new generated link is created with slug "mylink"

Given I am on the short URL detail page
When I enter custom slug "mylink" (already taken)
Then I see validation error "This slug is already in use"
```

**Scenario: Accessing an expired link**
```
Given a generated link with slug "abc123" expired yesterday
When a visitor navigates to /abc123
Then they see the "Link Expired" page
And no click is recorded
```
````

**Benefits:**
- QA converts each criterion directly into a test case
- Developer knows exactly what to implement
- Product can verify completion with a checklist
- Edge cases are caught before development, not during

## Why

1. **Eliminates ambiguity**: Pass/fail criteria leave no room for interpretation
2. **Enables test automation**: Given/When/Then maps to BDD test frameworks
3. **Speeds up review**: PR reviewer checks criteria instead of guessing intent
4. **Prevents regressions**: Criteria become permanent test cases

Reference: [Writing Great Specifications - Kamil Nicieja](https://www.manning.com/books/writing-great-specifications)


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
