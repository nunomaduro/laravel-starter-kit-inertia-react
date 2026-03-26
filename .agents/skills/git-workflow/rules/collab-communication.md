---
title: Team Communication in Git Workflows
category: collaboration
priority: medium
tags: [collaboration, communication, coordination, team-work]
related: [pr-description-template, collab-code-review, branch-feature-workflow]
---

# Team Communication in Git Workflows

Communicate effectively with your team through commits, PRs, and related tools to maintain smooth collaboration.

## Bad Example

```bash
# Surprise large changes with no communication
git push origin main  # (direct push bypassing PR)
# Team wakes up to 500 changed files

# Vague PR with no context
gh pr create --title "updates" --body ""
# Team has no idea what this is or why

# No response to review comments
# Reviewer asks questions
# PR author ignores for days
# Eventually force merges

# Working on same area without coordination
# Developer A: feature/user-profile
# Developer B: feature/profile-redesign
# Both discover conflict after 2 weeks of work

# Silent rebases/force pushes
git rebase main
git push --force
# No notification to collaborators on the branch
```

## Good Example

```bash
# Announce significant changes in advance
# Slack/Teams: "Heads up - I'll be refactoring the auth module this week.
# PRs touching auth/* might have conflicts."

# Detailed PR description communicating intent
gh pr create --title "feat: implement user notification system" --body "
## Summary
Adding push notification support. This is the first part of the
notification epic (see #100 for full scope).

## Team Notes
- @backend-team: I added new API endpoints, please review the contracts
- @mobile-team: This enables the notification feature you're waiting for
- @devops: New env vars needed: PUSH_NOTIFICATION_KEY, PUSH_NOTIFICATION_SECRET

## Dependencies
- Requires #234 to be merged first
- Mobile app changes tracked in mobile-repo#567

## Testing
Please test with staging push notification service."

# Responsive PR communication
# Reviewer: "Why did you choose this approach?"
# Author: "Good question! I considered X and Y but chose Z because...
#          Let me add a comment in the code explaining this."

# Coordinate overlapping work
# Before starting: "I'm going to be working on the profile page this week.
# Is anyone else planning changes there?"

# Communicate before force push
# In PR comment: "I'm about to rebase this branch to resolve conflicts.
# @collaborator - please push any local changes before I proceed."
```

## Why

Good communication prevents problems and builds trust:

1. **Avoid Conflicts**: Know what others are working on
2. **Faster Reviews**: Context helps reviewers understand quickly
3. **Better Decisions**: Team input improves solutions
4. **Reduced Surprises**: No unexpected breaking changes
5. **Team Morale**: Respectful communication builds relationships

Communication points in the workflow:

| When | What to Communicate |
|------|---------------------|
| Starting work | What you're working on, expected scope |
| Creating PR | What, why, testing notes, dependencies |
| During review | Answer questions promptly, explain decisions |
| Before force push | Warn collaborators on shared branches |
| After merging | Announce if it affects others |
| Blocking issues | Escalate early, don't wait |

Channels for different purposes:

```
Git commits      -> Future developers ("why was this done?")
PR description   -> Current reviewers ("what should I review?")
PR comments      -> Specific code discussion
Slack/Teams      -> Urgent coordination, announcements
Issues/Tickets   -> Permanent record, tracking
Documentation    -> Long-term reference
```

PR comment best practices:
- Respond to all reviewer comments
- Mark resolved conversations
- Use suggestions feature for small changes
- Tag relevant people for specific questions
- Update description when scope changes

Team conventions to establish:
- Review turnaround time expectations
- PR size limits
- Required reviewers/approvals
- Communication channels for different purposes
- On-call/escalation procedures
