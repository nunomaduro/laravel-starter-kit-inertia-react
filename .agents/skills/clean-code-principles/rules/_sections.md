# Clean Code Principles - Rule Categories

This document defines the organizational structure for clean code principles, ordered by priority and impact.

## Category Overview

| Priority | Category | Impact | Rule Count | Prefix |
|----------|----------|--------|------------|--------|
| 1 | SOLID Principles | CRITICAL | 10 | `solid-` |
| 2 | Core Principles | CRITICAL | 12 | `core-` |
| 3 | Design Patterns | HIGH | 1 | `pattern-` |
| 4 | Code Organization | HIGH | 0 | `org-` |
| 5 | Naming & Readability | MEDIUM | 0 | `name-` |
| 6 | Functions & Methods | MEDIUM | 0 | `func-` |
| 7 | Comments & Documentation | LOW | 0 | `doc-` |

## 1. SOLID Principles (CRITICAL)

**Priority:** CRITICAL
**Impact:** Architectural foundation, affects entire codebase structure
**Prefix:** `solid-`

The five fundamental principles of object-oriented design that guide maintainable, scalable software architecture.

### Rules

#### Single Responsibility Principle (SRP)
- `solid-srp-class` - A class should have only one reason to change
- `solid-srp-function` - A function should do one thing and do it well

#### Open/Closed Principle (OCP)
- `solid-ocp-extension` - Open for extension, closed for modification
- `solid-ocp-abstraction` - Use abstractions to enable extension

#### Liskov Substitution Principle (LSP)
- `solid-lsp-contracts` - Subtypes must honor base type contracts
- `solid-lsp-preconditions` - Cannot strengthen preconditions or weaken postconditions

#### Interface Segregation Principle (ISP)
- `solid-isp-clients` - Client-specific interfaces, not general-purpose
- `solid-isp-interfaces` - Small, cohesive interfaces

#### Dependency Inversion Principle (DIP)
- `solid-dip-abstractions` - Depend on abstractions, not concretions
- `solid-dip-injection` - Inject dependencies from outside

**Key Concepts:**
- Architectural soundness
- Maintainability at scale
- Testability through design
- Flexibility for change
- Reduced coupling

**When to Apply:**
- Designing new features or systems
- Refactoring existing architecture
- Addressing technical debt
- Improving testability
- Planning for future extensibility

---

## 2. Core Principles (CRITICAL)

**Priority:** CRITICAL
**Impact:** Daily coding practices, code quality foundation
**Prefix:** `core-`

Fundamental principles that apply to every line of code you write, regardless of paradigm or language.

### Rules

#### DRY (Don't Repeat Yourself)
- `core-dry` - Every piece of knowledge should have a single representation
- `core-dry-extraction` - Extract duplicated code into reusable functions
- `core-dry-single-source` - Single source of truth for configuration and data

#### KISS (Keep It Simple, Stupid)
- `core-kiss-simplicity` - Choose the simplest solution that works
- `core-kiss-readability` - Optimize for readability over cleverness

#### YAGNI (You Aren't Gonna Need It)
- `core-yagni-features` - Don't implement features before they're needed
- `core-yagni-abstractions` - Don't create abstractions prematurely

#### Other Core Principles
- `core-separation-concerns` - Different concerns in different modules
- `core-composition` - Favor composition over inheritance
- `core-law-demeter` - Only talk to immediate friends
- `core-fail-fast` - Detect and report errors early
- `core-encapsulation` - Hide implementation details

**Key Concepts:**
- Code duplication elimination
- Simplicity over complexity
- Lean development
- Modularity
- Information hiding

**When to Apply:**
- Writing any new code
- Code reviews
- Refactoring sessions
- Bug fixes
- Performance optimization

---

## 3. Design Patterns (HIGH)

**Priority:** HIGH
**Impact:** Solves recurring problems with proven solutions
**Prefix:** `pattern-`

Common design patterns that provide tested solutions to recurring software design problems.

### Rules

- `pattern-repository` - Abstraction for data access layer
- `pattern-factory` - Object creation without specifying exact class (planned)
- `pattern-strategy` - Encapsulate algorithms for runtime selection (planned)
- `pattern-decorator` - Add behavior without modifying objects (planned)
- `pattern-observer` - Define one-to-many dependencies (planned)
- `pattern-adapter` - Make incompatible interfaces work together (planned)
- `pattern-facade` - Simplified interface to complex subsystems (planned)

**Key Concepts:**
- Proven solutions
- Common vocabulary
- Design reusability
- Best practices codified
- Language-agnostic approaches

**When to Apply:**
- Solving common architectural problems
- Improving code structure
- Reducing coupling between components
- Making systems more testable
- Communicating design intent

---

## 4. Code Organization (HIGH)

