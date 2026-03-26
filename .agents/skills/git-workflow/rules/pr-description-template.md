---
title: PR Description Template
category: pull-request
priority: high
tags: [pull-requests, documentation, templates, communication]
related: [pr-small-focused, pr-reviewers, commit-body-context]
---

# PR Description Template

Use a consistent, informative template for pull request descriptions to aid reviewers and document changes.

## Bad Example

```bash
# No description
gh pr create --title "fix stuff"
# Body: (empty)

# Minimal unhelpful description
gh pr create --title "Update code" --body "Made some changes"

# Just restating the title
gh pr create --title "Add login feature" --body "This PR adds a login feature"

# Technical without context
gh pr create --title "Refactor auth" --body "
Changed AuthService.ts
Modified UserController.js
Updated tests
"
```

## Good Example

```bash
gh pr create --title "feat: add two-factor authentication" --body "$(cat <<'EOF'
## Summary
Implements TOTP-based two-factor authentication for user accounts, addressing security requirements from the Q4 audit.

## Changes
- Add TOTP secret generation and verification in AuthService
- Create 2FA setup flow with QR code display
- Add backup codes generation and storage
- Update login flow to prompt for 2FA when enabled

## Testing
- [ ] Manual testing of setup flow completed
- [ ] Verified QR codes work with Google Authenticator
- [ ] Tested backup code usage
- [ ] Unit tests added for TOTP verification

## Screenshots
![2FA Setup Screen](url-to-screenshot)

## Related
- Closes #234
- Depends on #230 (merged)
- Security audit: AUDIT-2024-15

## Notes for Reviewers
- The TOTP library choice is discussed in #220
- Backup codes are hashed, not encrypted (intentional)
- Please verify the rate limiting logic in AuthService.ts:45-60
EOF
)"

# Bug fix template
gh pr create --title "fix: resolve payment timeout on slow connections" --body "$(cat <<'EOF'
## Problem
Users on slow connections (>500ms latency) experience payment failures due to API timeout.

## Root Cause
The payment gateway timeout was set to 5 seconds, but slow connections need 10-15 seconds for round-trip.

## Solution
- Increase timeout to 30 seconds for payment endpoints
- Add retry logic with exponential backoff
- Show loading indicator during processing

## Testing
- Tested with network throttling (3G, slow 3G)
- Verified no regression on fast connections
- Added integration test for timeout scenarios

## Fixes
Closes #567
EOF
)"
```

## Why

Good PR descriptions improve the entire development process:

1. **Context for Reviewers**: Understand WHY before reviewing HOW
2. **Documentation**: PR descriptions become part of project history
3. **Self-Review**: Writing forces you to think through your changes
4. **Onboarding**: New team members learn from well-documented PRs
5. **Debugging Aid**: Future investigators find context for changes

Recommended template sections:

```markdown
## Summary
Brief description of what this PR does and why.

## Changes
- Bullet list of significant changes
- Focus on what's notable for reviewers

## Testing
How was this tested? Include manual and automated testing.

## Screenshots/Videos
For UI changes, include before/after screenshots.

## Related
- Links to issues, other PRs, documentation
- Dependencies or blockers

## Notes for Reviewers
- Areas that need extra attention
- Questions or uncertainties
- Non-obvious decisions
```

Store template in `.github/PULL_REQUEST_TEMPLATE.md` to auto-populate.
