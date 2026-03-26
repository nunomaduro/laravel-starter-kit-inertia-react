---
name: git-workflow
description: Git best practices, branching strategies, commit conventions, and PR workflows. Use when reviewing git history, writing commits, setting up branching strategy, or improving git practices. Triggers on "git best practices", "commit message", "branching strategy", or "PR workflow".
license: MIT
metadata:
  author: AsyrafHussin
  version: "1.2.0"
---

# Git Workflow

Git best practices, commit conventions, branching strategies, and pull request workflows. Guidelines for maintaining a clean, useful git history.

## When to Apply

Reference these guidelines when:
- Writing commit messages
- Creating branches
- Setting up git workflows
- Reviewing pull requests
- Maintaining git history

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Commit Messages | CRITICAL | `commit-` |
| 2 | Branching Strategy | HIGH | `branch-` |
| 3 | Pull Requests | HIGH | `pr-` |
| 4 | History Management | MEDIUM | `history-` |
| 5 | Collaboration | MEDIUM | `collab-` |

## Quick Reference

### 1. Commit Messages (CRITICAL)

- `commit-conventional` - Use conventional commits
- `commit-atomic` - Atomic commits (one logical change)
- `commit-present-tense` - Use imperative present tense
- `commit-meaningful` - Descriptive, meaningful messages
- `commit-body` - Add body for complex changes
- `commit-references` - Reference issues/tickets
- `commit-git-hooks` - Enforce standards with Husky + commitlint

### 2. Branching Strategy (HIGH)

- `branch-naming` - Consistent branch naming
- `branch-feature` - Feature branch workflow
- `branch-main-protected` - Protect main branch
- `branch-short-lived` - Keep branches short-lived
- `branch-delete-merged` - Delete merged branches
- `branch-release` - Release branch strategy
- `branch-workflow-strategies` - GitFlow vs GitHub Flow vs Trunk-Based
- `branch-monorepo` - Monorepo git workflows

### 3. Pull Requests (HIGH)

- `pr-small` - Keep PRs small and focused
- `pr-description` - Write clear descriptions
- `pr-reviewers` - Request appropriate reviewers
- `pr-ci-pass` - Ensure CI passes
- `pr-squash` - Squash when appropriate
- `pr-draft` - Use draft PRs for WIP and early feedback

### 4. History Management (MEDIUM)

- `history-rebase` - Rebase vs merge
- `history-no-force-push` - Avoid force push to shared branches
- `history-clean` - Keep history clean
- `history-tags` - Use tags for releases
- `history-worktree` - Work on multiple branches with git worktree

### 5. Collaboration (MEDIUM)

- `collab-code-review` - Effective code reviews
- `collab-conflicts` - Handle merge conflicts
- `collab-communication` - Communicate changes
- `collab-gitignore` - .gitignore best practices

## Essential Guidelines

### Conventional Commits

```
# Format
<type>(<scope>): <subject>

<body>

<footer>
```

#### Types

| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Formatting, no code change |
| `refactor` | Code change, no feature/fix |
| `perf` | Performance improvement |
| `test` | Adding/updating tests |
| `chore` | Maintenance, dependencies |
| `ci` | CI/CD changes |
| `build` | Build system changes |
| `revert` | Revert previous commit |

#### Examples

```bash
# ✅ Good commit messages
feat(auth): add password reset functionality

fix(cart): resolve quantity update race condition

docs(readme): add installation instructions

refactor(api): extract validation into middleware

test(user): add unit tests for registration

chore(deps): update dependencies to latest versions

# With body and footer
feat(orders): implement order cancellation

Add ability for users to cancel pending orders within 24 hours.
Cancelled orders trigger refund process automatically.

Closes #123
BREAKING CHANGE: Order status enum now includes 'cancelled'

# ❌ Bad commit messages
fix bug
update
WIP
asdfasdf
changes
misc fixes
```

### Branch Naming

