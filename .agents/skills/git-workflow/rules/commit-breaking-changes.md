---
title: Documenting Breaking Changes
category: commit
priority: critical
tags: [commits, breaking-changes, semver, migration]
related: [commit-conventional-format, commit-body-context]
---

# Documenting Breaking Changes

Clearly mark and document breaking changes that require consumers to modify their code or configuration.

## Bad Example

```bash
# No indication of breaking change
git commit -m "refactor: update API response format"
# Consumers have no warning their code will break

# Buried breaking change
git commit -m "feat: add new features and improvements"
# Breaking changes hidden among other updates

# Vague breaking change notice
git commit -m "fix: change function signature (BREAKING)"
# What changed? What should consumers do?

# Breaking change in patch-level commit
git commit -m "fix: rename getUserById to fetchUser"
# This is not a fix, it's a breaking change
```

## Good Example

```bash
# Conventional commit with breaking change indicator
git commit -m "feat(api)!: change response format to JSON:API spec"

# Breaking change with detailed footer
git commit -m "feat(auth)!: require API key for all endpoints

BREAKING CHANGE: All API endpoints now require authentication.
Previously, read-only endpoints were public.

Migration guide:
1. Generate an API key in the dashboard
2. Add 'Authorization: Bearer <key>' header to all requests
3. Update rate limit expectations (authenticated: 1000/min)

See: docs/migration/v3-auth.md"

# Multiple breaking changes documented
git commit -m "refactor(core)!: modernize configuration system

BREAKING CHANGE: Configuration file format changed from INI to YAML.

Before (config.ini):
  [database]
  host=localhost
  port=5432

After (config.yaml):
  database:
    host: localhost
    port: 5432

BREAKING CHANGE: Environment variable prefix changed from APP_ to MYAPP_.

Run 'npx migrate-config' to automatically convert your configuration."

# Deprecation notice (warning before breaking)
git commit -m "feat: add new validation API, deprecate old one

The validateInput() function is deprecated and will be removed in v4.0.
Use the new Validator class instead.

Deprecated:
  validateInput(data, rules)

New:
  new Validator(rules).validate(data)

See: docs/migration/validation.md"
```

## Why

Proper breaking change documentation is essential:

1. **Semantic Versioning**: Breaking changes trigger major version bumps
2. **Changelog Generation**: Tools extract BREAKING CHANGE footers automatically
3. **Consumer Awareness**: Developers know to check migration guides before upgrading
4. **Upgrade Planning**: Teams can schedule time for necessary code changes
5. **Trust**: Clear communication builds confidence in your library/API

What constitutes a breaking change:
- Removing or renaming public API
- Changing function signatures (parameters, return types)
- Changing default behavior
- Removing or renaming configuration options
- Changing data formats (API responses, file formats)
- Increasing minimum dependency versions
- Changing error types or codes

Best practices:
- Use `!` after type/scope for conventional commits
- Include `BREAKING CHANGE:` in the footer
- Provide migration instructions
- Link to detailed migration documentation
- Consider deprecation warnings before removal
- Group breaking changes in dedicated releases when possible
