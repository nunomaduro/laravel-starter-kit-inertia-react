---
title: Git Worktree
category: history
priority: medium
tags: [worktree, workflow, multitasking, branches]
related: [branch-feature-workflow, history-rebase-vs-merge, branch-short-lived]
---

# Git Worktree

Use `git worktree` to work on multiple branches simultaneously without stashing or switching branches.

## Bad Example

```bash
# Interrupt current work to switch branches
git stash
git checkout main
git pull
git checkout -b hotfix/critical-bug
# fix the bug...
git commit -m "fix: resolve critical auth bug"
git checkout feature/dashboard
git stash pop
# Hope the stash applies cleanly

# Clone the repo again just to work on another branch
git clone https://github.com/org/repo.git repo-hotfix
cd repo-hotfix
git checkout -b hotfix/payment-bug
# Now maintaining two full clones with duplicated node_modules
```

## Good Example

```bash
# Add a worktree for a hotfix without leaving your current branch
git worktree add ../repo-hotfix hotfix/critical-bug

# Work in the new worktree
cd ../repo-hotfix
# fix the bug, commit, push...
git commit -m "fix(auth): resolve token validation bypass"
git push origin hotfix/critical-bug

# Return to your original work — nothing was interrupted
cd ../repo
# Your feature branch is exactly where you left it

# List all worktrees
git worktree list
# /Users/dev/repo          abc1234 [feature/dashboard]
# /Users/dev/repo-hotfix   def5678 [hotfix/critical-bug]

# Remove worktree when done
git worktree remove ../repo-hotfix
```

```bash
# Common worktree workflows

# Review a PR without leaving your branch
git worktree add ../repo-review origin/pr-branch-name
cd ../repo-review
# Run the app, review changes...
git worktree remove ../repo-review

# Work on two features simultaneously
git worktree add ../repo-feature-b feature/analytics
# Main repo: working on feature/dashboard
# ../repo-feature-b: working on feature/analytics
# Shared .git directory — no duplicate history

# Create new branch directly in a new worktree
git worktree add -b feature/new-feature ../repo-new-feature main
```

```bash
# Worktree with separate dependencies (Node.js)
git worktree add ../repo-v2 feature/v2-upgrade
cd ../repo-v2
npm install  # Install deps for this branch separately
# Each worktree has its own node_modules
```

## Why

`git worktree` solves the context-switching problem without the downsides of stashing or cloning:

1. **No stash risk**: Stash pops can create conflicts. Worktrees keep each branch's state completely separate and intact.

2. **Shared `.git`**: Unlike a second clone, worktrees share the same git history — no duplicate `.git` directory, no need to `git fetch` in multiple places.

3. **Parallel work**: Respond to urgent hotfixes, review PRs, or work on two features simultaneously — all without interrupting each other.

4. **Clean state**: Each worktree has its own working directory and index. Changes in one worktree are completely invisible to others.

| Method | Shared .git | Interrupts work | Risk |
|--------|-------------|-----------------|------|
| `git stash` | ✅ | ✅ | Stash conflicts |
| Second clone | ❌ | ❌ | Disk space, stale history |
| `git worktree` | ✅ | ❌ | None |

**Limitation:** The same branch cannot be checked out in two worktrees simultaneously. Each worktree must be on a different branch.
