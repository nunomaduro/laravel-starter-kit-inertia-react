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
