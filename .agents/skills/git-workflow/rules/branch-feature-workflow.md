---
title: Feature Branch Workflow
category: branch
priority: high
tags: [branching, workflow, feature-branches, isolation]
related: [branch-naming-convention, branch-short-lived, pr-small-focused]
---

# Feature Branch Workflow

Develop new features in dedicated branches, keeping main stable and deployable at all times.

## Bad Example

```bash
# Working directly on main
git checkout main
# make changes directly
git commit -m "add new feature"
git push origin main

# Creating feature branch from outdated main
git checkout -b feature/new-widget
# (main has moved ahead by 50 commits)
# Now merge conflicts are inevitable

# Long-running feature branch without sync
git checkout -b feature/big-refactor
# work for 3 months without rebasing
# massive merge conflicts when done

# Multiple features in one branch
git checkout -b my-work
git commit -m "add feature A"
git commit -m "add feature B"
git commit -m "fix bug C"
```

## Good Example

```bash
# Start from updated main
git checkout main
git pull origin main
git checkout -b feature/user-dashboard

# Make focused commits
git commit -m "feat: add dashboard layout component"
git commit -m "feat: add user stats widget"
git commit -m "test: add dashboard component tests"

# Regularly sync with main
git fetch origin main
git rebase origin/main
# or
git merge origin/main

# Push feature branch for backup and collaboration
git push -u origin feature/user-dashboard

# Create PR when ready
gh pr create --base main --head feature/user-dashboard

# After PR merged, clean up
git checkout main
git pull origin main
git branch -d feature/user-dashboard
git push origin --delete feature/user-dashboard
```

## Why

Feature branch workflow is essential for team collaboration:

1. **Isolated Development**: Work on features without affecting others or main
2. **Code Review**: Changes are reviewed via PR before merging
3. **CI/CD Integration**: Run tests on feature branches before merging
4. **Easy Rollback**: If a feature causes issues, revert the single merge commit
5. **Parallel Work**: Multiple features can be developed simultaneously
6. **Clean History**: Main branch contains only reviewed, tested code

Workflow steps:
1. Create branch from latest main
2. Develop and commit changes
3. Push branch to remote
4. Open pull request
5. Address review feedback
6. Merge when approved and CI passes
7. Delete feature branch

Best practices:
- One feature per branch
- Keep branches short-lived (days, not months)
- Sync with main regularly to minimize conflicts
- Push frequently for backup and visibility
- Delete branches after merging