**Priority:** HIGH
**Impact:** Project structure, module boundaries, discoverability
**Prefix:** `org-`

Principles for organizing code into modules, packages, and directories for maintainability and scalability.

### Rules (Planned)

- `org-feature-folders` - Organize by feature, not by layer
- `org-module-boundaries` - Clear boundaries between modules
- `org-layered-architecture` - Proper separation of layers
- `org-package-cohesion` - Keep related code together
- `org-circular-dependencies` - Avoid circular imports

**Key Concepts:**
- Feature-based organization
- Module boundaries
- Layer separation
- Dependency direction
- Discoverability

**When to Apply:**
- Starting new projects
- Restructuring existing codebases
- Scaling applications
- Onboarding new team members
- Managing microservices

---

## 5. Naming & Readability (MEDIUM)

**Priority:** MEDIUM
**Impact:** Code comprehension, maintenance speed
**Prefix:** `name-`

Conventions and principles for naming variables, functions, classes, and other identifiers.

### Rules (Planned)

- `name-meaningful` - Use intention-revealing names
- `name-consistent` - Follow consistent naming conventions
- `name-searchable` - Avoid magic numbers and strings
- `name-avoid-encodings` - No Hungarian notation
- `name-domain-language` - Use ubiquitous domain language

**Key Concepts:**
- Intention revelation
- Consistency
- Searchability
- Domain terminology
- Avoid abbreviations

**When to Apply:**
- Creating new identifiers
- Refactoring unclear names
- Code reviews
- Domain modeling
- API design

---

## 6. Functions & Methods (MEDIUM)

**Priority:** MEDIUM
**Impact:** Code readability, testability at function level
**Prefix:** `func-`

Principles for writing clean, focused functions and methods.

### Rules (Planned)

- `func-small` - Keep functions small and focused
- `func-single-purpose` - Do one thing only
- `func-few-arguments` - Limit function parameters
- `func-no-side-effects` - Minimize or document side effects
- `func-command-query` - Separate commands from queries

**Key Concepts:**
- Small functions
- Single purpose
- Few parameters
- Pure functions when possible
- Predictable behavior

**When to Apply:**
- Writing new functions
- Refactoring long methods
- Improving testability
- Code reviews
- Performance optimization

---

## 7. Comments & Documentation (LOW)

**Priority:** LOW
**Impact:** Code maintainability, knowledge transfer
**Prefix:** `doc-`

Guidelines for when and how to use comments and documentation effectively.

### Rules (Planned)

- `doc-self-documenting` - Write code that explains itself
- `doc-why-not-what` - Comments should explain why, not what
- `doc-avoid-noise` - No redundant or obvious comments
- `doc-api-docs` - Document public APIs and interfaces

**Key Concepts:**
- Self-documenting code
- Intent over implementation
- Avoid redundancy
- Public API documentation
- Living documentation

**When to Apply:**
- Complex business logic
- Non-obvious algorithms
- Public APIs
- Architectural decisions
- Workarounds and hacks

---

## Rule Naming Convention

All rules follow a consistent naming pattern:

```
{prefix}-{concept}-{specificity}
```

Examples:
- `solid-srp-class` - SOLID principle, SRP concept, class level
- `core-dry-extraction` - Core principle, DRY concept, extraction technique
- `pattern-repository` - Design pattern category, repository pattern

## Priority Levels Explained

- **CRITICAL**: Core architectural and coding principles. Violations significantly impact maintainability, testability, and scalability.
- **HIGH**: Important patterns and organizational principles. Violations complicate future development.
- **MEDIUM**: Best practices that improve code quality. Violations make code harder to read and maintain.
- **LOW**: Nice-to-have practices. Violations have minimal impact but reduce clarity.

## Impact Assessment

- **CRITICAL Impact**: Affects entire system architecture, multiple teams, long-term maintainability
- **HIGH Impact**: Affects module design, team productivity, medium-term maintainability
- **MEDIUM Impact**: Affects code readability, individual developer productivity
- **LOW Impact**: Affects code clarity, documentation quality

## Usage Guidelines

1. Start with SOLID and Core Principles - these are non-negotiable
2. Apply Design Patterns when solving specific architectural problems
3. Use Code Organization principles when structuring projects
4. Follow Naming & Readability guidelines for all new code
5. Apply Function principles during refactoring and new development
6. Add Comments only when necessary to explain complex logic

## Cross-References

Rules often relate to each other. The `related` field in each rule's frontmatter indicates:
- Rules that commonly apply together
- Rules that solve similar problems
- Rules that complement each other
- Rules that provide context or prerequisites

## Evolution

This categorization will evolve as:
- New rules are added
- Patterns emerge from practice
- Team feedback is incorporated
- Language-specific adaptations are needed
