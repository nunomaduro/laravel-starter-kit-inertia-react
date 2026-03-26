# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Test Structure (struct)

**Impact:** CRITICAL
**Description:** Fundamental patterns for organizing test code. AAA pattern for clear test flow, descriptive naming for test documentation, one assertion per test for precise failure messages, and proper setup/teardown for shared test context.

## 2. Test Isolation (iso)

**Impact:** CRITICAL
**Description:** Tests must run independently without shared mutable state. Independent execution, deterministic results, no order dependency, proper cleanup, and strategic use of test doubles ensure reliable test suites that can run in any order and in parallel.

## 3. Assertions (assert)

**Impact:** HIGH
**Description:** Effective assertions catch bugs and communicate intent. Specific matchers (toBe, toEqual, toContain), meaningful failure messages, consistent expected-first ordering, named constants over magic numbers, and custom matchers for domain logic.

## 4. Test Data (data)

**Impact:** HIGH
**Description:** Well-managed test data makes tests readable and maintainable. Factories for consistent object creation, builder pattern for complex objects, faker for realistic data, minimal data focused on what matters, and proper fixture management.

## 5. Mocking (mock)

**Impact:** MEDIUM
**Description:** Strategic mocking isolates code under test from external dependencies. Mock only at boundaries (APIs, databases, file system), verify important interactions, avoid over-mocking that tests implementation details, and use realistic mock behavior with tools like MSW.

## 6. Coverage (cov)

**Impact:** MEDIUM
**Description:** Coverage strategy guides testing effort. Focus on meaningful coverage of business logic and decision branches, cover edge cases and boundary values, test error scenarios and unhappy paths, and treat 80% as a baseline guide not a rigid target.

## 7. Performance (perf)

**Impact:** LOW
**Description:** Fast tests enable short feedback loops. Unit tests should run under 50ms each, parallel execution reduces total suite time, and organizing tests by speed tier (unit/integration/e2e) enables fast local feedback with comprehensive CI runs.
