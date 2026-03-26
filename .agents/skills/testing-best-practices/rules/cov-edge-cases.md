---
title: Cover Edge Cases
impact: MEDIUM
impactDescription: "catches 35% of production bugs caused by unexpected input values"
tags: coverage, edge-cases, boundary-values
---

## Cover Edge Cases

**Impact: MEDIUM (catches 35% of production bugs caused by unexpected input values)**

Test boundary values, empty inputs, null/undefined, and special characters. Most production bugs live at the edges, not in the happy path.

## Incorrect

```typescript
// ❌ Bad: only testing the happy path
describe('StringUtils', () => {
  test('truncates a long string', () => {
    expect(truncate('Hello World', 5)).toBe('He...');
  });
});

describe('ArrayUtils', () => {
  test('finds the maximum value', () => {
    expect(findMax([3, 1, 4, 1, 5])).toBe(5);
  });
});

describe('UserValidator', () => {
  test('validates a correct email', () => {
    expect(validateUser({ name: 'Alice', email: 'alice@example.com', age: 25 })).toBe(true);
  });
});
```

**Problems:**
- What happens when `truncate` receives an empty string? A string shorter than the limit?
- What does `findMax` return for an empty array? An array with one element? Negative numbers?
- What if `age` is 0, -1, or `Number.MAX_SAFE_INTEGER`?
- Production crashes from unhandled edge cases that happy-path tests never exercise

## Correct

```typescript
// ✅ Good: systematically test edge cases and boundary values
describe('StringUtils - truncate', () => {
  test('truncates string longer than limit', () => {
    expect(truncate('Hello World', 5)).toBe('He...');
  });

  test('returns original string when shorter than limit', () => {
    expect(truncate('Hi', 10)).toBe('Hi');
  });

  test('returns original string when exactly at limit', () => {
    expect(truncate('Hello', 5)).toBe('Hello');
  });

  test('handles empty string', () => {
    expect(truncate('', 5)).toBe('');
  });

  test('handles limit of zero', () => {
    expect(truncate('Hello', 0)).toBe('...');
  });

  test('handles special characters and unicode', () => {
    expect(truncate('Hello! @#$%', 7)).toBe('Hell...');
  });

  test('handles null input gracefully', () => {
    expect(truncate(null as unknown as string, 5)).toBe('');
  });
});

describe('ArrayUtils - findMax', () => {
  test('finds max in a normal array', () => {
    expect(findMax([3, 1, 4, 1, 5])).toBe(5);
  });

  test('handles single-element array', () => {
    expect(findMax([42])).toBe(42);
  });

  test('throws for empty array', () => {
    expect(() => findMax([])).toThrow('Array must not be empty');
  });

  test('handles negative numbers', () => {
    expect(findMax([-5, -1, -3])).toBe(-1);
  });

  test('handles zero', () => {
    expect(findMax([0, -1, -2])).toBe(0);
  });

  test('handles duplicate max values', () => {
    expect(findMax([5, 5, 3, 5])).toBe(5);
  });

  test('handles Number.MAX_SAFE_INTEGER', () => {
    expect(findMax([1, Number.MAX_SAFE_INTEGER, 3])).toBe(Number.MAX_SAFE_INTEGER);
  });
});

describe('UserValidator - edge cases', () => {
  test('rejects empty name', () => {
    expect(validateUser({ name: '', email: 'a@b.com', age: 25 })).toBe(false);
  });

  test('rejects undefined email', () => {
    expect(validateUser({ name: 'Alice', email: undefined as unknown as string, age: 25 })).toBe(false);
  });

  test('rejects age of zero', () => {
    expect(validateUser({ name: 'Alice', email: 'a@b.com', age: 0 })).toBe(false);
  });

  test('rejects negative age', () => {
    expect(validateUser({ name: 'Alice', email: 'a@b.com', age: -1 })).toBe(false);
  });

  test('handles name with special characters', () => {
    expect(validateUser({ name: "O'Brien-Smith", email: 'a@b.com', age: 30 })).toBe(true);
  });

  test('rejects age exceeding reasonable maximum', () => {
    expect(validateUser({ name: 'Alice', email: 'a@b.com', age: 200 })).toBe(false);
  });
});
```

**Benefits:**
- Boundary values (0, -1, empty, max) catch off-by-one errors and unguarded code paths
- Null/undefined tests verify defensive coding works in practice
- Special character tests prevent encoding and parsing bugs
- Each edge case documents an assumption the code makes about its inputs

Reference: [Boundary Value Analysis](https://en.wikipedia.org/wiki/Boundary-value_analysis)
