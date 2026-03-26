---
title: Rebase vs. Merge
category: history
priority: medium
tags: [history, rebase, merge, workflow]
related: [history-no-force-push, branch-feature-workflow, pr-squash-merge]
---

# Rebase vs. Merge

Understand when to use rebase versus merge to maintain a clean and useful git history.

## Bad Example

```bash
# Merging main into feature branch repeatedly
git checkout feature/login
git merge main  # Creates merge commit
# more work
git merge main  # Another merge commit
# more work
git merge main  # Yet another merge commit
# History now has 10 merge commits

# Rebasing shared branches
git checkout main
git rebase feature/experimental
# Rewrites main history, breaks everyone's local copies

# Rebasing after pushing without force
git rebase main
git push  # Fails: "Updates were rejected"
git push --force  # Overwrites colleagues' work

# Inconsistent approach causing confusion
# Sometimes rebase, sometimes merge, no clear pattern
```

## Good Example

```bash
# Rebase feature branch onto main (preferred for updating)
git checkout feature/login
git fetch origin main
git rebase origin/main
# Clean, linear history within the feature branch

# Interactive rebase to clean up before PR
git rebase -i HEAD~5
# Squash WIP commits, reorder, improve messages

# Merge for bringing feature into main (via PR)
git checkout main
gh pr merge --squash  # Squash merge is recommended

# Rebase workflow for feature branches
git checkout feature/dashboard
git fetch origin
git rebase origin/main
# Resolve any conflicts
git push --force-with-lease  # Safe force push for YOUR branch

# Use merge for shared long-lived branches (if using GitFlow)
git checkout develop
git merge --no-ff release/v1.0
# Preserves release branch history
```

## Why

Understanding when to use each strategy is crucial:

**Use Rebase when:**
- Updating your feature branch with latest main
- Cleaning up commits before creating PR
- Working on your own branch that hasn't been shared
- You want a linear history

**Use Merge when:**
- Bringing completed features into main (via PR)
- Working with shared branches others depend on
- You need to preserve the exact history
- Combining long-lived branches (GitFlow)

Comparison:

| Aspect | Rebase | Merge |
|--------|--------|-------|
| History | Linear, clean | Branching, complete |
| Commit SHAs | Changed | Preserved |
| Safe for shared branches | No | Yes |
| Conflict resolution | Per-commit | Once |
| Traceability | Simplified | Complete |

Golden rules:
1. **Never rebase shared branches** (main, develop)
2. **Always rebase your own feature branches** to update them
3. **Use `--force-with-lease`** instead of `--force` when pushing rebased branches
4. **Squash merge to main** for clean history

```bash
# Safe force push (fails if remote has changes you haven't seen)
git push --force-with-lease

# Dangerous force push (avoid)
git push --force
```
