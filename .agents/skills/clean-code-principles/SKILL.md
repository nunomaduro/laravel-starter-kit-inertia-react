---
name: clean-code-principles
description: SOLID principles, design patterns, DRY, KISS, and clean code fundamentals. Use when reviewing architecture, checking code quality, refactoring, or discussing design decisions. Triggers on "review architecture", "check code quality", "SOLID principles", "design patterns", or "clean code".
license: MIT
metadata:
  author: AsyrafHussin
  version: "1.0.2"
---

# Clean Code Principles

Fundamental software design principles, SOLID, design patterns, and clean code practices. Language-agnostic guidelines for writing maintainable, scalable software.

## When to Apply

Reference these guidelines when:
- Designing new features or systems
- Reviewing code architecture
- Refactoring existing code
- Discussing design decisions
- Improving code quality

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | SOLID Principles | CRITICAL | `solid-` |
| 2 | Core Principles | CRITICAL | `core-` |
| 3 | Design Patterns | HIGH | `pattern-` |
| 4 | Code Organization | HIGH | `org-` |
| 5 | Naming & Readability | MEDIUM | `name-` |
| 6 | Functions & Methods | MEDIUM | `func-` |
| 7 | Comments & Documentation | LOW | `doc-` |

## Quick Reference

### 1. SOLID Principles (CRITICAL)

- `solid-srp` - Single Responsibility Principle
- `solid-ocp` - Open/Closed Principle
- `solid-lsp` - Liskov Substitution Principle
- `solid-isp` - Interface Segregation Principle
- `solid-dip` - Dependency Inversion Principle

### 2. Core Principles (CRITICAL)

- `core-dry` - Don't Repeat Yourself
- `core-kiss` - Keep It Simple, Stupid
- `core-yagni` - You Aren't Gonna Need It
- `core-separation-of-concerns` - Separate different responsibilities
- `core-composition-over-inheritance` - Favor composition
- `core-law-of-demeter` - Principle of least knowledge
- `core-fail-fast` - Detect and report errors early
- `core-encapsulation` - Hide implementation details

### 3. Design Patterns (HIGH)

- `pattern-factory` - Factory pattern for object creation
- `pattern-strategy` - Strategy pattern for algorithms
- `pattern-repository` - Repository pattern for data access
- `pattern-decorator` - Decorator pattern for behavior extension
- `pattern-observer` - Observer pattern for event handling
- `pattern-adapter` - Adapter pattern for interface conversion
- `pattern-facade` - Facade pattern for simplified interfaces
- `pattern-dependency-injection` - DI for loose coupling

### 4. Code Organization (HIGH) — planned

- `org-feature-folders` - Organize by feature, not layer
- `org-module-boundaries` - Clear module boundaries
- `org-layered-architecture` - Proper layer separation
- `org-package-cohesion` - Related code together
- `org-circular-dependencies` - Avoid circular imports

### 5. Naming & Readability (MEDIUM) — planned

- `name-meaningful` - Use intention-revealing names
- `name-consistent` - Consistent naming conventions
- `name-searchable` - Avoid magic numbers/strings
- `name-avoid-encodings` - No Hungarian notation
- `name-domain-language` - Use domain terminology

### 6. Functions & Methods (MEDIUM) — planned

- `func-small` - Keep functions small
- `func-single-purpose` - Do one thing
- `func-few-arguments` - Limit parameters
- `func-no-side-effects` - Minimize side effects
- `func-command-query` - Separate commands and queries

### 7. Comments & Documentation (LOW) — planned

- `doc-self-documenting` - Code should explain itself
- `doc-why-not-what` - Explain why, not what
- `doc-avoid-noise` - No redundant comments
- `doc-api-docs` - Document public APIs

## Essential Guidelines

For detailed examples and explanations, see the rule files:

- [core-dry.md](rules/core-dry.md) - Don't Repeat Yourself principle
- [pattern-repository.md](rules/pattern-repository.md) - Repository pattern for data access

### SOLID Principles (Summary)

| Principle | Definition |
|-----------|------------|
| **S**ingle Responsibility | A class should have only one reason to change |
| **O**pen/Closed | Open for extension, closed for modification |
| **L**iskov Substitution | Subtypes must be substitutable for base types |
| **I**nterface Segregation | Don't force clients to depend on unused interfaces |
| **D**ependency Inversion | Depend on abstractions, not concretions |

### Core Principles (Summary)

| Principle | Definition |
|-----------|------------|
| **DRY** | Don't Repeat Yourself - single source of truth |
| **KISS** | Keep It Simple - avoid over-engineering |
| **YAGNI** | You Aren't Gonna Need It - build only what's needed |

### Quick Examples

```typescript
// Single Responsibility - one class, one job
class UserService {
  constructor(
    private validator: UserValidator,
    private repository: UserRepository,
  ) {}

  createUser(data) {
    this.validator.validate(data);
    return this.repository.create(data);
  }
}

// Dependency Inversion - depend on abstractions
interface Repository<T> {
  find(id: string): Promise<T | null>;
  save(entity: T): Promise<T>;
}

class OrderService {
  constructor(private repository: Repository<Order>) {}
}

// DRY - single source of truth
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const isValidEmail = (email: string) => EMAIL_REGEX.test(email);

// Meaningful names over magic numbers
const MINIMUM_AGE = 18;
if (user.age >= MINIMUM_AGE) { }
```

## Output Format

When auditing code, output findings in this format:

```
file:line - [principle] Description of issue
```

Example:
```
src/services/UserService.ts:15 - [solid-srp] Class handles validation, persistence, and notifications
src/utils/helpers.ts:42 - [core-dry] Email validation duplicated from validators/email.ts
src/models/Order.ts:28 - [name-meaningful] Variable 'x' should describe its purpose
```

## How to Use

Read individual rule files for detailed explanations:

```
rules/solid-srp-class.md
rules/core-dry.md
rules/pattern-repository.md
```

## References

This skill is built on established software engineering principles:

### Core Books
- **Clean Code** by Robert C. Martin - Foundation for clean code practices
- **Design Patterns** by Gang of Four - Classic design pattern catalog
- **Refactoring** by Martin Fowler - Improving code structure
- **The Pragmatic Programmer** by Hunt & Thomas - Practical wisdom

### Online Resources
- [Refactoring Guru](https://refactoring.guru/) - Design patterns and code smells
- [Martin Fowler's Refactoring Catalog](https://refactoring.com/catalog/) - Comprehensive refactoring techniques
- [Uncle Bob's Clean Coder Blog](https://blog.cleancoder.com/) - Software craftsmanship articles

### Pattern Catalogs
- [Refactoring Guru - Design Patterns](https://refactoring.guru/design-patterns)
- [Martin Fowler - Enterprise Patterns](https://martinfowler.com/eaaCatalog/)

## Metadata

**Version:** 1.0.2
**Status:** Active
**Coverage:** 23 rules across 3 implemented categories (SOLID, Core Principles, Design Patterns); 4 planned
**Last Updated:** 2026-03-07

### Rule Statistics
- SOLID Principles: 10 rules
- Core Principles: 12 rules
- Design Patterns: 1 rule

