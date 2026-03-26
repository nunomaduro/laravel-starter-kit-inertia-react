---
title: Release Branch Strategy
category: branch
priority: high
tags: [branching, releases, deployment, versioning]
related: [history-tags-releases, branch-feature-workflow, commit-breaking-changes]
---

# Release Branch Strategy

Use a consistent branching strategy for releases that fits your deployment model and team size.

## Bad Example

```bash
# No release strategy - deploying random commits
git checkout main
git tag v1.2.3  # Tagged without preparation
git push --tags

# Mixing release and feature work
git checkout release/v2.0
git commit -m "feat: add new feature"  # Features in release branch!
git commit -m "fix: hotfix for release"

# Inconsistent tagging
git tag release-1.0
git tag v1.1.0
git tag 1.2
git tag version-1.3.0

# No clear production state
git log main  # Which commit is in production?
# Nobody knows

# Release branches that never merge back
git checkout release/v1.0
git commit -m "fix: production hotfix"
# Hotfix never merged to main, gets lost
```

## Good Example

```bash
# Trunk-Based Development (recommended for CI/CD)
git checkout main
# All development happens on main
# Deploy from main with feature flags
git tag -a v1.2.3 -m "Release version 1.2.3"
git push origin v1.2.3

# GitFlow for scheduled releases
git checkout -b release/v2.0.0 develop
# Only bug fixes in release branch
git commit -m "fix: correct date format in reports"
# When ready:
git checkout main
git merge --no-ff release/v2.0.0
git tag -a v2.0.0 -m "Release version 2.0.0"
git checkout develop
git merge --no-ff release/v2.0.0
git branch -d release/v2.0.0

# Hotfix workflow
git checkout -b hotfix/v2.0.1 main
git commit -m "fix: critical security patch"
git checkout main
git merge --no-ff hotfix/v2.0.1
git tag -a v2.0.1 -m "Hotfix release 2.0.1"
git checkout develop
git merge --no-ff hotfix/v2.0.1
git branch -d hotfix/v2.0.1

# Release with changelog
git checkout main
git tag -a v1.5.0 -m "Release version 1.5.0

Features:
- Add user dashboard (#123)
- Implement search functionality (#456)

Fixes:
- Resolve login timeout issue (#789)

Breaking Changes:
- API v1 deprecated, use v2"
```

## Why

A clear release strategy ensures reliable deployments:

1. **Predictability**: Everyone knows how releases work
2. **Traceability**: Clear mapping between tags and deployments
3. **Hotfix Path**: Quick way to patch production issues
4. **Stability**: Production code is always identifiable
5. **Rollback**: Easy to revert to previous release

Common strategies:

**Trunk-Based Development**
- All development on main
- Feature flags for incomplete work
- Deploy main continuously
- Best for: Small teams, frequent deployments

**GitHub Flow**
- main is always deployable
- Feature branches merge to main
- Deploy after merge or on tag
- Best for: Web apps, SaaS products

**GitFlow**
- main = production
- develop = integration
- release branches for preparation
- Best for: Scheduled releases, versioned software

Tagging conventions:
```bash
# Semantic versioning
v1.0.0      # Major.Minor.Patch
v1.0.0-rc.1 # Release candidate
v1.0.0-beta # Beta release
v1.0.0-alpha # Alpha release

# Always use annotated tags for releases
git tag -a v1.0.0 -m "Release message"
```
