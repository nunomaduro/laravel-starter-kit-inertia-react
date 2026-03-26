---
title: Describe-It Block Structure
impact: CRITICAL
impactDescription: "test organization and readability"
tags: test-structure, describe, it, grouping
---

## Describe-It Block Structure

**Impact: CRITICAL (test organization and readability)**

Organize tests using nested describe blocks and it/test functions for clear hierarchy and context. The hierarchy should mirror the structure of what you are testing: class > method > scenario.

## Incorrect

```typescript
// ❌ Bad: Flat structure without organization
test('calculator add positive numbers', () => {
  expect(calculator.add(2, 3)).toBe(5);
});

test('calculator add negative numbers', () => {
  expect(calculator.add(-2, -3)).toBe(-5);
});

test('calculator subtract', () => {
  expect(calculator.subtract(5, 3)).toBe(2);
});

test('calculator divide', () => {
  expect(calculator.divide(10, 2)).toBe(5);
});

test('calculator divide by zero', () => {
  expect(() => calculator.divide(10, 0)).toThrow();
});

test('calculator multiply', () => {
  expect(calculator.multiply(3, 4)).toBe(12);
});
```

**Problems:**
- No logical grouping of related tests
- No shared setup via `beforeEach`/`afterEach`
- Flat test output makes it hard to scan results
- Test names are long because they must include full context

## Correct

```typescript
// ✅ Good: Well-organized hierarchical structure
describe('Calculator', () => {
  let calculator: Calculator;

  beforeEach(() => {
    calculator = new Calculator();
  });

  describe('add', () => {
    it('returns sum of two positive numbers', () => {
      expect(calculator.add(2, 3)).toBe(5);
    });

    it('returns sum of two negative numbers', () => {
      expect(calculator.add(-2, -3)).toBe(-5);
    });

    it('returns sum of positive and negative numbers', () => {
      expect(calculator.add(5, -3)).toBe(2);
    });
  });

  describe('subtract', () => {
    it('returns difference between two numbers', () => {
      expect(calculator.subtract(5, 3)).toBe(2);
    });

    it('returns negative when subtracting larger from smaller', () => {
      expect(calculator.subtract(3, 5)).toBe(-2);
    });
  });

  describe('divide', () => {
    it('returns quotient of two numbers', () => {
      expect(calculator.divide(10, 2)).toBe(5);
    });

    it('returns decimal for non-even division', () => {
      expect(calculator.divide(5, 2)).toBe(2.5);
    });

    it('throws DivisionByZeroError when dividing by zero', () => {
      expect(() => calculator.divide(10, 0)).toThrow(DivisionByZeroError);
    });
  });

  describe('multiply', () => {
    it('returns product of two numbers', () => {
      expect(calculator.multiply(3, 4)).toBe(12);
    });

    it('returns zero when multiplying by zero', () => {
      expect(calculator.multiply(5, 0)).toBe(0);
    });
  });
});
```

**Benefits:**
- Related tests are logically grouped under meaningful contexts
- Shared setup is scoped to specific groups via `beforeEach`/`afterEach`
- Test runner displays results in a clear hierarchy
- Shorter test names because context is provided by describe blocks
- Easier navigation and filtering by describe block names

Reference: [Jest Docs — Organizing Tests](https://jestjs.io/docs/api#describename-fn)
