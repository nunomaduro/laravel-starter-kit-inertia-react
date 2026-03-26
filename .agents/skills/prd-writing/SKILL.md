---
name: prd-writing
description: Step-by-step workflow for writing Product Requirements Documents. Use when creating PRDs, documenting features, writing specifications, or planning new products. Triggers on "write PRD", "create PRD", "document requirements", "feature spec", or "product requirements".
license: MIT
metadata:
  author: agent-skills
  version: "1.0.0"
---

# PRD Writing

Step-by-step workflow for writing clear, actionable Product Requirements Documents. Follow the 6-step process below, using the 25 rules across 7 categories as supporting knowledge.

## Metadata

- **Version:** 1.0.0
- **Rule Count:** 25 rules across 7 categories
- **License:** MIT

## Workflow

Follow these steps in order. Skip a step only if the user has already provided that information. Do NOT start drafting before completing discovery.

### Step 1: Assess Project State

Determine what you're working with:

- **Existing project** — code exists → explore the codebase first (models, routes, controllers, auth, patterns). See `disc-codebase-exploration`.
- **Empty/greenfield project** — no code yet → ask the user what they want to build first. Brainstorm together until the product idea and core features are clear, then ask about planned stack, architecture decisions, and constraints.

### Step 2: Ask Clarifying Questions

Ask 3-5 targeted questions to fill knowledge gaps. Use **lettered options** (A/B/C) when there are clear choices, and open-ended questions when you need free-text answers. Focus on:

1. **Problem** — What problem are we solving? Why now?
2. **Users** — Who are the target users?
3. **Scope** — What should it NOT do?
4. **Success** — How do we know it's done?
5. **Constraints** — Timeline, budget, team size?

Mark unknowns as TBD, not assumptions. See `disc-clarifying-questions`.

### Step 3: Draft the PRD

Use the template from `struct-prd-template` and fill in all 12 sections:

1. **Executive Summary** — 1 paragraph: problem, solution, impact
2. **Problem Statement** — what, who, evidence, why now
3. **Goals & Success Metrics** — 3-5 measurable KPIs
4. **User Personas** — primary and secondary with roles/goals/pain points
5. **User Stories & Acceptance Criteria** — "As a / I want / So that" with checkboxes
6. **Functional Requirements** — numbered FR-1, FR-2, etc.
7. **Non-Functional Requirements** — performance, security, accessibility with specific numbers
8. **Technical Specifications** — data model, auth, routes, integrations
9. **Out of Scope** — what we're NOT building and why
10. **Non-Goals** — what we're NOT optimizing for
11. **Dependencies & Risks** — blockers with owners and mitigations
12. **Open Questions** — unresolved items with owners and due dates

**Key rules to follow while drafting:**
- Start with the problem, not the solution (`disc-problem-first`)
- Replace vague language with numbers (`metric-no-vague-language`)
- Every requirement must be testable (`quality-acceptance-criteria`)
- Use Given/When/Then for complex acceptance criteria

### Step 4: Present for Review

Show the draft to the user. Ask specifically:
- Does the problem statement match your understanding?
- Are user stories missing any workflows?
- Are the scope boundaries correct?
- Any open questions I should resolve?

### Step 5: Revise Based on Feedback

Incorporate feedback, resolve open questions, and update the PRD. Add a review history entry if the PRD will be shared with a team.

### Step 6: Save the PRD

Save to `docs/prd/{feature-name}.md` using kebab-case naming. Include frontmatter:

```markdown
---
title: Feature Name
status: draft
author: Author Name
created: YYYY-MM-DD
updated: YYYY-MM-DD
---
```

## Rules Reference

The 25 rules below provide detailed guidance for each step. Read them when you need deeper context.

### 1. Discovery (CRITICAL)

- `disc-problem-first` - Start with the problem, not the solution
- `disc-clarifying-questions` - Ask clarifying questions before writing
- `disc-codebase-exploration` - Explore the codebase before drafting
- `disc-stakeholder-alignment` - Align with stakeholders on goals and constraints

### 2. Structure (CRITICAL)

- `struct-standard-sections` - Use standardized PRD sections
- `struct-executive-summary` - Write a concise executive summary
- `struct-prd-template` - Provide a ready-to-use PRD template
- `struct-output-location` - Save PRDs to a consistent file location
- `struct-single-source-of-truth` - PRD is the definitive reference for a feature

### 3. Requirements (HIGH)

- `req-user-personas` - Define target user personas
- `req-user-stories` - Write user stories with acceptance criteria
- `req-functional` - Define specific functional requirements
- `req-non-functional` - Define non-functional requirements

### 4. Scope (HIGH)

- `scope-out-of-scope` - Explicitly define what is out of scope
- `scope-non-goals` - List non-goals to protect timeline
- `scope-dependencies` - Identify dependencies and blockers

### 5. Metrics (HIGH)

- `metric-measurable-success` - Define measurable success criteria
- `metric-no-vague-language` - Replace vague terms with quantifiable benchmarks
- `metric-kpis` - Define 3-5 key performance indicators

### 6. Technical (MEDIUM)

- `tech-data-model` - Document the data model and relationships
- `tech-auth-model` - Define authentication and authorization
- `tech-api-routes` - Document API and route structure
- `tech-integration-points` - Identify third-party integrations

### 7. Quality (MEDIUM)

- `quality-acceptance-criteria` - Write testable acceptance criteria
- `quality-iterative-review` - Iterate with feedback before finalizing

## References

- [Lenny's Newsletter - How to Write a Great PRD](https://www.lennysnewsletter.com/p/how-to-write-a-great-prd)
- [Silicon Valley Product Group - Product Discovery](https://www.svpg.com/product-discovery/)
- [Shape Up - Basecamp](https://basecamp.com/shapeup)
- [Writing Great Specifications - Kamil Nicieja](https://www.manning.com/books/writing-great-specifications)
- [Atlassian - Product Requirements Document](https://www.atlassian.com/agile/product-management/requirements)

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
