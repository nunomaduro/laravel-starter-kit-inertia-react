---
title: Imperative Mood in Commit Messages
category: commit
priority: critical
tags: [commits, writing-style, conventions, grammar]
related: [commit-conventional-format, commit-meaningful-subject]
---

# Imperative Mood in Commit Messages

Write commit messages in the imperative mood, as if giving a command or instruction.

## Bad Example

```bash
# Past tense
git commit -m "fixed the login bug"
git commit -m "added user authentication"
git commit -m "updated the README"

# Present participle (-ing form)
git commit -m "fixing memory leak in cache"
git commit -m "adding support for dark mode"

# Third person present
git commit -m "fixes issue with form validation"
git commit -m "adds new API endpoint"

# Descriptive statement
git commit -m "this commit adds a new feature"
git commit -m "changes to the database schema"
```

## Good Example

```bash
# Imperative mood - like giving a command
git commit -m "fix the login bug"
git commit -m "add user authentication"
git commit -m "update the README"

# Think: "This commit will..."
git commit -m "fix memory leak in cache"
git commit -m "add support for dark mode"

# Matches git's own conventions
git commit -m "merge branch 'feature' into main"
git commit -m "revert 'add broken feature'"

# Complete examples
git commit -m "refactor database connection handling"
git commit -m "remove deprecated API endpoints"
git commit -m "implement retry logic for failed requests"
```

## Why

Using imperative mood provides consistency and clarity:

1. **Git's Convention**: Git itself uses imperative mood ("Merge branch...", "Revert...", "Initial commit")
2. **Completes the Sentence**: The message completes "If applied, this commit will..."
   - "If applied, this commit will *fix the login bug*" (correct)
   - "If applied, this commit will *fixed the login bug*" (incorrect)
3. **Conciseness**: Imperative mood is typically shorter and more direct
4. **Action-Oriented**: Focuses on what the commit does, not what was done
5. **Industry Standard**: Most open source projects and style guides recommend this convention

Quick test: Read your commit message after "This commit will..." - if it sounds grammatically correct, you've used imperative mood.
