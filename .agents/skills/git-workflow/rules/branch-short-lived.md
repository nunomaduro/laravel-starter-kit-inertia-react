---
title: Short-Lived Branches
category: branch
priority: high
tags: [branching, merge-conflicts, feature-flags, continuous-integration]
related: [branch-feature-workflow, pr-small-focused, history-rebase-vs-merge]
---

# Short-Lived Branches

Keep feature branches short-lived to minimize merge conflicts and integration challenges.

## Bad Example

```bash
# Branch exists for months
git log --oneline feature/big-rewrite
# Shows commits spanning 4 months
# 200+ commits, massive diff from main

# Stale branch that diverged significantly
git checkout feature/old-feature
git diff main --stat
# 150 files changed, 10000 insertions, 5000 deletions

# "Development" branch that never merges
git checkout develop
# Perpetually behind main, used as dumping ground

# Long-running feature with no intermediate merges
git checkout -b feature/new-architecture
# 6 months later, impossible to merge
```

## Good Example

```bash
# Feature branch lives for days, not weeks
git checkout -b feature/add-search
# Day 1: implement basic search
# Day 2: add tests, create PR
# Day 3: address review, merge

# Break large features into smaller branches
git checkout -b feature/search-api
# Merge after API is done
git checkout -b feature/search-ui
# Merge after UI is done
git checkout -b feature/search-integration
# Merge after integration is done

# Use feature flags for incomplete features
git checkout -b feature/new-checkout
git commit -m "feat: add new checkout (behind feature flag)"
# Can merge to main even though feature isn't complete

# Regular rebasing keeps branch fresh
git checkout feature/user-profile
git fetch origin main
git rebase origin/main
# Conflicts are small and manageable
```

## Why

Short-lived branches reduce risk and improve collaboration:

1. **Fewer Conflicts**: Less time to diverge from main = fewer merge conflicts
2. **Easier Reviews**: Small PRs are reviewed faster and more thoroughly
3. **Faster Feedback**: Issues discovered earlier in smaller increments
4. **Reduced Risk**: If something goes wrong, less code to debug/revert
5. **Better Flow**: Work moves through the system continuously

Guidelines for short-lived branches:
- Target: 1-5 days
- Maximum: 2 weeks (exceptional cases)
- If longer needed: break into smaller pieces

Strategies for large features:
- **Feature Flags**: Merge incomplete features behind toggles
- **Vertical Slices**: Deliver end-to-end thin slices
- **Branch by Abstraction**: Introduce new implementation alongside old
- **Dark Launching**: Deploy without exposing to users

Warning signs of long-lived branches:
- Multiple "sync with main" merge commits
- Commit messages like "merge conflicts resolved"
- Fear of rebasing due to size
- Multiple developers afraid to touch the branch
