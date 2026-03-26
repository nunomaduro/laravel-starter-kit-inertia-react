---
title: Save PRDs to a Consistent File Location
impact: CRITICAL
impactDescription: "Enables discovery and prevents document sprawl"
tags: structure, file-organization, conventions, discoverability
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
