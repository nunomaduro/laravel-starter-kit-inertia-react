---
title: Use Standardized PRD Sections
impact: CRITICAL
impactDescription: "Ensures completeness and scannability"
tags: structure, template, sections, completeness
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
