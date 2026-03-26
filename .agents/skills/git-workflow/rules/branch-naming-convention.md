---
title: Branch Naming Convention
category: branch
priority: high
tags: [branching, naming, conventions, automation]
related: [branch-feature-workflow, commit-conventional-format]
---

# Branch Naming Convention

Use consistent, descriptive branch names that indicate the type and purpose of the work.

## Bad Example

```bash
# Vague or meaningless names
git checkout -b fix
git checkout -b update
git checkout -b johns-branch
git checkout -b test123

# Inconsistent formats
git checkout -b Feature_Login
git checkout -b fix-auth
git checkout -b ISSUE-456
git checkout -b add_new_stuff

# Too long or including unnecessary info
git checkout -b feature/add-the-new-user-authentication-system-with-oauth-and-jwt-support-for-the-mobile-app

# Spaces or special characters
git checkout -b "my feature"
git checkout -b feature@login
```

## Good Example

```bash
# Type prefix with descriptive slug
git checkout -b feature/user-authentication
git checkout -b fix/login-timeout
git checkout -b hotfix/security-patch
git checkout -b refactor/database-layer

# Include ticket/issue number
git checkout -b feature/AUTH-123-oauth-integration
git checkout -b fix/BUG-456-null-pointer
git checkout -b feature/GH-789-dark-mode

# Short but descriptive
git checkout -b docs/api-reference
git checkout -b test/payment-integration
git checkout -b chore/upgrade-dependencies

# Release and environment branches
git checkout -b release/v2.1.0
git checkout -b hotfix/v2.0.1
```

## Why

Consistent branch naming provides many benefits:

1. **Quick Identification**: Instantly understand the purpose of any branch
2. **Automation Ready**: CI/CD can trigger different workflows based on branch prefixes
3. **Filtering**: Easily list branches by type (`git branch --list 'feature/*'`)
4. **Issue Tracking**: Link branches to tickets for traceability
5. **Clean History**: Merge commits reference meaningful branch names

Recommended prefixes:
- `feature/` - New features or enhancements
- `fix/` - Bug fixes
- `hotfix/` - Urgent production fixes
- `refactor/` - Code restructuring
- `docs/` - Documentation updates
- `test/` - Test additions or fixes
- `chore/` - Maintenance tasks
- `release/` - Release preparation

Naming guidelines:
- Use lowercase letters
- Separate words with hyphens (kebab-case)
- Keep names concise but meaningful
- Include issue/ticket numbers when applicable
- Avoid personal identifiers (use ticket IDs instead)
