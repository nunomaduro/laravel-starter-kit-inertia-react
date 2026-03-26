---
title: Write User Stories with Acceptance Criteria
impact: HIGH
impactDescription: "Bridges product intent and engineering execution"
tags: requirements, user-stories, acceptance-criteria, definition-of-done
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
