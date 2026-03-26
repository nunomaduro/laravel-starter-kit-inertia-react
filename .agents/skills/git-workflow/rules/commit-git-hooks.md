---
title: Git Hooks for Commit Enforcement
category: commit
priority: high
tags: [commits, git-hooks, husky, commitlint, automation]
related: [commit-conventional-format, commit-meaningful-subject, pr-ci-checks]
---

# Git Hooks for Commit Enforcement

Use Git hooks to automatically enforce commit message standards and run checks before commits reach the repository.

## Bad Example

```bash
# No hooks — bad commits reach the repo unchecked
git commit -m "fix"
git commit -m "wip"
git commit -m "asdfasdf"
# All accepted with no validation

# Manual-only enforcement — relies on discipline
# Team agrees to follow conventions but nothing enforces it
# One developer ignores conventions, pollutes history
```

## Good Example

```bash
# Install Husky and commitlint
npm install --save-dev husky @commitlint/cli @commitlint/config-conventional

# Enable Husky
npx husky init

# Create commit-msg hook
echo "npx --no -- commitlint --edit \$1" > .husky/commit-msg
chmod +x .husky/commit-msg

# Create pre-commit hook for linting and tests
echo "npm run lint && npm run test:unit" > .husky/pre-commit
chmod +x .husky/pre-commit
```

```javascript
// commitlint.config.js
module.exports = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'type-enum': [
      2,
      'always',
      ['feat', 'fix', 'docs', 'style', 'refactor', 'perf', 'test', 'chore', 'ci', 'build', 'revert']
    ],
    'subject-max-length': [2, 'always', 72],
    'subject-case': [2, 'always', 'lower-case'],
    'body-max-line-length': [2, 'always', 100],
  },
};
```

```json
// package.json — ensure hooks are installed on npm install
{
  "scripts": {
    "prepare": "husky"
  }
}
```

```bash
# Now bad commits are rejected automatically
git commit -m "fix"
# ✖   subject may not be empty [subject-empty]
# ✖   type may not be empty [type-empty]

git commit -m "feat(auth): add OAuth login"
# ✔   Commit message is valid
```

## Why

Git hooks automate enforcement at the source, before bad commits reach the repository:

1. **Immediate Feedback**: Developers know instantly if their commit message is wrong
2. **Zero Drift**: Conventions are enforced consistently — no exceptions
3. **Automated Changelog**: Properly formatted commits enable tools like `semantic-release`
4. **No CI Dependency**: Catches issues locally before pushing, saving CI minutes
5. **Onboarding**: New developers learn conventions through immediate feedback

Hook types commonly used:

| Hook | When | Use For |
|------|------|---------|
| `commit-msg` | After writing message | Validate commit format |
| `pre-commit` | Before commit | Lint, format, unit tests |
| `pre-push` | Before push | Integration tests, build check |

**Important:** Add `.husky/` to version control so all team members get the same hooks. The `prepare` script in `package.json` installs hooks automatically after `npm install`.
