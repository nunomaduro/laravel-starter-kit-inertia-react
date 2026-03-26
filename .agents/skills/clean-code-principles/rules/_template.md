---
id: {prefix}-{concept}-{specificity}
title: {Full Descriptive Title}
category: {solid-principles|core-principles|design-patterns|code-organization|naming-readability|functions-methods|comments-documentation}
priority: {critical|high|medium|low}
tags: [{tag1}, {tag2}, {tag3}, {tag4}]
related: [{rule-id-1}, {rule-id-2}, {rule-id-3}]
---

# {Rule Title}

{One or two sentence summary explaining the principle and why it matters. Should be clear and actionable.}

## Bad Example

```typescript
// Anti-pattern: {Brief description of what's wrong}

{Code example demonstrating the violation}

// Problems:
// 1. {Specific issue 1}
// 2. {Specific issue 2}
// 3. {Specific issue 3}
```

**Why This Is Wrong:**
- {Consequence 1}
- {Consequence 2}
- {Consequence 3}

## Good Example

```typescript
// Correct approach: {Brief description of the solution}

{Code example demonstrating proper implementation}

// Benefits:
// 1. {Benefit 1}
// 2. {Benefit 2}
// 3. {Benefit 3}
```

**Alternative Approach (Optional):**

```typescript
// Another valid solution: {When this might be preferred}

{Alternative code example if applicable}
```

## Why

Explanation of the principle and its benefits:

1. **{Benefit Category 1}**: {Detailed explanation}

2. **{Benefit Category 2}**: {Detailed explanation}

3. **{Benefit Category 3}**: {Detailed explanation}

4. **{Benefit Category 4}**: {Detailed explanation}

5. **{Benefit Category 5}**: {Detailed explanation}

6. **{Benefit Category 6}**: {Detailed explanation}

7. **{Benefit Category 7}**: {Detailed explanation}

## When to Apply

- {Situation 1}
- {Situation 2}
- {Situation 3}
- {Situation 4}

## When NOT to Apply (Optional)

```typescript
// Acceptable exception: {Scenario where the rule can be relaxed}

{Code example of acceptable violation with clear reasoning}

// This is acceptable because:
// - {Reason 1}
// - {Reason 2}
```

## Common Mistakes (Optional)

### Mistake 1: {Common misunderstanding}

```typescript
// ❌ Wrong
{Code showing mistake}

// ✅ Correct
{Code showing correction}
```

### Mistake 2: {Another common issue}

```typescript
// ❌ Wrong
{Code showing mistake}

// ✅ Correct
{Code showing correction}
```

## Testing Implications (Optional)

How this principle affects testing:

```typescript
// Test example showing improved testability
{Test code demonstrating benefits}
```

## Real-World Example (Optional)

{Brief description of how this applies in production scenarios}

```typescript
// Production scenario: {Description}
{Realistic code example}
```

## Related Principles

- **{Related Rule 1}**: {Brief explanation of relationship}
- **{Related Rule 2}**: {Brief explanation of relationship}
- **{Related Rule 3}**: {Brief explanation of relationship}

## Further Reading (Optional)

- {Resource title} - {URL or reference}
- {Resource title} - {URL or reference}

## Language-Specific Notes (Optional)

### TypeScript/JavaScript
{Language-specific considerations}

### Python
{Language-specific considerations}

### Java
{Language-specific considerations}

### Go
{Language-specific considerations}

---

## Template Guidelines

### Frontmatter

- **id**: Use format `{prefix}-{concept}-{specificity}`. Must be unique and match filename.
- **title**: Full descriptive title, human-readable
- **category**: One of the 7 defined categories
- **priority**: critical (SOLID, Core) | high (Patterns, Org) | medium (Naming, Functions) | low (Comments)
- **tags**: 3-5 relevant tags for searchability
- **related**: 2-4 related rule IDs that commonly apply together

### Content Structure

1. **Title & Summary**: Clear, one-sentence explanation
2. **Bad Example**: Show the anti-pattern with clear problems listed
3. **Good Example**: Show proper implementation with benefits
4. **Why**: 5-7 benefits explaining the value
5. **When to Apply**: Practical scenarios
6. **Optional Sections**: Add as needed for complex rules

### Code Examples

- Use TypeScript for primary examples (language-agnostic)
- Keep examples focused and minimal
- Show realistic scenarios, not toy examples
- Include comments explaining key points
- Use ❌ for bad examples, ✅ for good examples

### Writing Style

- Be direct and actionable
- Focus on "why" not just "what"
- Use active voice
- Keep explanations concise
- Provide context for decisions
- Assume intermediate developer knowledge

### Length Guidelines

- Minimum: 200 lines (simple rules)
- Target: 300-400 lines (most rules)
- Maximum: 600 lines (complex patterns)

### Quality Checklist

- [ ] Frontmatter complete and accurate
- [ ] Clear bad example with explained problems
- [ ] Clear good example with explained benefits
- [ ] At least 5 benefits in "Why" section
- [ ] Practical "When to Apply" scenarios
- [ ] Related rules referenced
- [ ] Code examples are realistic
- [ ] Comments explain key concepts
- [ ] Language-agnostic where possible
- [ ] Proofread for clarity and typos
