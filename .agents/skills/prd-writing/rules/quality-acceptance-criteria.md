---
title: Write Testable Acceptance Criteria
impact: MEDIUM
impactDescription: "Defines 'done' in a way that QA can verify"
tags: quality, acceptance-criteria, testing, definition-of-done
---

## Write Testable Acceptance Criteria

**Impact: MEDIUM (Defines 'done' in a way that QA can verify)**

Every requirement and user story needs acceptance criteria that are binary (pass/fail), specific, and testable. Use checklist format for simple criteria and Given/When/Then for behavioral scenarios.

## Incorrect

```markdown
<!-- Bad: vague, untestable criteria -->
## Acceptance Criteria
- The feature works correctly
- Users can do what they need to do
- No major bugs
- Good performance
- Looks nice on mobile
```

**Problems:**
- "Works correctly" — according to whom?
- "What they need to do" — what specifically?
- "No major bugs" — what's major vs minor?
- "Good performance" — see metric-no-vague-language rule
- QA cannot write test cases from this

## Correct

````markdown
<!-- Good: testable with checklist format -->
## Acceptance Criteria — Short URL Creation

### Checklist Format
- [ ] User enters a valid URL and title, clicks "Create"
- [ ] System generates a 6-char alphanumeric slug
- [ ] New short URL appears in the user's list immediately
- [ ] Copy button copies the full short URL to clipboard
- [ ] Invalid URL (not http/https) shows inline error "Please enter a valid URL"
- [ ] URL exceeding 2048 chars shows "URL is too long (max 2048 characters)"
- [ ] Empty title shows "Title is required"

### Given/When/Then Format (for complex scenarios)

**Scenario: Creating a link with a custom slug**
```
Given I am on the short URL detail page
When I enter custom slug "my-link" (with hyphen)
Then I see validation error "Slug must be alphanumeric only"

Given I am on the short URL detail page
When I enter custom slug "mylink" (valid, 6 chars)
And the slug "mylink" is not taken
Then a new generated link is created with slug "mylink"

Given I am on the short URL detail page
When I enter custom slug "mylink" (already taken)
Then I see validation error "This slug is already in use"
```

**Scenario: Accessing an expired link**
```
Given a generated link with slug "abc123" expired yesterday
When a visitor navigates to /abc123
Then they see the "Link Expired" page
And no click is recorded
```
````

**Benefits:**
- QA converts each criterion directly into a test case
- Developer knows exactly what to implement
- Product can verify completion with a checklist
- Edge cases are caught before development, not during

## Why

1. **Eliminates ambiguity**: Pass/fail criteria leave no room for interpretation
2. **Enables test automation**: Given/When/Then maps to BDD test frameworks
3. **Speeds up review**: PR reviewer checks criteria instead of guessing intent
4. **Prevents regressions**: Criteria become permanent test cases

Reference: [Writing Great Specifications - Kamil Nicieja](https://www.manning.com/books/writing-great-specifications)
