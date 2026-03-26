# Testing Best Practices v2.0.0

Unit testing, integration testing, and TDD principles for reliable, maintainable test suites.

## Overview

- Test structure (AAA pattern, descriptive names, one assertion per test)
- Test isolation (independent, deterministic, no shared state)
- Assertions (specific matchers, meaningful messages, custom matchers)
- Test data (factories, builders, faker, fixtures)
- Mocking (boundaries only, verify interactions, MSW)
- Coverage (meaningful coverage, edge cases, unhappy paths)
- Performance (fast unit tests, parallel execution, test organization)
- 34 rules across 7 categories

## Categories

### 1. Test Structure (Critical)
AAA pattern, descriptive names, one assertion, describe/it, BDD, setup/teardown.

### 2. Test Isolation (Critical)
Independent tests, no shared state, deterministic, cleanup, test doubles.

### 3. Assertions (High)
Specific assertions, meaningful messages, expected-first, custom matchers.

### 4. Test Data (High)
Factories, builders, faker, minimal data, realistic edge cases, fixtures.

### 5. Mocking (Medium)
Mock at boundaries, verify interactions, don't over-mock, realistic mocks.

### 6. Coverage (Medium)
Meaningful coverage, edge cases, error scenarios, 80% baseline not 100%.

### 7. Performance (Low)
Fast unit tests, parallel execution, organized test tiers.

## Usage

```
Write tests for this service class
Review my test code for best practices
Set up testing strategy for this project
```

## References

- [Vitest Documentation](https://vitest.dev)
- [Jest Documentation](https://jestjs.io)
- [Testing Library](https://testing-library.com)
- [MSW (Mock Service Worker)](https://mswjs.io)
