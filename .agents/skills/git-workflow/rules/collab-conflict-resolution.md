---
title: Merge Conflict Resolution
category: collaboration
priority: medium
tags: [collaboration, merge-conflicts, resolution, testing]
related: [history-rebase-vs-merge, branch-short-lived]
---

# Merge Conflict Resolution

Handle merge conflicts carefully and systematically to maintain code integrity.

## Bad Example

```bash
# Blindly accepting one side
git checkout --ours .
git add .
git commit -m "resolve conflicts"
# Potentially lost important changes

# Blindly accepting incoming changes
git checkout --theirs .
git add .
git commit -m "resolve conflicts"
# Potentially lost your own work

# Leaving conflict markers in code
<<<<<<< HEAD
const timeout = 5000;
=======
const timeout = 10000;
>>>>>>> feature/update-timeout
# Code doesn't compile, markers committed

# Not testing after resolution
git add .
git commit -m "merge conflict resolved"
git push
# Broken code pushed without verification

# Complex merge without understanding both sides
# "I'll just delete this stuff and hope it works"
```

## Good Example

```bash
# Check conflict status
git status
# Shows files with conflicts

# View conflicts in detail
git diff --check

# Open conflicted file and understand both changes
# Look for conflict markers:
# <<<<<<< HEAD (your changes)
# =======
# >>>>>>> branch-name (their changes)

# Resolve thoughtfully, keeping necessary parts of both
# Before:
<<<<<<< HEAD
function calculateTotal(items) {
    return items.reduce((sum, item) => sum + item.price, 0);
}
=======
function calculateTotal(items, taxRate) {
    const subtotal = items.reduce((sum, item) => sum + item.price, 0);
    return subtotal * (1 + taxRate);
}
>>>>>>> feature/add-tax

# After (merged thoughtfully):
function calculateTotal(items, taxRate = 0) {
    const subtotal = items.reduce((sum, item) => sum + item.price, 0);
    return subtotal * (1 + taxRate);
}

# Stage resolved file
git add src/utils/pricing.js

# Continue rebase or complete merge
git rebase --continue
# or
git commit -m "merge: resolve conflict in pricing calculation

Merged tax calculation feature with existing implementation.
Made taxRate optional with default of 0 for backward compatibility."

# Verify resolution
npm test
npm run build
```

## Why

Proper conflict resolution prevents bugs and preserves work:

1. **Code Integrity**: Both sets of changes are considered
2. **Functionality**: Merged code works correctly
3. **History Clarity**: Meaningful merge commit explains resolution
4. **Team Trust**: Others' work is respected
5. **Prevention**: Understanding conflicts helps prevent future ones

Conflict resolution workflow:

```bash
# 1. Identify conflicts
git status

# 2. For each conflicted file:
#    - Open in editor
#    - Understand both versions
#    - Create merged version
#    - Remove conflict markers
#    - Stage the file
git add <resolved-file>

# 3. Complete the merge/rebase
git commit  # for merge
git rebase --continue  # for rebase

# 4. Verify everything works
npm test
npm run build

# 5. Push when confident
git push
```

Useful tools for conflict resolution:
```bash
# Use merge tool
git mergetool

# View specific version
git show :1:file  # Common ancestor
git show :2:file  # Your version (HEAD)
git show :3:file  # Their version (incoming)

# Abort if overwhelmed
git merge --abort
git rebase --abort
```

Prevention strategies:
- Keep branches short-lived
- Sync with main frequently
- Communicate about overlapping work
- Use feature flags to avoid parallel changes
- Coordinate large refactors with the team
