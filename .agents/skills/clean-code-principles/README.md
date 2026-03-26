# Clean Code Principles

Fundamental software design principles for writing maintainable, scalable code.

**Version:** 1.0.2
**Rules:** 23 (10 SOLID + 12 Core + 1 Pattern); 4 categories planned
**License:** MIT

---

## Overview

Language-agnostic guidelines covering SOLID principles, core coding principles (DRY, KISS, YAGNI), and design patterns. Examples are written in TypeScript but apply to any object-oriented or functional language.

## Categories (23 rules implemented)

### 1. SOLID Principles (Critical) — 10 rules
Five fundamental object-oriented design principles: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion.

### 2. Core Principles (Critical) — 12 rules
DRY (3 rules), KISS (2 rules), YAGNI (2 rules), Separation of Concerns, Composition over Inheritance, Law of Demeter, Fail Fast, Encapsulation.

### 3. Design Patterns (High) — 1 rule
Repository pattern for data access abstraction.

### 4. Code Organization (High) — planned
Feature folders, module boundaries, layered architecture, package cohesion, circular dependency prevention.

### 5. Naming & Readability (Medium) — planned
Meaningful names, consistent conventions, no magic numbers, domain language.

### 6. Functions & Methods (Medium) — planned
Small functions, single purpose, limited parameters, pure functions, command-query separation.

### 7. Comments & Documentation (Low) — planned
Self-documenting code, explain why not what, avoid noise, document public APIs.

## Usage

Ask Claude to:
- "Review architecture" — triggers SOLID + Separation of Concerns analysis
- "Check SOLID principles" — targeted SOLID review
- "Check code quality" — DRY, KISS, YAGNI audit
- "Suggest design patterns" — pattern recommendations
- "Refactoring advice" — actionable improvements with rule references

## Key Principles

### SOLID
| Principle | Rule | Summary |
|-----------|------|---------|
| **S**ingle Responsibility | `solid-srp-class`, `solid-srp-function` | One reason to change |
| **O**pen/Closed | `solid-ocp-extension`, `solid-ocp-abstraction` | Open for extension, closed for modification |
| **L**iskov Substitution | `solid-lsp-contracts`, `solid-lsp-preconditions` | Subtypes must be substitutable |
| **I**nterface Segregation | `solid-isp-clients`, `solid-isp-interfaces` | Small, focused interfaces |
| **D**ependency Inversion | `solid-dip-abstractions`, `solid-dip-injection` | Depend on abstractions |

### Core

| Principle | Rules | Summary |
|-----------|-------|---------|
| **DRY** | `core-dry`, `core-dry-extraction`, `core-dry-single-source` | Single source of truth |
| **KISS** | `core-kiss-simplicity`, `core-kiss-readability` | Simplest solution that works |
| **YAGNI** | `core-yagni-features`, `core-yagni-abstractions` | Build only what's needed |

## Output Format

When auditing code:

```
file:line - [rule-id] Description of issue
```

Example:
```
src/services/UserService.ts:15 - [solid-srp-class] Class handles validation, persistence, and email
src/utils/helpers.ts:42 - [core-dry] Email validation duplicated from validators/email.ts
src/models/Order.ts:28 - [core-yagni-abstractions] Generic abstraction used in only one place
```

## References

- **Clean Code** by Robert C. Martin — Foundation for clean code practices
- **Design Patterns** by Gang of Four — Classic design pattern catalog
- **Refactoring** by Martin Fowler — Improving code structure
- **The Pragmatic Programmer** by Hunt & Thomas — Practical software wisdom
- [Refactoring Guru](https://refactoring.guru/) — Design patterns and code smells
- [Martin Fowler's Refactoring Catalog](https://refactoring.com/catalog/) — Comprehensive techniques
