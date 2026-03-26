---
title: Small, Focused Pull Requests
category: pull-request
priority: high
tags: [pull-requests, code-review, velocity, quality]
related: [pr-description-template, branch-short-lived, commit-atomic-changes]
---

# Small, Focused Pull Requests

Keep pull requests small and focused on a single concern to enable thorough reviews and reduce risk.

## Bad Example

```bash
# Massive PR with multiple unrelated changes
gh pr create --title "Updates for Q4" --body "
- Add user authentication
- Refactor database layer
- Update all dependencies
- Fix 15 bugs
- Add new dashboard
- Change color scheme
"
# 5000+ lines changed, 100+ files

# PR that mixes refactoring with features
gh pr create --title "Add search and cleanup code"
# Reviewers can't distinguish intentional changes from refactoring

# Kitchen sink PR
git diff main --stat
# 200 files changed, 15000 insertions, 8000 deletions
# "I'll just add one more thing..."
```

## Good Example

```bash
# Single feature, single PR
gh pr create --title "feat: add user search functionality" --body "
## Summary
- Add search input to user list
- Implement debounced API calls
- Display results with highlighting

## Changes
- 4 files changed
- ~200 lines
"

# Separate PRs for separate concerns
gh pr create --title "refactor: extract validation utilities"
# Merge refactoring first
gh pr create --title "feat: add email validation to signup"
# Then add feature using refactored code

# Breaking up large features
gh pr create --title "feat: add checkout API endpoints"
gh pr create --title "feat: add checkout UI components"
gh pr create --title "feat: integrate checkout UI with API"

# Each PR is reviewable in 15-30 minutes
git diff main --stat
# 8 files changed, 150 insertions, 20 deletions
```

## Why

Small PRs dramatically improve code quality and velocity:

1. **Faster Reviews**: Reviewers can focus deeply on fewer changes
2. **Better Feedback**: Detailed review comments are more likely
3. **Reduced Risk**: Smaller changes = smaller potential for bugs
4. **Easier Debugging**: If issues arise, the cause is easier to identify
5. **Quicker Merging**: Less waiting, faster feedback loops
6. **Fewer Conflicts**: Less time open = less chance of conflicts

Size guidelines:
- **Ideal**: 50-200 lines changed
- **Acceptable**: 200-400 lines
- **Large**: 400-800 lines (needs justification)
- **Too Large**: 800+ lines (should be split)

Strategies for smaller PRs:
- Separate refactoring from feature work
- Use feature flags to merge incomplete features
- Break features into vertical slices
- Submit preparatory PRs (add tests first, then implementation)
- Extract shared utilities into separate PRs

The "1-hour rule": If a PR can't be reviewed in under an hour, it's probably too big.
