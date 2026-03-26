---
title: Explicitly Define What is Out of Scope
impact: HIGH
impactDescription: "The most effective defense against scope creep"
tags: scope, boundaries, out-of-scope, scope-creep
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