```bash
# ✅ Good branch names
feature/user-authentication
feature/JIRA-123-password-reset
fix/cart-total-calculation
fix/issue-456-login-redirect
hotfix/security-patch
docs/api-documentation
refactor/database-queries
chore/update-dependencies

# ❌ Bad branch names
new-feature
john-branch
test
fix
temp
asdf
```

### Branch Workflow

```bash
# Main branches
main          # Production-ready code
develop       # Integration branch (optional)

# Supporting branches
feature/*     # New features
fix/*         # Bug fixes
hotfix/*      # Production hotfixes
release/*     # Release preparation

# Workflow
git checkout main
git pull origin main
git checkout -b feature/user-profile

# Work on feature...
git add .
git commit -m "feat(profile): add profile page"

# Keep branch updated
git fetch origin
git rebase origin/main

# Push and create PR
git push -u origin feature/user-profile
```

### Atomic Commits

```bash
# ❌ One commit doing multiple things
git commit -m "Add login, fix header, update deps"

# ✅ Separate commits for each change
git commit -m "feat(auth): add login page"
git commit -m "fix(header): correct navigation alignment"
git commit -m "chore(deps): update React to v18.2"
```

### Commit Frequently, Push Regularly

```bash
# ✅ Small, frequent commits
git commit -m "feat(cart): add product to cart"
git commit -m "feat(cart): display cart item count"
git commit -m "feat(cart): implement remove item"
git commit -m "test(cart): add unit tests"

# ❌ One massive commit
git commit -m "feat: implement entire shopping cart"
```

### Pull Request Best Practices

#### PR Title

```
# Format (like commit)
<type>(<scope>): <description>

# ✅ Good PR titles
feat(auth): implement OAuth2 login
fix(checkout): resolve payment processing error
docs(api): add endpoint documentation

# ❌ Bad PR titles
Update
Fix stuff
WIP
```

#### PR Description Template

```markdown
## Summary
Brief description of what this PR does.

## Changes
- Added user authentication endpoints
- Implemented JWT token generation
- Added password hashing with bcrypt

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed

## Screenshots (if UI changes)
[Add screenshots here]

## Related Issues
Closes #123
Related to #456

## Checklist
- [ ] Code follows project conventions
- [ ] Self-review completed
- [ ] Tests added/updated
- [ ] Documentation updated
```

### Keep PRs Small

```
# ✅ Focused PRs
PR #1: Add user model and migrations
PR #2: Implement user registration endpoint
PR #3: Add email verification
PR #4: Implement login/logout

# ❌ Large unfocused PR
PR #1: Add entire user authentication system
       (2000+ lines, 50+ files)
```

### Rebase vs Merge

```bash
# ✅ Rebase for feature branches (clean history)
git checkout feature/my-feature
git fetch origin
git rebase origin/main
# Resolve any conflicts
git push --force-with-lease  # Only your branch!

# ✅ Merge for integrating to main (preserve context)
git checkout main
git merge --no-ff feature/my-feature
# Creates merge commit, preserves branch history

# ❌ Never force push to shared branches
git push --force origin main  # NEVER DO THIS
```

### Interactive Rebase (Clean Up)

```bash
# Before pushing, clean up commits
git rebase -i HEAD~5

# In editor:
pick abc123 feat: add login page
squash def456 fix typo
squash ghi789 more fixes
pick jkl012 feat: add logout

# Result: clean history with meaningful commits
```

### Handling Conflicts

```bash
# During rebase
git rebase origin/main
# CONFLICT in file.ts

# 1. Open conflicted files
# 2. Resolve conflicts (remove markers)
# 3. Stage resolved files
git add file.ts

# 4. Continue rebase
git rebase --continue

# Or abort if needed
git rebase --abort
```

### Git Hooks

```bash
# .husky/pre-commit
#!/bin/sh
npm run lint
npm run test:unit

# .husky/commit-msg
#!/bin/sh
npx commitlint --edit $1

# commitlint.config.js
module.exports = {
  extends: ['@commitlint/config-conventional'],
};
```

