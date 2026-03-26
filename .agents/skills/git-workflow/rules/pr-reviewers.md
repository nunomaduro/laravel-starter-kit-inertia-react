---
title: Requesting and Assigning Reviewers
category: pull-request
priority: high
tags: [pull-requests, code-review, collaboration, codeowners]
related: [pr-description-template, collab-code-review]
---

# Requesting and Assigning Reviewers

Thoughtfully select reviewers who can provide valuable feedback on your changes.

## Bad Example

```bash
# No reviewers assigned
gh pr create --title "feat: new feature"
# PR sits for days with no review

# Adding everyone to every PR
gh pr create --reviewer "alice,bob,charlie,david,eve,frank"
# Diffusion of responsibility - no one reviews thoroughly

# Only selecting friends for easy approvals
gh pr create --reviewer "my-buddy"
# Missing expertise, potential rubber-stamping

# Random reviewer selection
gh pr create --reviewer "whoever-is-online"
# Missing domain knowledge

# Assigning junior developers to review critical security changes
gh pr create --title "fix: security vulnerability" --reviewer "intern"
```

## Good Example

```bash
# Select reviewers based on expertise
gh pr create --title "feat: add payment processing" \
  --reviewer "payment-team-lead,security-expert"

# Use CODEOWNERS for automatic assignment
# .github/CODEOWNERS
# /src/payments/ @payment-team
# /src/auth/ @security-team
# *.sql @database-team

# Balanced review team
gh pr create --title "feat: new dashboard" \
  --reviewer "frontend-expert,product-owner"

# Request specific feedback
gh pr create --title "refactor: database layer" --body "
## Reviewers
- @db-expert: Please review query optimization
- @api-lead: Please verify API contract unchanged
"

# Escalate important changes
gh pr create --title "fix: critical security patch" \
  --reviewer "security-team,tech-lead" \
  --label "security,urgent"
```

## Why

Thoughtful reviewer selection improves code quality:

1. **Domain Expertise**: Reviewers catch issues specific to their area
2. **Knowledge Sharing**: Reviews spread understanding across the team
3. **Accountability**: Clear ownership of the review process
4. **Balanced Perspectives**: Different viewpoints catch different issues
5. **Mentorship**: Junior developers learn by reviewing senior code

Reviewer selection criteria:
- **Code Ownership**: Who maintains the affected code?
- **Domain Knowledge**: Who understands this feature area?
- **Recent Context**: Who worked on related changes recently?
- **Growth Opportunity**: Can this help someone learn?
- **Availability**: Who has bandwidth for timely review?

CODEOWNERS file setup:
```
# .github/CODEOWNERS

# Default owners
* @tech-lead

# Frontend
/src/components/ @frontend-team
/src/pages/ @frontend-team

# Backend
/src/api/ @backend-team
/src/services/ @backend-team

# Security-sensitive
/src/auth/ @security-team
/src/payments/ @security-team @payment-lead

# Infrastructure
/terraform/ @devops-team
/.github/ @devops-team
```

Best practices:
- Require at least 1-2 reviewers
- Include at least one domain expert
- Rotate reviewers to spread knowledge
- Don't overload individuals with reviews
- Set expectations for review turnaround time
