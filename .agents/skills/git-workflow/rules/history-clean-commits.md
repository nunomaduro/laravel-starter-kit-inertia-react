---
title: Clean Up Commits Before Merging
category: history
priority: medium
tags: [history, interactive-rebase, cleanup, code-review]
related: [history-rebase-vs-merge, commit-atomic-changes, pr-squash-merge]
---

# Clean Up Commits Before Merging

Use interactive rebase to clean up commit history before creating or merging a pull request.

## Bad Example

```bash
# PR with messy commit history
git log --oneline feature/auth
# abc123 fix typo
# def456 oops forgot file
# ghi789 WIP
# jkl012 more WIP
# mno345 actually working now
# pqr678 fix tests
# stu901 add auth feature
# vwx234 WIP don't push
# yza567 merge main
# bcd890 start auth feature

# Commits that don't compile
# Each commit should leave the project in working state

# Commit messages that don't explain changes
# "stuff" "changes" "update" "fix" "wip"
```

## Good Example

```bash
# Interactive rebase to clean up before PR
git checkout feature/auth
git rebase -i main

# In the editor:
pick bcd890 feat: add authentication service
squash stu901 add auth feature
fixup yza567 WIP don't push
pick vwx234 feat: implement login form
fixup mno345 actually working now
fixup jkl012 more WIP
fixup ghi789 WIP
pick pqr678 test: add authentication tests
fixup def456 oops forgot file
fixup abc123 fix typo
# drop merge commits

# Result: clean, logical commits
git log --oneline feature/auth
# 111aaa feat: add authentication service
# 222bbb feat: implement login form
# 333ccc test: add authentication tests

# Reword commits during rebase
git rebase -i HEAD~3
# Change 'pick' to 'reword' for commits needing better messages

# Reorder commits for logical progression
# In interactive rebase, just reorder the lines

# After cleanup, force push to your branch
git push --force-with-lease origin feature/auth
```

## Why

Clean commit history provides long-term value:

1. **Meaningful History**: Each commit tells a clear story
2. **Easier Review**: Reviewers see logical progression of changes
3. **Better Bisect**: Each commit is a valid state for debugging
4. **Useful Blame**: `git blame` shows meaningful commits
5. **Simpler Reverts**: Clean commits are easier to revert selectively

Interactive rebase commands:

| Command | Effect |
|---------|--------|
| `pick` | Keep commit as-is |
| `reword` | Keep commit, edit message |
| `edit` | Pause to amend commit |
| `squash` | Combine with previous, keep message |
| `fixup` | Combine with previous, discard message |
| `drop` | Remove commit entirely |

Cleanup workflow:
```bash
# 1. Start interactive rebase
git rebase -i main

# 2. Squash WIP commits
# Change 'pick' to 'squash' or 'fixup'

# 3. Reorder if needed
# Move lines in editor

# 4. Reword messages
# Change 'pick' to 'reword'

# 5. Save and resolve any conflicts

# 6. Force push to your branch
git push --force-with-lease
```

Guidelines:
- Each commit should compile and pass tests
- Commit messages should be clear and conventional
- Related changes should be in the same commit
- Unrelated changes should be separate commits
- WIP commits should be squashed away