### Tagging Releases

```bash
# Semantic versioning tags
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0

# List tags
git tag -l "v1.*"

# Tag format
v1.0.0    # Major.Minor.Patch
v1.0.0-beta.1  # Pre-release
v1.0.0-rc.1    # Release candidate
```

### .gitignore Best Practices

```gitignore
# Dependencies
node_modules/
vendor/

# Build outputs
dist/
build/
.next/

# Environment files
.env
.env.local
.env.*.local

# IDE
.idea/
.vscode/
*.swp

# OS files
.DS_Store
Thumbs.db

# Logs
*.log
logs/

# Test coverage
coverage/

# Cache
.cache/
*.cache
```

### Useful Git Commands

```bash
# View commit history
git log --oneline --graph --all

# See what changed
git diff --staged
git diff HEAD~1

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Discard local changes
git checkout -- file.ts
git restore file.ts  # Git 2.23+

# Stash changes
git stash
git stash pop
git stash list

# Cherry-pick commit
git cherry-pick abc123

# Find who changed a line
git blame file.ts

# Search commits
git log --grep="fix"
git log -S "functionName"
```

## Output Format

When reviewing git practices, output findings:

```
[category] Description of issue or suggestion
```

Example:
```
[commit] Use imperative mood: "Add feature" not "Added feature"
[branch] Branch name 'test' should follow pattern: feature/description
[pr] PR is too large (50+ files), consider splitting
```

## How to Use

Read individual rule files for detailed explanations:

```
rules/commit-conventional-format.md
rules/branch-naming-convention.md
rules/pr-small-focused.md
```

## References

- [Git Official Documentation](https://git-scm.com/doc) - Comprehensive Git documentation
- [Pro Git Book](https://git-scm.com/book/en/v2) - The complete Pro Git book
- [Conventional Commits](https://www.conventionalcommits.org) - Commit message specification
- [GitHub Flow](https://docs.github.com/en/get-started/quickstart/github-flow) - Lightweight workflow
- [How to Write a Git Commit Message](https://chris.beams.io/posts/git-commit/) - Commit message guide
- [Google Code Review Practices](https://google.github.io/eng-practices/review/) - Code review best practices

## Examples from Well-Known Projects

Learn from projects with excellent git practices:
- **Linux Kernel** - Detailed commit messages and patch workflow
- **React** - Conventional commits and thorough PR reviews
- **Vue.js** - Clean commit history and good PR templates
- **TypeScript** - Structured branching and clear release process
- **Next.js** - Conventional commits and automated releases

## Configuration Examples

### Commitlint

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
    'body-max-line-length': [2, 'always', 100]
  }
};
```

### Husky Git Hooks

```bash
# .husky/commit-msg
#!/bin/sh
npx commitlint --edit $1

# .husky/pre-commit
#!/bin/sh
npm run lint
npm test
```

### GitHub PR Template

```markdown
# .github/PULL_REQUEST_TEMPLATE.md
## Summary
Brief description of changes

## Changes
- List key changes
- One per line

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed

## Related
Closes #issue-number
```

---

## Metadata

**Skill Version:** 1.2.0
**Last Updated:** 2026-03-08
**Total Rules:** 31
**Categories:** 5 (Commit Messages, Branching Strategy, Pull Requests, History Management, Collaboration)

**Compatible With:**
- Git 2.0+
- GitHub, GitLab, Bitbucket, Azure DevOps

**Recommended Tools:**
- [Git](https://git-scm.com/) - Version control system
- [GitHub CLI](https://cli.github.com/) - GitHub command-line tool
- [Commitlint](https://commitlint.js.org/) - Commit message linter
- [Husky](https://typicode.github.io/husky/) - Git hooks
- [Semantic Release](https://semantic-release.gitbook.io/) - Automated versioning

---

## License

MIT License. This skill is provided as-is for educational and development purposes.
