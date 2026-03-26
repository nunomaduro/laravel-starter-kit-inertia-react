---
title: Workflow Strategies
category: branch
priority: high
tags: [branching, gitflow, github-flow, trunk-based, workflow]
related: [branch-feature-workflow, branch-release-strategy, branch-main-protected]
---

# Workflow Strategies

Choose the right branching strategy for your team — GitFlow, GitHub Flow, or Trunk-Based Development.

## Bad Example

```bash
# No defined strategy — everyone does their own thing
git checkout -b johns-stuff
git checkout -b new-feature-v2-final
git checkout -b HOTFIX_URGENT
# Inconsistent, uncoordinated, leads to merge chaos

# Using GitFlow for a small team with continuous deployment
# Overhead of develop, release, hotfix branches adds complexity
# with no real benefit for a 2-person team deploying daily
```

## Good Example

### GitHub Flow — Simple, continuous deployment

Best for: Small teams, SaaS products, frequent releases

```bash
# Only two concepts: main + feature branches
git checkout -b feature/user-notifications
# work, commit, push...
gh pr create --title "feat: add user notifications"
# Review → CI passes → Squash merge to main → Deploy
git branch -d feature/user-notifications
```

```
main ──●──────────────────●── (always deployable)
        \                /
         feature/xyz ──●
```

### GitFlow — Structured release cycles

Best for: Mobile apps, versioned software, scheduled releases

```bash
# Long-lived branches
main       # Production releases only
develop    # Integration branch

# Supporting branches
feature/*  # New features → develop
release/*  # Release prep → main + develop
hotfix/*   # Production fixes → main + develop

# Create a feature
git checkout -b feature/payment develop
# Complete feature
git merge --no-ff feature/payment develop
git branch -d feature/payment

# Prepare release
git checkout -b release/v1.2.0 develop
# Bug fixes only...
git merge --no-ff release/v1.2.0 main
git tag -a v1.2.0
git merge --no-ff release/v1.2.0 develop
```

### Trunk-Based Development — Maximum CI/CD velocity

Best for: Large teams, microservices, high deployment frequency

```bash
# Everyone commits to main (trunk) daily
# Feature flags control what's visible to users

git checkout main
git pull
# Make small change
git commit -m "feat(search): add index for user queries behind flag"
git push origin main
# CI runs → Deploy immediately

# Short-lived feature branches (max 1-2 days)
git checkout -b feature/search-v2
# Complete in hours, not days
gh pr create  # Small PR, fast review
# Merge same day
```

## Why

Choosing the right strategy prevents coordination problems:

| Strategy | Team Size | Release Cadence | Complexity |
|----------|-----------|-----------------|------------|
| GitHub Flow | 1–10 | Continuous | Low |
| GitFlow | Any | Scheduled (weekly/monthly) | High |
| Trunk-Based | 10+ | Multiple times/day | Medium |

**Decision guide:**
- Deploying multiple times per day → **Trunk-Based**
- Scheduled releases (app stores, versioned APIs) → **GitFlow**
- Everything else → **GitHub Flow**

The worst outcome is mixing strategies — pick one and document it in `CONTRIBUTING.md`.
