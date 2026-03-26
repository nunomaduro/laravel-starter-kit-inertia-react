# Git Workflow - Agent Guidelines

**Version:** 1.2.0 | **Author:** AsyrafHussin

Git best practices, commit conventions, branching strategies, and pull request workflows.

---

## Rule Categories

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Commit Messages | CRITICAL | `commit-` |
| 2 | Branching Strategy | HIGH | `branch-` |
| 3 | Pull Requests | HIGH | `pr-` |
| 4 | History Management | MEDIUM | `history-` |
| 5 | Collaboration | MEDIUM | `collab-` |

## How to Apply

Apply rules in priority order: CRITICAL → HIGH → MEDIUM. Consider team size and project context.

Output format:
```
[category] Description of issue or recommendation
```

Example:
```
[commit] Use imperative mood: "Add feature" not "Added feature"
[branch] Branch name should follow pattern: feature/description
[pr] PR is too large (50+ files), consider splitting
```

---

## What to Check Per Category

### 1. Commit Messages (CRITICAL)

- Conventional commit format: `type(scope): subject`
- Subject line ≤ 72 characters, imperative mood
- Body present for complex changes (explains why, not what)
- Breaking changes marked with `!` or `BREAKING CHANGE:` footer
- Issue/ticket references in footer (`Closes #123`, `Fixes #456`)
- Atomic commits — one logical change per commit
- Git hooks (Husky + commitlint) enforce format automatically

### 2. Branching Strategy (HIGH)

- Branch names follow `type/description` pattern (kebab-case)
- `main`/`master` protected — no direct pushes
- Feature branches short-lived (merged within days, not weeks)
- Merged branches deleted after merge
- Release branches used for versioned releases
- Workflow strategy defined: GitHub Flow, GitFlow, or Trunk-Based
- Monorepos: branches scoped to package, affected detection in CI

### 3. Pull Requests (HIGH)

- PR size: ideal < 200 lines, flag > 400 lines
- Description includes summary, changes, and testing notes
- PR title follows conventional commit format
- Appropriate reviewers assigned
- CI checks required before merge
- Draft PRs used for work-in-progress

### 4. History Management (MEDIUM)

- No force pushes to shared branches (`main`, `develop`)
- Consistent merge strategy (squash, merge, or rebase — pick one)
- Release tags follow semantic versioning (`v1.2.3`)
- No WIP/fixup commits in main branch history
- `git worktree` used for parallel branch work instead of stashing

### 5. Collaboration (MEDIUM)

- Code review comments are constructive and specific
- Merge conflicts resolved by the branch author
- CODEOWNERS file defines ownership for critical paths
- Breaking changes communicated to the team
- `.gitignore` present, no secrets or build artifacts committed

---

## Priority Rules for Common Requests

### "Review my commit message"
1. Check conventional format (`commit-conventional-format`)
2. Check subject length and mood (`commit-meaningful-subject`, `commit-imperative-mood`)
3. Check body if complex change (`commit-body-context`)
4. Check footer references (`commit-references`, `commit-breaking-changes`)

### "Review my branch name"
1. Check naming pattern (`branch-naming-convention`)
2. Check type prefix matches the work type (`branch-feature-workflow`)

### "Review my PR"
1. Check size (`pr-small-focused`)
2. Check description quality (`pr-description-template`)
3. Check CI requirements (`pr-ci-checks`)
4. Check reviewer assignment (`pr-reviewers`)
5. Check merge strategy (`pr-squash-merge`)

### "Set up git workflow for new project"
1. Set up commitlint with conventional commits config
2. Configure Husky hooks for commit-msg validation
3. Create `.github/PULL_REQUEST_TEMPLATE.md`
4. Enable branch protection on `main`
5. Create `CONTRIBUTING.md` with git guidelines
6. Set up semantic-release for automated versioning

---

## Common Issues and Fixes

### Vague commit messages
```bash
# Bad
git commit -m "fix bug"

# Good
git commit -m "fix(auth): resolve token expiry not refreshing session"
```

### Oversized PR
- Split by layer: API endpoints PR → UI components PR → integration PR
- Separate refactoring from feature work
- Use feature flags to merge incomplete features safely

### Unprotected main branch
```bash
gh api repos/:owner/:repo/branches/main/protection --method PUT --input - <<EOF
{
  "required_pull_request_reviews": { "required_approving_review_count": 1 },
  "required_status_checks": { "strict": true, "contexts": ["ci/test"] },
  "enforce_admins": true,
  "allow_force_pushes": false
}
EOF
```

### Inconsistent branch naming — enforce with CI
```yaml
# .github/workflows/branch-naming.yml
name: Branch Naming
on: pull_request
jobs:
  check:
    runs-on: ubuntu-latest
    steps:
      - name: Check branch name
        run: |
          if [[ ! "${{ github.head_ref }}" =~ ^(feature|fix|hotfix|refactor|docs|test|chore)/.+ ]]; then
            echo "Branch name must match pattern: type/description"
            exit 1
          fi
```

---

## Agent Self-Check

Before completing a review, verify:
- [ ] Checked all CRITICAL rules first (commit messages)
- [ ] Provided specific, actionable recommendations
- [ ] Included concrete examples or configuration snippets
- [ ] Explained why each issue matters
- [ ] Prioritized issues by impact
- [ ] Considered team size and project context

---

## References

- [Pro Git Book](https://git-scm.com/book/en/v2)
- [Conventional Commits](https://www.conventionalcommits.org)
- [GitHub Flow](https://docs.github.com/en/get-started/quickstart/github-flow)
- [Google Code Review Guide](https://google.github.io/eng-practices/review/)
