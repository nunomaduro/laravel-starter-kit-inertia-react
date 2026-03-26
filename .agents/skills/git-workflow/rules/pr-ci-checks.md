---
title: CI Checks Must Pass
category: pull-request
priority: high
tags: [pull-requests, ci-cd, quality-gates, automation]
related: [pr-small-focused, branch-main-protected]
---

# CI Checks Must Pass

All continuous integration checks must pass before a pull request can be merged.

## Bad Example

```bash
# Ignoring failing tests
gh pr merge --admin  # Bypass required checks

# Disabling tests that fail
git commit -m "test: skip flaky test to unblock PR"
# test.skip('this test fails sometimes')

# Merging with lint warnings
# "It's just style, I'll fix it later"
gh pr merge  # With 50 lint warnings

# Ignoring security scan results
# "That vulnerability doesn't affect us"
gh pr merge  # With critical security finding

# Committing to make CI pass without fixing issue
git commit -m "chore: disable eslint rule"
# /* eslint-disable security/detect-sql-injection */
```

## Good Example

```bash
# Wait for all checks to pass
gh pr checks --watch
# All checks passed

# Fix failing tests before requesting review
npm test
# Fix: update test assertions for new behavior
git commit -m "test: update assertions for new API response"

# Address lint issues
npm run lint -- --fix
git commit -m "style: fix lint issues"

# Address security findings
npm audit fix
git commit -m "chore: fix security vulnerabilities"

# If test is legitimately flaky, fix the flakiness
git commit -m "test: fix race condition in async test"

# Required checks configuration (GitHub)
# Settings > Branches > Branch protection rules
# - Require status checks to pass
# - Require branches to be up to date
```

## Why

Enforcing CI checks maintains code quality:

1. **Quality Gate**: Automated verification before human review
2. **Consistency**: Same checks run for every change
3. **Early Detection**: Catch issues before they reach production
4. **Time Saving**: Automated checks are faster than manual verification
5. **Confidence**: Green CI means basic quality bar is met

Essential CI checks:

```yaml
# Example GitHub Actions workflow
name: CI
on: [pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npm ci
      - run: npm test

  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npm ci
      - run: npm run lint

  type-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npm ci
      - run: npm run type-check

  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npm audit --audit-level=high

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npm ci
      - run: npm run build
```

Common checks to require:
- Unit tests
- Integration tests
- Linting / code style
- Type checking (TypeScript, mypy)
- Security scanning (npm audit, Snyk)
- Build verification
- Code coverage thresholds
- Performance benchmarks

Never bypass CI for "just this once" - that's how bugs reach production.
