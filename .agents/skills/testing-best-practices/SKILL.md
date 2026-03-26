---
name: testing-best-practices
description: Unit testing, integration testing, and test-driven development principles. Use when writing tests, reviewing test code, improving test coverage, or setting up testing strategy. Triggers on "write tests", "review tests", "testing best practices", or "TDD".
license: MIT
metadata:
  author: agent-skills
  version: "2.0.0"
---

# Testing Best Practices

Unit testing, integration testing, and TDD principles for reliable, maintainable test suites. Contains 34 rules across 7 categories using TypeScript with Jest/Vitest.

## Metadata

- **Version:** 2.0.0
- **Rule Count:** 34 rules across 7 categories
- **License:** MIT

## When to Apply

Reference these guidelines when:
- Writing unit or integration tests
- Reviewing test code quality
- Improving test coverage strategy
- Setting up testing infrastructure
- Debugging flaky or slow tests

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Test Structure | CRITICAL | `struct-` |
| 2 | Test Isolation | CRITICAL | `iso-` |
| 3 | Assertions | HIGH | `assert-` |
| 4 | Test Data | HIGH | `data-` |
| 5 | Mocking | MEDIUM | `mock-` |
| 6 | Coverage | MEDIUM | `cov-` |
| 7 | Performance | LOW | `perf-` |

## Quick Reference

### 1. Test Structure (CRITICAL)

- `struct-aaa-pattern` - Arrange, Act, Assert pattern
- `struct-descriptive-names` - Descriptive test names
- `struct-one-assertion` - One logical assertion per test
- `struct-describe-it` - Organized test suites with describe/it
- `struct-given-when-then` - BDD style when appropriate
- `struct-setup-teardown` - Proper setup and teardown

### 2. Test Isolation (CRITICAL)

- `iso-independent-tests` - Tests run independently
- `iso-no-shared-state` - No shared mutable state
- `iso-deterministic` - Same result every run
- `iso-no-order-dependency` - Run in any order
- `iso-cleanup` - Clean up after tests
- `iso-test-doubles` - Strategic use of test doubles

### 3. Assertions (HIGH)

- `assert-specific` - Use specific assertions
- `assert-meaningful-messages` - Helpful failure messages
- `assert-expected-actual` - Expected value first
- `assert-no-magic-numbers` - Use named constants
- `assert-custom-matchers` - Custom matchers for domain logic

### 4. Test Data (HIGH)

- `data-factories` - Use factories for test data
- `data-builders` - Builder pattern for complex objects
- `data-faker` - Generate realistic fake data
- `data-minimal` - Minimal test data
- `data-realistic` - Realistic edge cases
- `data-fixtures` - Manage test fixtures

### 5. Mocking (MEDIUM)

- `mock-boundaries` - Mock only at boundaries
- `mock-verify-interactions` - Verify important mock calls
- `mock-minimal` - Don't over-mock
- `mock-realistic` - Realistic mock behavior with MSW

### 6. Coverage (MEDIUM)

- `cov-meaningful` - Focus on meaningful coverage
- `cov-edge-cases` - Cover edge cases and boundary values
- `cov-unhappy-paths` - Test error scenarios
- `cov-not-100-percent` - 100% coverage isn't the goal

### 7. Performance (LOW)

- `perf-fast-unit` - Keep unit tests fast (<50ms each)
- `perf-parallel` - Run tests in parallel
- `perf-test-organization` - Organize tests for fast feedback

## Essential Guidelines

### AAA Pattern (Arrange, Act, Assert)

```typescript
it('calculates total with discount', () => {
  // Arrange
  const cart = new ShoppingCart();
  cart.addItem({ name: 'Book', price: 20 });
  cart.applyDiscount(0.1);

  // Act
  const total = cart.getTotal();

  // Assert
  expect(total).toBe(18);
});
```

### Descriptive Test Names

```typescript
// ✅ Describes behavior and scenario
describe('UserService.register', () => {
  it('creates user with hashed password', () => {});
  it('throws ValidationError when email is invalid', () => {});
  it('sends welcome email after successful registration', () => {});
});
```

### Test Isolation

```typescript
// ✅ Each test sets up its own data
beforeEach(() => {
  mockRepository = { save: vi.fn(), find: vi.fn() };
  service = new OrderService(mockRepository);
});
```

## How to Use

Read individual rule files for detailed explanations:

```
rules/struct-aaa-pattern.md
rules/iso-independent-tests.md
rules/mock-boundaries.md
rules/cov-meaningful.md
```

## References

- [Vitest Documentation](https://vitest.dev)
- [Jest Documentation](https://jestjs.io)
- [Testing Library](https://testing-library.com)
- [MSW (Mock Service Worker)](https://mswjs.io)

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
