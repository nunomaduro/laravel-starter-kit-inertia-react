---
title: Code Review Best Practices
category: collaboration
priority: medium
tags: [collaboration, code-review, feedback, quality]
related: [pr-reviewers, pr-description-template, pr-small-focused]
---

# Code Review Best Practices

Conduct thorough, constructive code reviews that improve code quality and share knowledge.

## Bad Example

```bash
# Rubber-stamp approval
gh pr review 123 --approve --body "LGTM"
# No actual review conducted

# Harsh, unconstructive feedback
gh pr review 123 --request-changes --body "
This code is terrible.
Why would you do it this way?
This is completely wrong."

# Nitpicking style over substance
# 50 comments about spacing and naming
# 0 comments about logic or architecture

# Delayed reviews blocking progress
# PR sits for 2 weeks without any review
# Team velocity suffers

# Review scope creep
gh pr review 123 --body "
While you're here, can you also:
- Refactor the entire module
- Add 100% test coverage
- Update all documentation"
```

## Good Example

```bash
# Thorough review with specific feedback
gh pr review 123 --comment --body "
Overall this looks good! A few suggestions:

**Logic:**
- The retry logic on line 45 could cause infinite loops if the server keeps returning 503

**Security:**
- User input on line 78 should be sanitized before SQL query

**Testing:**
- Consider adding a test case for empty input

**Minor:**
- Typo on line 23: 'recieve' -> 'receive'"

# Constructive change request
gh pr review 123 --request-changes --body "
Thanks for this implementation! I found one issue that should be addressed before merging:

The password comparison on line 56 uses string equality (`==`) instead of a timing-safe comparison, which could be vulnerable to timing attacks.

Suggested fix:
\`\`\`python
import hmac
if hmac.compare_digest(stored_hash, input_hash):
\`\`\`

Let me know if you'd like to discuss this approach!"

# Approval with minor suggestions
gh pr review 123 --approve --body "
Looks great! Nice clean implementation.

Optional suggestions (not blocking):
- Line 34: Could use early return for readability
- Consider adding a docstring to the main function

Approving as-is - feel free to address these in a follow-up PR if you agree."
```

## Why

Effective code review is essential for team success:

1. **Quality Assurance**: Catch bugs before they reach production
2. **Knowledge Sharing**: Spread understanding of the codebase
3. **Mentorship**: Junior developers learn from feedback
4. **Consistency**: Maintain coding standards across the team
5. **Security**: Fresh eyes catch vulnerabilities

Review checklist:

**Functionality**
- [ ] Does the code do what it's supposed to do?
- [ ] Are edge cases handled?
- [ ] Is error handling appropriate?

**Security**
- [ ] Input validation present?
- [ ] No sensitive data exposed?
- [ ] Authentication/authorization correct?

**Design**
- [ ] Is the approach appropriate?
- [ ] Are there simpler solutions?
- [ ] Does it follow existing patterns?

**Testing**
- [ ] Are tests present and meaningful?
- [ ] Do tests cover edge cases?
- [ ] Are tests maintainable?

**Maintainability**
- [ ] Is the code readable?
- [ ] Are names descriptive?
- [ ] Is it properly documented?

Review etiquette:
- Review promptly (within 24 hours)
- Be constructive and specific
- Explain the "why" behind suggestions
- Distinguish blocking issues from suggestions
- Ask questions instead of making demands
- Praise good code, not just critique issues
- Remember: review the code, not the person
