---
title: Delete Merged Branches
category: branch
priority: high
tags: [branching, cleanup, maintenance, automation]
related: [branch-short-lived, branch-feature-workflow]
---

# Delete Merged Branches

Clean up branches after they've been merged to keep the repository tidy and navigable.

## Bad Example

```bash
# Accumulating merged branches
git branch -a
# feature/user-login (merged 6 months ago)
# feature/payment-v1 (merged 1 year ago)
# feature/old-dashboard (merged 2 years ago)
# fix/ancient-bug (merged 18 months ago)
# ... 200 more stale branches

# Keeping "just in case"
git checkout -b feature/backup-of-merged-branch
# The history is already in main!

# Never cleaning up remotes
git fetch
# Fetching hundreds of stale remote branches

# Reusing merged branch names
git checkout feature/login  # old merged branch
git commit -m "new changes"
# Confusing history, unclear what belongs to which PR
```

## Good Example

```bash
# Delete local branch after merge
git checkout main
git pull origin main
git branch -d feature/user-auth
# -d is safe, won't delete unmerged branches

# Delete remote branch after merge
git push origin --delete feature/user-auth

# Combined cleanup after merge
git checkout main
git pull origin main
git branch -d feature/user-auth
git push origin --delete feature/user-auth

# Clean up all merged local branches
git checkout main
git branch --merged | grep -v "main\|master" | xargs git branch -d

# Clean up stale remote tracking branches
git fetch --prune
# or
git remote prune origin

# Enable auto-delete on GitHub
# Settings > General > Automatically delete head branches

# Periodic cleanup script
git checkout main
git pull --prune
git branch --merged | grep -v "main\|master\|develop" | xargs git branch -d
# Note: xargs -r (skip if empty) is Linux/GNU only — omit on macOS
```

## Why

Cleaning up merged branches maintains repository health:

1. **Clarity**: Easy to see active work vs. completed work
2. **Performance**: Fewer branches means faster git operations
3. **Navigation**: Finding the right branch is easier
4. **Reduced Confusion**: No ambiguity about branch status
5. **Professionalism**: Clean repository reflects well on the team

When to delete:
- Immediately after PR is merged
- During regular repository maintenance
- As part of sprint cleanup

Safe deletion commands:
- `git branch -d` - Only deletes if fully merged
- `git branch -D` - Force delete (use cautiously)
- `git push origin --delete` - Delete remote branch

Automation options:
- GitHub: Enable "Automatically delete head branches" in repo settings
- GitLab: Enable "Delete source branch when merge request is accepted"
- CI/CD: Add cleanup step to merge pipelines

Exceptions (branches to keep):
- `main` / `master`
- `develop` (if using GitFlow)
- `release/*` branches (until end of support)
- Protected environment branches
