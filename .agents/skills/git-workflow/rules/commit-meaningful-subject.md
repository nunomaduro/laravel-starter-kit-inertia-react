---
title: Meaningful Commit Subject Lines
category: commit
priority: critical
tags: [commits, subject-line, clarity, documentation]
related: [commit-conventional-format, commit-imperative-mood, commit-body-context]
---

# Meaningful Commit Subject Lines

Write clear, descriptive subject lines that explain WHAT changed and WHY it matters.

## Bad Example

```bash
# Too vague
git commit -m "fix bug"
git commit -m "update code"
git commit -m "changes"
git commit -m "WIP"

# Too long (hard to scan in logs)
git commit -m "fix the bug where users could not log in when they had special characters in their password because the validation regex was incorrect"

# Implementation details instead of purpose
git commit -m "change line 42 in auth.js"
git commit -m "add if statement to check null"

# Meaningless references
git commit -m "fix issue"
git commit -m "address review comments"
git commit -m "final fix"
git commit -m "fix fix"
```

## Good Example

```bash
# Clear, specific, and concise (50 chars or less ideal)
git commit -m "fix: allow special characters in passwords"

# Explains the what and implies the why
git commit -m "feat: add rate limiting to prevent API abuse"

# Specific about the affected area
git commit -m "fix(auth): handle expired JWT tokens gracefully"

# Descriptive but concise
git commit -m "perf: lazy load dashboard widgets"

# When more context needed, use body
git commit -m "fix: prevent duplicate form submissions

Users reported seeing duplicate orders when clicking
the submit button multiple times. Add debounce and
disable button after first click.

Fixes #567"
```

## Why

Good subject lines are crucial for maintainability:

1. **Scannable History**: `git log --oneline` becomes a useful changelog
2. **Quick Understanding**: Team members can understand changes without reading code
3. **Efficient Debugging**: When using `git bisect`, meaningful messages help identify problematic commits
4. **Documentation**: Commit history serves as documentation of the project's evolution
5. **Better Tooling**: GitHub, GitLab, and other tools display subject lines in many places

Guidelines for great subject lines:
- Keep under 50 characters when possible (hard limit: 72)
- Capitalize the first letter (after type prefix if using conventional commits)
- No period at the end
- Focus on "what" and "why", not "how"
- Be specific enough to distinguish from similar commits
