---
title: Rule Title
priority: CRITICAL|HIGH|MEDIUM|LOW
category: Test Structure|Test Isolation|Assertions|Test Data|Mocking & Test Doubles|Coverage Strategy|Test Performance
---

# Rule Title

Brief one-sentence description of the principle.

## Bad Example

```typescript
// Comment explaining why this is problematic
describe('ComponentName', () => {
  test('demonstrates antipattern', () => {
    // Code showing what NOT to do
    // Multiple aspects of the problem
    expect(something).toBe(wrong);
  });
});
```

## Good Example

```typescript
// Comment explaining the correct approach
describe('ComponentName', () => {
  // Setup if needed
  beforeEach(() => {
    // Clean setup
  });

  test('demonstrates best practice', () => {
    // Arrange
    const input = prepareTestData();

    // Act
    const result = functionUnderTest(input);

    // Assert
    expect(result).toBe(expected);
  });

  test('handles edge case correctly', () => {
    // Additional test showing the pattern
  });
});
```

## Why

1. **Benefit 1**: Explanation of why this matters
2. **Benefit 2**: Impact on test quality
3. **Benefit 3**: Developer experience improvement
4. **Benefit 4**: Maintenance and debugging advantages

Guidelines:
- Specific guideline or tip
- When to apply this pattern
- What to watch out for
- Tools or libraries that help
