---
title: Avoid Force Push on Shared Branches
category: history
priority: medium
tags: [history, force-push, safety, collaboration]
related: [history-rebase-vs-merge, branch-main-protected]
---

# Avoid Force Push on Shared Branches

Never force push to shared branches like main or develop, as it rewrites history and disrupts other developers.

## Bad Example

```bash
# Force pushing to main
git checkout main
git reset --hard HEAD~3
git push --force origin main
# Everyone's local main is now broken

# Force pushing to shared feature branch
git checkout feature/shared-work
git rebase main
git push --force origin feature/shared-work
# Colleagues' work may be lost

# Amending pushed commits on shared branch
git commit --amend -m "better message"
git push --force origin feature/team-project
# Team members can't pull cleanly

# Force pushing after failed rebase
git rebase main
# Conflicts everywhere, give up
git reset --hard ORIG_HEAD
git push --force
# But you already pushed the partial rebase
```

## Good Example

```bash
# Use --force-with-lease for personal branches
git checkout feature/my-branch
git rebase main
git push --force-with-lease origin feature/my-branch
# Fails safely if someone else pushed

# Revert instead of force push on shared branches
git checkout main
git revert abc123
git push origin main
# History preserved, change undone

# Fix mistakes with new commits, not rewrites
git commit -m "fix: correct the previous commit's error"
git push origin shared-branch
# No history rewrite

# If you must coordinate a rebase on shared branch
# 1. Notify all collaborators
# 2. Everyone commits and pushes their work
# 3. Everyone stops pushing
# 4. One person rebases and force pushes
# 5. Everyone resets to remote
git fetch origin
git reset --hard origin/feature/shared-work
```

## Why

Force pushing to shared branches causes serious problems:

1. **Lost Work**: Other developers' commits can be overwritten
2. **Broken History**: Local branches diverge from remote
3. **Sync Issues**: Developers see conflicts when pulling
4. **Confusion**: Commit SHAs change, breaking references
5. **CI/CD Issues**: Builds may reference non-existent commits

The `--force-with-lease` safety net:
```bash
# Regular force push - dangerous
git push --force
# Overwrites remote regardless of state

# Force with lease - safer
git push --force-with-lease
# Fails if remote has commits you haven't fetched
# Protects against overwriting others' work
```

Scenarios and solutions:

| Situation | Bad | Good |
|-----------|-----|------|
| Undo commit on main | `reset --hard && push --force` | `git revert` |
| Clean up commits | Rebase shared branch | Squash merge at PR |
| Fix typo in message | `--amend && --force` | New commit or squash at merge |
| Sync feature branch | Merge (creates noise) | Rebase personal branches |

Branch protection prevents force pushes:
```yaml
# GitHub branch protection
main:
  allow_force_pushes: false
  allow_deletions: false
```

Even with `--force-with-lease`, communicate with your team before force pushing to any branch others might be using.
