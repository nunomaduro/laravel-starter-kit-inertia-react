# Clean Code Principles - Agent Documentation

**Version:** 1.0.2
**Focus:** SOLID Principles, Core Principles (DRY, KISS, YAGNI), Design Patterns
**Rules:** 23 (10 SOLID + 12 Core + 1 Pattern); 4 categories planned
**License:** MIT

---

This skill provides comprehensive clean code principles, SOLID guidelines, and design patterns for building maintainable, scalable software.

## Overview

The clean-code-principles skill offers language-agnostic software design principles organized into 7 categories, from CRITICAL (SOLID, Core Principles) to LOW priority (Comments). Each rule provides bad/good examples, explanations, and practical guidance.

## When to Use This Skill

Activate this skill when:
- Reviewing code architecture or design
- Refactoring existing code
- Making design decisions
- Establishing coding standards
- Teaching software design principles
- Addressing technical debt
- Improving code quality and maintainability

## Trigger Phrases

The skill activates on:
- "review architecture"
- "check code quality"
- "SOLID principles"
- "design patterns"
- "clean code"
- "refactoring advice"
- "code smells"
- "best practices"
- "DRY principle"
- "separation of concerns"

## Skill Structure

```
clean-code-principles/
├── SKILL.md              # Main skill definition
├── AGENTS.md             # This file - agent documentation
├── README.md             # User-facing documentation
├── metadata.json         # Structured metadata and references
└── rules/
    ├── _sections.md      # Category definitions and organization
    ├── _template.md      # Template for new rules
    ├── solid-*.md        # SOLID principles (10 rules)
    ├── core-*.md         # Core principles (12 rules)
    └── pattern-*.md      # Design patterns (1 rule)
```

## Rule Categories

### 1. SOLID Principles (CRITICAL - 10 rules)
**Prefix:** `solid-`

Five fundamental object-oriented design principles:
- **S**ingle Responsibility: `solid-srp-class`, `solid-srp-function`
- **O**pen/Closed: `solid-ocp-extension`, `solid-ocp-abstraction`
- **L**iskov Substitution: `solid-lsp-contracts`, `solid-lsp-preconditions`
- **I**nterface Segregation: `solid-isp-clients`, `solid-isp-interfaces`
- **D**ependency Inversion: `solid-dip-abstractions`, `solid-dip-injection`

**Use when:** Designing architecture, planning refactoring, discussing system design

### 2. Core Principles (CRITICAL - 12 rules)
**Prefix:** `core-`

