---
title: Issue and PR References in Commits
category: commit
priority: critical
tags: [commits, traceability, issues, automation]
related: [commit-body-context, commit-conventional-format]
---

# Issue and PR References in Commits

Link commits to relevant issues, pull requests, and external resources for traceability.

## Bad Example

```bash
# No reference to the issue being fixed
git commit -m "fix: resolve login timeout issue"
# Which issue? Where was it reported?

# Vague references
git commit -m "fix: address the bug from last week's meeting"

# Reference in wrong format (won't auto-link)
git commit -m "fix: login issue (issue 123)"

# Reference without context
git commit -m "fix: #456"
# What does #456 refer to?
```

## Good Example

```bash
# GitHub/GitLab auto-linking keywords
git commit -m "fix: resolve race condition in auth flow

Fixes #234"

git commit -m "feat: add bulk export functionality

Implements #567
Closes #568"

# Multiple references with context
git commit -m "fix: handle edge case in payment processing

The payment gateway returns different error codes for
the same failure type depending on the merchant account.

Fixes #891
Related to #445
See also: payment-gateway/docs/error-codes"

# External references
git commit -m "security: patch XSS vulnerability in comments

Apply sanitization to user-generated content before rendering.

Fixes #234
CVE-YYYY-NNNNN
See: https://owasp.org/xss-prevention"

# Co-author attribution
git commit -m "feat: implement new search algorithm

Based on discussion in #123 and design doc.

Co-authored-by: Jane Doe <jane@example.com>
Co-authored-by: John Smith <john@example.com>"
```

## Why

References create valuable connections:

1. **Traceability**: Link code changes to requirements, bugs, and discussions
2. **Auto-Linking**: GitHub/GitLab automatically link #123 to issue/PR 123
3. **Auto-Closing**: Keywords like "Fixes #123" automatically close issues when merged
4. **Context Preservation**: Future developers can find the full discussion
5. **Audit Trail**: Important for compliance and debugging

Common keywords that auto-close issues:
- `Fixes #123`
- `Closes #123`
- `Resolves #123`

Keywords for reference without closing:
- `Related to #123`
- `See #123`
- `Part of #123`
- `Refs #123`

Best practices:
- Always reference the issue being fixed
- Include CVE numbers for security fixes
- Link to relevant documentation or ADRs
- Credit co-authors for pair programming
- Reference external bug trackers if applicable
