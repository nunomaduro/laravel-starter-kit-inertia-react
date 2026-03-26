---
title: Atomic Commits
category: commit
priority: critical
tags: [commits, atomic, best-practices, revert]
related: [commit-meaningful-subject, history-clean-commits]
---

# Atomic Commits

Each commit should represent a single, complete, logical change that can be understood and reverted independently.

## Bad Example

```bash
# Multiple unrelated changes in one commit
git add .
git commit -m "fix login bug, add new dashboard feature, update styles, fix typos"

# Partial implementation that breaks the build
git commit -m "start working on user profile"
# (code doesn't compile or tests fail)

# Mixing refactoring with feature changes
git commit -m "add payment processing and refactor entire codebase"
```

## Good Example

```bash
# Single focused change
git commit -m "fix: prevent null pointer in login validation"

# Complete feature in one commit (if small enough)
git commit -m "feat: add password strength indicator to signup form"

# Separate commits for separate concerns
git commit -m "refactor: extract validation logic to separate module"
git commit -m "feat: add email format validation"
git commit -m "test: add unit tests for email validation"

# Each commit leaves the codebase in a working state
git commit -m "feat: add user profile API endpoint"
git commit -m "feat: add user profile UI component"
git commit -m "feat: connect profile UI to API"
```

## Why

Atomic commits provide critical benefits:

1. **Easy Reversion**: If a change introduces a bug, you can revert just that commit without losing other work
2. **Clear History**: Each commit tells a complete story about what changed and why
3. **Simplified Code Review**: Reviewers can understand changes one logical unit at a time
4. **Bisect Friendly**: `git bisect` works effectively when each commit is a complete, working state
5. **Cherry-Pick Ready**: Individual changes can be applied to other branches without bringing unrelated code
6. **Reduced Conflicts**: Smaller, focused changes are less likely to conflict with others' work

Rule of thumb: If you need "and" in your commit message to describe what you did, consider splitting it into multiple commits.