Fundamental coding practices:
- **DRY** (Don't Repeat Yourself): 3 rules
- **KISS** (Keep It Simple): 2 rules
- **YAGNI** (You Aren't Gonna Need It): 2 rules
- **Other**: Separation of Concerns, Composition Over Inheritance, Law of Demeter, Fail Fast, Encapsulation

**Use when:** Daily coding, code reviews, addressing duplication or complexity

### 3. Design Patterns (HIGH - 1 rule)
**Prefix:** `pattern-`

Common solutions to recurring problems:
- Repository Pattern (data access abstraction)

**Use when:** Solving architectural problems, abstracting infrastructure concerns

### 4-7. Future Categories
- **Code Organization** (`org-`): Module structure, boundaries
- **Naming & Readability** (`name-`): Identifier naming conventions
- **Functions & Methods** (`func-`): Function-level best practices
- **Comments & Documentation** (`doc-`): Documentation guidelines

## How to Use Rules

### Accessing Rules

1. **By ID:** Reference specific rules using their ID
   ```
   Check against solid-srp-class and core-dry
   ```

2. **By Category:** Apply all rules in a category
   ```
   Review this class against SOLID principles
   ```

3. **By Scenario:** Choose relevant rules for the context
   ```
   This has duplicated validation logic - check DRY rules
   ```

### Rule Format

Each rule follows a consistent structure:

```markdown
---
id: {rule-id}
title: {Full Title}
category: {category}
priority: {critical|high|medium|low}
tags: [{tags}]
related: [{related-rule-ids}]
---

# {Rule Title}

{One-sentence summary}

## Bad Example
{Anti-pattern code with problems listed}

## Good Example
{Correct implementation with benefits}

## Why
{5-7 benefits explaining the value}

## When to Apply
{Practical scenarios}
```

### Output Format

When identifying violations, use:

```
file:line - [rule-id] Description of issue
```

Example:
```
src/services/UserService.ts:15 - [solid-srp-class] Class handles validation, persistence, and notifications
src/utils/helpers.ts:42 - [core-dry] Email validation duplicated from validators/email.ts
src/models/Order.ts:28 - [core-kiss-simplicity] Overly complex abstraction for simple use case
```

## Agent Strategies

### Strategy 1: Architecture Review

**Goal:** Assess overall system design

**Approach:**
1. Start with SOLID principles (highest impact)
2. Identify violations of SRP, DIP, OCP
3. Check for proper separation of concerns
4. Evaluate composition vs inheritance
5. Assess interface design (ISP)

**Output:** Prioritized list of architectural issues with rule references

### Strategy 2: Code Quality Audit

**Goal:** Find code quality issues in specific files

**Approach:**
1. Scan for duplication (DRY rules)
2. Check complexity (KISS rules)
3. Look for overengineering (YAGNI rules)
4. Verify single responsibility
5. Assess encapsulation

**Output:** File-by-file findings with specific line references

### Strategy 3: Refactoring Guidance

**Goal:** Provide actionable refactoring steps

**Approach:**
1. Identify the primary issue (which rule violated)
2. Reference the good example from that rule
3. Suggest specific refactoring steps
4. Mention related rules that may also help
5. Prioritize changes by impact

**Output:** Step-by-step refactoring plan with rule references

### Strategy 4: Design Decision Support

**Goal:** Help choose between design alternatives

**Approach:**
1. Analyze each option against relevant principles
2. Consider YAGNI (simplest solution first)
3. Evaluate against SOLID principles
4. Check alignment with KISS
5. Recommend based on principle adherence

**Output:** Comparative analysis with principle-based recommendation

### Strategy 5: Pattern Application

**Goal:** Suggest appropriate design patterns

**Approach:**
1. Identify the problem being solved
2. Check if any patterns apply
3. Verify pattern aligns with SOLID principles
4. Ensure pattern isn't overengineering (YAGNI)
5. Provide implementation guidance

**Output:** Pattern recommendation with integration guidance

## Best Practices for Agents

### Do:
- Start with CRITICAL rules (SOLID, Core)
- Provide specific line numbers when possible
- Reference rule IDs in all findings
- Explain WHY a rule matters, not just that it's violated
- Give actionable refactoring suggestions
- Consider the codebase context
- Prioritize by impact (CRITICAL > HIGH > MEDIUM > LOW)
- Show code examples from the rules
- Mention related rules that apply together

### Don't:
- Don't cite every minor violation
- Don't recommend patterns without clear need (YAGNI)
- Don't ignore context (some violations may be acceptable)
- Don't focus on LOW priority rules when CRITICAL issues exist
- Don't provide fixes without explaining the principle
- Don't recommend complex solutions for simple problems (KISS)
- Don't suggest abstractions without concrete use cases (YAGNI)

### Prioritization

When multiple issues exist:
1. **CRITICAL - SOLID violations:** Architecture-level problems
2. **CRITICAL - Core violations:** Duplication, complexity, overengineering
3. **HIGH - Pattern opportunities:** Where patterns genuinely help
4. **MEDIUM - Naming/Functions:** Readability improvements
5. **LOW - Documentation:** Comment and doc issues

## Example Interactions

### Example 1: Architecture Review

**User:** "Review the architecture of this OrderService class"

**Agent Approach:**
1. Check class responsibilities against `solid-srp-class`
2. Evaluate dependencies against `solid-dip-injection`
3. Look for data access against `pattern-repository`
4. Check for proper separation against `core-separation-concerns`

**Agent Output:**
```
Architecture Review - OrderService

CRITICAL Issues:
1. [solid-srp-class] Class has multiple responsibilities:
   - Order validation (should be separate validator)
   - Direct database access (should use repository)
   - Email sending (should be separate service)
   - Payment processing (should be separate service)

2. [solid-dip-abstractions] Direct dependency on concrete implementations:
   - Instantiates MySQLDatabase directly (line 15)
   - Creates StripePaymentGateway in constructor (line 18)

3. [pattern-repository] Direct SQL queries in service layer:
   - Lines 45-50: Raw SQL should be in repository

Recommendations:
1. Split into focused classes following SRP
2. Inject dependencies through constructor (DIP)
3. Create repository abstraction for data access
4. Extract email and payment to separate services

See: solid-srp-class.md, solid-dip-injection.md, pattern-repository.md
```

### Example 2: Code Quality Check

**User:** "Check this validation function for code quality issues"

**Agent Approach:**
1. Check for duplication (`core-dry`)
2. Check complexity (`core-kiss-simplicity`)
3. Check function responsibility (`solid-srp-function`)

**Agent Output:**
```
Code Quality Issues:

utils/validation.ts:
  Line 23 - [core-dry] Email regex duplicated from line 45 and validators/email.ts
  Line 30 - [core-dry-extraction] Password validation logic should be extracted to reusable function
  Line 50 - [solid-srp-function] Function validates both email AND password - split into two functions

Suggested refactoring:
1. Extract common regex patterns to constants file (core-dry-single-source)
2. Create separate validateEmail() and validatePassword() functions (solid-srp-function)
3. Import from shared validators module (core-dry-extraction)

See: core-dry.md, core-dry-extraction.md, solid-srp-function.md
```

### Example 3: Design Decision

**User:** "Should I use inheritance or composition for this User/Admin relationship?"

**Agent Approach:**
1. Apply `core-composition` principle
2. Check LSP implications (`solid-lsp-contracts`)
3. Consider YAGNI (`core-yagni-abstractions`)

**Agent Output:**
```
Recommendation: Use Composition

Analysis:
1. [core-composition] Composition is preferred over inheritance for flexibility
   - Admins can have additional capabilities without inheritance constraints
   - Easier to test each capability independently
   - Can mix different permission sets dynamically

2. [solid-lsp-contracts] Inheritance risks LSP violations
   - If Admin overrides User methods differently
   - If Admin has different behavior expectations

3. [core-yagni-abstractions] Start simple
   - Don't create inheritance hierarchy unless you need polymorphism
   - Compose from Permission and Role objects instead

Suggested approach:
```typescript
class User {
  constructor(
    private permissions: PermissionSet,
    private roleManager: RoleManager
  ) {}
}
```

See: core-composition.md, solid-lsp-contracts.md
```

## Integration with Other Skills

This skill complements:
- **refactoring**: Provides principles for refactoring decisions
- **architecture-review**: Supplies SOLID and design principles
- **code-review**: Offers quality criteria for reviews
- **testing**: Promotes testable design (DIP, SRP)

## Rule Reference Quick Guide

### Most Common Rules

**For duplication:**
- `core-dry` - Main DRY principle
- `core-dry-extraction` - How to extract duplicated code
- `core-dry-single-source` - Configuration and constants

**For complex code:**
- `core-kiss-simplicity` - Avoid overengineering
- `core-kiss-readability` - Optimize for readability
- `core-yagni-features` - Don't build unused features
- `core-yagni-abstractions` - Don't abstract prematurely

**For class design:**
- `solid-srp-class` - Single responsibility for classes
- `solid-dip-injection` - Dependency injection
- `core-separation-concerns` - Separate different concerns
- `core-composition` - Favor composition over inheritance

**For function design:**
- `solid-srp-function` - Single responsibility for functions
- `core-kiss-readability` - Clear, readable functions

**For interfaces:**
- `solid-isp-interfaces` - Small, focused interfaces
- `solid-isp-clients` - Client-specific interfaces

**For extensibility:**
- `solid-ocp-extension` - Open for extension, closed for modification
- `solid-ocp-abstraction` - Use abstractions for extension points

**For inheritance:**
- `solid-lsp-contracts` - Subtypes must honor contracts
- `solid-lsp-preconditions` - Pre/postcondition rules
- `core-composition` - Prefer composition

**For data access:**
- `pattern-repository` - Abstract data persistence

## Metadata

**Version:** 1.0.2
**Rules:** 23 (10 SOLID, 12 Core, 1 Pattern)
**Categories:** 7 (3 implemented, 4 planned)
**Languages:** Language-agnostic (examples in TypeScript)
**Last Updated:** 2026-03-07

## Resources

### Books
- Clean Code (Robert C. Martin)
- Design Patterns (Gang of Four)
- Refactoring (Martin Fowler)
- The Pragmatic Programmer (Hunt & Thomas)

### Online
- [Refactoring Guru](https://refactoring.guru/) - Design patterns and code smells
- [Martin Fowler's Catalog](https://refactoring.com/catalog/) - Refactoring techniques
- [Uncle Bob's Blog](https://blog.cleancoder.com/) - Software craftsmanship

## Contributing New Rules

When adding new rules:
1. Use `rules/_template.md` as starting point
2. Follow naming convention: `{prefix}-{concept}-{specificity}.md`
3. Include YAML frontmatter with all required fields
4. Provide clear bad/good examples
5. Explain 5-7 benefits in "Why" section
6. Add to `metadata.json` rules array
7. Update category counts in `_sections.md`
8. Reference related rules in frontmatter
9. Keep examples language-agnostic (TypeScript preferred)
10. Aim for 300-400 lines of content

## License

MIT License. This skill is provided as-is for educational and development purposes.
