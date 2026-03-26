---
title: Protected Main Branch
category: branch
priority: high
tags: [branching, protection, security, quality-gates]
related: [branch-feature-workflow, pr-ci-checks, collab-code-review]
---

# Protected Main Branch

The main branch should be protected from direct pushes and require pull requests for all changes.

## Bad Example

```bash
# Pushing directly to main
git checkout main
git commit -m "quick fix"
git push origin main

# Force pushing to main
git push --force origin main

# Bypassing branch protection
git push origin main --no-verify

# Merging locally and pushing
git checkout main
git merge feature/something
git push origin main

# Resetting main to different state
git checkout main
git reset --hard HEAD~5
git push --force origin main
```

## Good Example

```bash
# All changes through pull requests
git checkout -b fix/login-issue
git commit -m "fix: resolve login timeout"
git push -u origin fix/login-issue
gh pr create --base main

# Wait for reviews and CI
gh pr checks
gh pr status

# Merge through GitHub/GitLab UI or CLI
gh pr merge --squash

# For hotfixes, still use PR (just expedited)
git checkout -b hotfix/security-patch
git commit -m "fix: patch XSS vulnerability"
git push -u origin hotfix/security-patch
gh pr create --base main --label "urgent"
# Request expedited review
```

## Why

Protected main branch is crucial for code quality:

1. **Code Review**: Every change is reviewed by at least one other person
2. **CI Verification**: All tests must pass before merging
3. **Audit Trail**: All changes are traceable through PRs
4. **Reduced Risk**: Prevents accidental pushes of broken code
5. **Compliance**: Required for many security and regulatory standards

Recommended protection rules:

```yaml
# GitHub branch protection settings
main:
  required_pull_request_reviews:
    required_approving_review_count: 1
    dismiss_stale_reviews: true
    require_code_owner_reviews: true
  required_status_checks:
    strict: true
    contexts:
      - "ci/tests"
      - "ci/lint"
  enforce_admins: true
  required_linear_history: true
  allow_force_pushes: false
  allow_deletions: false
```

Protection levels:
- **Minimum**: Require PR, 1 approval
- **Standard**: Require PR, 1 approval, CI passing
- **Strict**: Require PR, 2 approvals, CI passing, CODEOWNERS
- **Maximum**: All above + enforce for admins, require linear history

Even maintainers and admins should follow the PR process to maintain consistency and set a good example.
