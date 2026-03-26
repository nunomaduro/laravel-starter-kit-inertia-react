---
title: Draft Pull Requests
category: pull-request
priority: high
tags: [pull-requests, collaboration, work-in-progress, early-feedback]
related: [pr-description-template, pr-ci-checks]
---

# Draft Pull Requests

Use draft PRs to share work-in-progress, get early feedback, and run CI before requesting formal review.

## Bad Example

```bash
# Requesting review on incomplete work
gh pr create --title "WIP: new feature" --reviewer "senior-dev"
# Wasting reviewer's time on unfinished code

# Waiting until everything is perfect
# 3 weeks of solo development
# Then: "Here's my 5000-line PR, please review"

# No visibility into ongoing work
# Team has no idea what you're working on
# Surprise: massive PR appears

# Using comments to indicate draft status
gh pr create --title "[WIP] [DO NOT MERGE] [DRAFT] feature"
# Inconsistent, easy to accidentally merge
```

## Good Example

```bash
# Create draft PR early
gh pr create --draft --title "feat: implement checkout flow"

# CI runs on draft PRs
gh pr checks --watch
# Fix issues before requesting review

# Share work in progress for early feedback
gh pr create --draft --title "RFC: new architecture approach" --body "
## Status: Draft - Seeking Feedback

This PR explores a new approach to state management.
Not ready for detailed review, but looking for:

- [ ] Is this direction worth pursuing?
- [ ] Any concerns with the overall approach?
- [ ] Similar patterns we should consider?

Will mark ready for review once implementation is complete.
"

# Convert to ready when complete
gh pr ready 123

# Or mark ready with reviewers
gh pr ready 123 --reviewer "tech-lead,domain-expert"

# Use draft for running CI on experiments
gh pr create --draft --title "experiment: try new bundler"
# Test in CI without requesting review
# Close without merging if experiment fails
```

## Why

Draft PRs improve collaboration and code quality:

1. **Early CI Feedback**: Catch build/test issues before review
2. **Visibility**: Team sees work in progress
3. **Early Design Feedback**: Get directional input before investing heavily
4. **No Premature Reviews**: Clearly signals "not ready for detailed review"
5. **Reduced Rework**: Course-correct early based on feedback

When to use draft PRs:

| Situation | Use Draft? |
|-----------|-----------|
| Work in progress | Yes |
| Seeking architectural feedback | Yes |
| Running CI on experiment | Yes |
| Waiting for dependency PR | Yes |
| Ready for review | No (convert to ready) |
| Hotfix that needs immediate review | No |

Draft PR workflow:
1. Create draft PR when starting work
2. Push commits as you develop
3. CI runs automatically
4. Request informal feedback if needed
5. Mark ready when implementation complete
6. Add reviewers for formal review
7. Address feedback and merge

Draft PR etiquette:
- Don't request formal reviewers on drafts
- Use PR description to explain what feedback you want
- Update status in description as work progresses
- Convert to ready promptly when done
- Close drafts that won't be completed

Protect against accidental merges:
```yaml
# Branch protection
# [x] Require pull request reviews
# Draft PRs cannot be merged until marked ready
```
