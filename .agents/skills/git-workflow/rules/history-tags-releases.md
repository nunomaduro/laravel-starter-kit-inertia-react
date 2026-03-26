---
title: Tags and Releases
category: history
priority: medium
tags: [history, tags, releases, versioning, semver]
related: [branch-release-strategy, commit-breaking-changes]
---

# Tags and Releases

Use annotated tags to mark releases and maintain a clear versioning history.

## Bad Example

```bash
# Lightweight tags with no context
git tag v1.0.0
git push --tags
# No message, no author info, no date

# Inconsistent tag naming
git tag release-1.0
git tag v1.1.0
git tag 1.2
git tag version_1.3.0
# Impossible to sort or automate

# Tagging random commits
git tag v2.0.0  # Tags current HEAD which might be WIP

# Forgetting to push tags
git tag -a v1.0.0 -m "Release 1.0.0"
# But never pushed to remote

# Moving tags after push
git tag -f v1.0.0 HEAD
git push --force --tags
# Breaks anyone who already pulled
```

## Good Example

```bash
# Create annotated tag with message
git tag -a v1.0.0 -m "Release version 1.0.0

Features:
- User authentication
- Dashboard widgets
- API rate limiting

Fixes:
- Resolved login timeout issue
- Fixed memory leak in cache"

# Push specific tag
git push origin v1.0.0

# Push all tags
git push --tags

# Tag a specific commit (for releases)
git tag -a v1.0.0 abc123 -m "Release 1.0.0"

# List tags with messages
git tag -n

# Create GitHub release from tag
gh release create v1.0.0 \
  --title "Version 1.0.0" \
  --notes "## What's New
- User authentication system
- Dashboard widgets
- API rate limiting

## Bug Fixes
- Login timeout issue resolved
- Memory leak in cache fixed

## Breaking Changes
- API v1 deprecated, use v2"

# Create release with assets
gh release create v1.0.0 \
  --title "Version 1.0.0" \
  --notes-file CHANGELOG.md \
  dist/app-1.0.0.zip \
  dist/app-1.0.0.tar.gz
```

## Why

Proper tagging enables release management:

1. **Version Identification**: Clear mapping between code and releases
2. **Deployment Reference**: CI/CD can deploy specific tags
3. **Changelog Generation**: Tools extract release notes from tags
4. **Rollback Target**: Easy to identify and revert to previous versions
5. **Communication**: Users and team know what's deployed

Semantic Versioning (SemVer):
```
MAJOR.MINOR.PATCH

v1.0.0  - Initial release
v1.1.0  - New feature (backward compatible)
v1.1.1  - Bug fix (backward compatible)
v2.0.0  - Breaking change
```

Pre-release versions:
```bash
v1.0.0-alpha.1   # Alpha release
v1.0.0-beta.1    # Beta release
v1.0.0-rc.1      # Release candidate
```

Tag commands reference:
```bash
# Create annotated tag
git tag -a v1.0.0 -m "Message"

# Create tag on specific commit
git tag -a v1.0.0 <commit-sha> -m "Message"

# List all tags
git tag -l

# List tags matching pattern
git tag -l "v1.*"

# Show tag details
git show v1.0.0

# Delete local tag
git tag -d v1.0.0

# Delete remote tag
git push origin --delete v1.0.0

# Checkout specific tag
git checkout v1.0.0
```

Best practices:
- Always use annotated tags for releases (`-a` flag)
- Follow semantic versioning
- Include release notes in tag message
- Create GitHub/GitLab releases for visibility
- Never move or delete published tags
