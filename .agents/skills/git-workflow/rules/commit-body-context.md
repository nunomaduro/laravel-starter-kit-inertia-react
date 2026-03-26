---
title: Commit Body for Context
category: commit
priority: critical
tags: [commits, documentation, context, motivation]
related: [commit-meaningful-subject, commit-references]
---

# Commit Body for Context

Use the commit body to explain the motivation behind changes and provide additional context that isn't obvious from the code.

## Bad Example

```bash
# No body when context is needed
git commit -m "fix: change timeout from 30s to 120s"
# Why? Was 30s too short? For what operation?

# Body that just repeats the subject
git commit -m "fix: update user validation

Updated the user validation."

# Body describing obvious code changes
git commit -m "refactor: rename variable

Changed 'x' to 'count' on line 45.
Changed 'y' to 'total' on line 46.
Changed 'z' to 'average' on line 47."

# No blank line between subject and body
git commit -m "feat: add caching layer
This improves performance significantly."
```

## Good Example

```bash
# Explain the WHY, not just the WHAT
git commit -m "fix: increase API timeout from 30s to 120s

Large file uploads were failing silently because the
default 30-second timeout was too short. Analysis showed
uploads over 10MB consistently need 60-90 seconds.

Setting timeout to 120s provides buffer for network variance.

Fixes #892"

# Provide context for non-obvious decisions
git commit -m "feat: implement custom retry logic instead of using library

The popular retry-lib package has a memory leak in v2.x
(see github.com/retry-lib/issues/234) that affects long-running
processes. Our custom implementation:

- Uses exponential backoff with jitter
- Respects Retry-After headers
- Has zero dependencies

Will revisit when retry-lib v3 is stable."

# Document trade-offs and alternatives considered
git commit -m "perf: use Redis for session storage

Benchmarks showed 40% latency reduction vs. PostgreSQL
for session lookups. Considered alternatives:

- Memcached: Faster but no persistence
- In-memory: Doesn't scale across instances

Redis provides good balance of speed and durability.

See: docs/adr/005-session-storage.md"
```

## Why

A well-written commit body is invaluable:

1. **Future Context**: Six months later, you'll forget why you made certain decisions
2. **Code Review Aid**: Reviewers understand intent without asking questions
3. **Onboarding Help**: New team members learn project history and reasoning
4. **Debugging Support**: Understanding past decisions helps when investigating issues
5. **Documentation**: Commit messages are searchable, permanent documentation

What to include in the body:
- **Motivation**: Why was this change necessary?
- **Approach**: Why this solution over alternatives?
- **Trade-offs**: What compromises were made?
- **Side Effects**: Any non-obvious impacts?
- **References**: Links to issues, docs, or discussions

Format guidelines:
- Blank line between subject and body
- Wrap at 72 characters
- Use bullet points for lists
- Include relevant links
