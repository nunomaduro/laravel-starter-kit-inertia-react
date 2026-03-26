---
title: Squash Merge Strategy
category: pull-request
priority: high
tags: [pull-requests, merge-strategy, history, changelog]
related: [history-clean-commits, history-rebase-vs-merge, commit-conventional-format]
---

# Squash Merge Strategy

Use squash merging to maintain a clean, linear history while preserving development context in PRs.

## Bad Example

```bash
# Regular merge creating noise in history
git log --oneline main
# a1b2c3d Merge pull request #123
# d4e5f6g WIP
# g7h8i9j fix typo
# j1k2l3m add feature
# m4n5o6p WIP again
# p7q8r9s forgot file
# s1t2u3v Merge branch 'main' into feature
# ...50 more commits for one feature

# Merge commits from syncing with main
git log --oneline
# Merge branch 'main' into feature/x
# Merge branch 'main' into feature/x
# Merge branch 'main' into feature/x

# Inconsistent merge strategies
# Some PRs squashed, some regular merged, some rebased
# History is unpredictable
```

## Good Example

```bash
# Squash merge via GitHub CLI
gh pr merge 123 --squash

# Squash merge via GitHub UI
# Select "Squash and merge" from dropdown

# Result: clean single commit in main
git log --oneline main
# a1b2c3d feat: add user authentication (#123)
# b2c3d4e fix: resolve payment timeout (#122)
# c3d4e5f feat: add dashboard widgets (#121)

# Squash merge with custom message
gh pr merge 123 --squash --subject "feat: add user search" --body "
Implements full-text search for users with:
- Debounced input handling
- Result highlighting
- Pagination support

Closes #100"

# Configure repo to default to squash
# Settings > General > Pull Requests
# [x] Allow squash merging
# [ ] Allow merge commits
# [ ] Allow rebase merging

# Or allow all but make squash default
# Default to pull request title for squash merge commits
```

## Why

Squash merging provides the best of both worlds:

1. **Clean History**: Main branch has one commit per feature/fix
2. **Easy Revert**: Revert entire feature with single `git revert`
3. **Clear Changelog**: Each commit represents a complete change
4. **Development Freedom**: WIP commits during development are fine
5. **Preserved Context**: Full commit history remains in the PR

Comparison of merge strategies:

| Strategy | Main History | PR History | Bisect | Revert |
|----------|-------------|------------|--------|--------|
| Merge | Noisy | Preserved | Complex | Complex |
| Squash | Clean | In PR only | Simple | Simple |
| Rebase | Clean | Lost | Simple | Per-commit |

When NOT to squash:
- Commits have different authors who need credit
- Individual commits are meaningful and should be preserved
- Repository uses conventional commits for changelog generation per-commit

Squash merge commit message format:
```
feat: add user authentication (#123)

* Add login form component
* Implement JWT token handling
* Add session persistence
* Add logout functionality

Co-authored-by: Jane Doe <jane@example.com>
```

Configure GitHub to use PR title as squash commit message for consistent conventional commits.
