---
title: Provide a Ready-to-Use PRD Template
impact: CRITICAL
impactDescription: "Ensures consistent, complete output every time"
tags: structure, template, output, format, copy-paste
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
