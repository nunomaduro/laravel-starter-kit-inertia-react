---
title: Monorepo Git Workflows
category: branch
priority: medium
tags: [monorepo, branching, nx, turborepo, affected]
related: [branch-workflow-strategies, branch-feature-workflow, pr-small-focused]
---

# Monorepo Git Workflows

Adapt branching and PR strategies for monorepos to avoid unnecessary builds and keep changes scoped.

## Bad Example

```bash
# PR touches everything — triggers full rebuild for unrelated change
git diff main --name-only
# apps/web/src/components/Button.tsx
# apps/mobile/src/screens/Home.tsx
# libs/shared/utils/format.ts
# apps/api/src/routes/users.ts
# One PR, four unrelated packages changed

# No affected detection — runs all tests on every commit
# CI: run all 4000 tests for a README change
# CI time: 45 minutes for a 1-line docs update

# Flat branch names with no package context
git checkout -b fix-button
# Which package? Which app?
```

## Good Example

```bash
# Scope branches to the package being changed
git checkout -b feat/web-auth-login
git checkout -b fix/api-user-validation
git checkout -b chore/shared-utils-cleanup

# Keep PRs scoped to one package when possible
git diff main --name-only
# apps/web/src/components/Button.tsx  ← one package
# apps/web/src/components/Button.test.tsx

# Use affected commands to run only what changed
# Nx
npx nx affected --target=test
npx nx affected --target=build
npx nx affected --target=lint

# Turborepo
npx turbo run test --filter=...[main]
npx turbo run build --filter=...[main]
```

```yaml
# CI — only run affected packages
# .github/workflows/ci.yml
name: CI
on: pull_request

jobs:
  affected:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0  # Required for affected detection

      - name: Determine affected projects
        run: npx nx show projects --affected --base=origin/main

      - name: Test affected
        run: npx nx affected --target=test --base=origin/main

      - name: Build affected
        run: npx nx affected --target=build --base=origin/main
```

```bash
# Conventional commits with package scope
git commit -m "feat(web): add login page"
git commit -m "fix(api): resolve user not found error"
git commit -m "chore(shared): update lodash dependency"

# Tag releases per package (independent versioning)
git tag web-v2.1.0
git tag api-v1.4.2
git tag shared-v3.0.0
```

## Why

Monorepos require adapted practices to stay performant:

1. **Affected detection**: Running all tests on every commit wastes CI time. `nx affected` or `turbo --filter` only runs tasks for packages that changed (directly or via dependency graph).

2. **Scoped PRs**: Mixing changes across packages makes reviews harder and can cause unintended side effects. One PR per package keeps changes reviewable and rollbacks simple.

3. **Scoped commits**: Including the package name in the commit scope (`feat(web):`, `fix(api):`) makes the changelog and git history meaningful at the package level.

4. **Independent versioning**: Each package can have its own version and release cycle. Tag packages individually (`web-v2.1.0`) rather than tagging the whole repo.

5. **`fetch-depth: 0` in CI**: Shallow clones break affected detection — the tool needs full history to determine what changed relative to the base branch.
