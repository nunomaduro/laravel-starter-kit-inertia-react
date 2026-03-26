# Git Workflow

Git best practices, commit conventions, and branching strategies for clean, maintainable repositories.

**Version:** 1.2.0 | **Rules:** 31 | **License:** MIT

---

## Overview

This skill provides guidance for:
- Commit message conventions and enforcement
- Branch naming, management, and workflow strategies
- Pull request workflows
- History management and git worktree
- Team collaboration and .gitignore

## Categories

### 1. Commit Messages (Critical) — 8 rules
Conventional commits, atomic changes, meaningful messages, git hooks enforcement.

### 2. Branching Strategy (High) — 8 rules
Branch naming, feature branches, protected main, GitFlow vs GitHub Flow vs Trunk-Based, monorepo workflows.

### 3. Pull Requests (High) — 6 rules
Small PRs, descriptions, reviews, CI checks, squash merge, draft PRs.

### 4. History Management (Medium) — 5 rules
Rebase vs merge, no force push, clean history, tags, git worktree.

### 5. Collaboration (Medium) — 4 rules
Code reviews, conflict resolution, communication, .gitignore best practices.

## Usage

Ask Claude to:
- "Review commit message"
- "Suggest branch name"
- "Check PR description"
- "Set up git workflow for new project"
- "Which workflow strategy should I use?"
- "How to use git worktree?"

## Quick Reference

### Commit Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `refactor`: Code restructure
- `test`: Tests
- `chore`: Maintenance
- `ci`: CI/CD changes

### Branch Prefixes
- `feature/`: New features
- `fix/`: Bug fixes
- `hotfix/`: Production fixes
- `docs/`: Documentation
- `refactor/`: Refactoring

### Workflow Strategy Guide
| Strategy | Best For | Complexity |
|----------|----------|------------|
| GitHub Flow | Small teams, continuous deploy | Low |
| GitFlow | Scheduled releases, versioned apps | High |
| Trunk-Based | Large teams, multiple deploys/day | Medium |

### Example Commits
```
feat(auth): add OAuth login
fix(cart): resolve total calculation
docs(api): update endpoint documentation
chore(deps): upgrade React to v19
```

## References

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [Trunk Based Development](https://trunkbaseddevelopment.com/)
- [Pro Git Book](https://git-scm.com/book/en/v2)
