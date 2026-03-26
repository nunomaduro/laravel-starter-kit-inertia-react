---
title: Conventional Commit Format
category: commit
priority: critical
tags: [commits, conventional-commits, standards, automation]
related: [commit-meaningful-subject, commit-references, commit-breaking-changes]
---

# Conventional Commit Format

Use the Conventional Commits specification for standardized, machine-readable commit messages.

## Bad Example

```bash
# Vague, non-standard format
git commit -m "fixed stuff"

# No type prefix
git commit -m "add user authentication"

# Mixed formats in the same repo
git commit -m "[FEATURE] login page"
git commit -m "BUG: fix crash"
git commit -m "updated tests"
```

## Good Example

```bash
# Standard conventional commit format
git commit -m "feat: add user authentication module"

# With scope for more context
git commit -m "fix(auth): resolve token refresh race condition"

# With body for detailed explanation
git commit -m "feat(api): add rate limiting to endpoints

Implement token bucket algorithm for rate limiting.
Default limit is 100 requests per minute per user.

Closes #234"

# Breaking change indicator
git commit -m "feat(api)!: change response format to JSON:API spec"
```

## Why

Conventional Commits provide several benefits:

1. **Automated Changelog Generation**: Tools can automatically generate changelogs from commit history
2. **Semantic Versioning**: Commits directly map to version bumps (feat = minor, fix = patch, breaking = major)
3. **Clear Communication**: Team members instantly understand the nature of changes
4. **Searchable History**: Easy to filter commits by type (e.g., find all fixes)
5. **CI/CD Integration**: Automate releases based on commit types

Common types include:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, semicolons)
- `refactor`: Code changes that neither fix bugs nor add features
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks (dependencies, build scripts)
- `ci`: CI/CD configuration changes
