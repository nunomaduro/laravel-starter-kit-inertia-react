---
title: PRD is the Definitive Reference for a Feature
impact: CRITICAL
impactDescription: "Eliminates conflicting information sources"
tags: structure, source-of-truth, consistency, maintenance
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
