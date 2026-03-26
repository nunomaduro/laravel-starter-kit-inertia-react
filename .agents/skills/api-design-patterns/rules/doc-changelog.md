---
title: API Changelog
impact: MEDIUM
impactDescription: "Prevents production outages from undiscovered breaking changes"
tags: documentation, changelog, versioning, breaking-changes
---

## API Changelog

**Impact: MEDIUM (Prevents production outages from undiscovered breaking changes)**

Without a changelog, API consumers discover breaking changes when their production integrations fail. A dated, categorized changelog gives consumers advance notice of changes so they can adapt their code before it breaks.

## Incorrect

```
// ❌ No changelog at all

// Consumers discover changes through:
// - Production errors after a deploy
// - Slack messages: "Did something change with /users?"
// - Trial and error comparing old vs new behavior
// - Reading git commit history (if the repo is even public)

// ❌ Or: a vague changelog with no useful detail

## Updates
- Fixed some bugs
- Improved performance
- Updated user endpoint
```

**Problems:**
- Breaking changes surprise consumers in production
- No way to know when a field was deprecated or removed
- Consumers cannot plan migration timelines for breaking changes
- Support teams fielding questions that a changelog would answer

## Correct

### Keep a Changelog Format

```markdown
# API Changelog

All notable changes to the API are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/).

## [1.5.0] - 2024-03-15

### Added
- `GET /users` now supports `?sort=created_at` and `?sort=last_login_at`
  query parameters for sorting results
- New `phone_verified` boolean field on User resource
- `POST /users/bulk` endpoint for creating up to 100 users in one request

### Changed
- `GET /users` default `per_page` changed from 50 to 20 for better
  performance. Use `?per_page=50` to restore previous behavior.

### Deprecated
- `GET /users` query parameter `?order` is deprecated in favor of `?sort`.
  `?order` will be removed in v2.0.0 (scheduled for 2024-09-01).
- User field `username` is deprecated. Use `email` as the unique identifier.
  Field will be removed in v2.0.0.

## [1.4.2] - 2024-02-28

### Fixed
- `PATCH /users/:id` now correctly returns 422 instead of 500 when email
  format is invalid
- Pagination `total_pages` calculation was off by one for exact multiples

## [1.4.1] - 2024-02-10

### Security
- Rate limiting on `POST /auth/login` reduced from 60 to 10 requests per
  minute to mitigate brute-force attacks

## [1.4.0] - 2024-01-20

### Added
- `GET /users/:id/activity` endpoint returning recent account activity
- Support for `fields` query parameter on all GET endpoints (sparse
  fieldsets)
- `X-Request-Id` response header on all endpoints

### Removed
- **BREAKING:** `GET /users/search` endpoint removed. Use
  `GET /users?q=search_term` instead. See [migration guide](https://docs.example.com/migration/search).

## [1.3.0] - 2024-01-05

### Added
- `Accept-Encoding: br` (Brotli) compression support
- `suspend` and `reactivate` actions on `POST /users/:id/actions`

### Changed
- Error responses now include `request_id` field in the error envelope
```

### Changelog Entry Categories

```
Added       — New endpoints, fields, query parameters, features
Changed     — Non-breaking changes to existing behavior
Deprecated  — Features that will be removed in a future version
Removed     — BREAKING: features removed in this version
Fixed       — Bug fixes
Security    — Security-related changes
```

### Communicating Breaking Changes

```http
// ✅ Deprecation header on responses for deprecated features
HTTP/1.1 200 OK
Deprecation: Sun, 01 Sep 2024 00:00:00 GMT
Sunset: Sun, 01 Sep 2024 00:00:00 GMT
Link: <https://docs.example.com/migration/search>; rel="deprecation"
```

```json
// ✅ Deprecation notice in response body
{
  "data": { ... },
  "meta": {
    "warnings": [
      {
        "code": "DEPRECATED_FIELD",
        "message": "Field 'username' is deprecated and will be removed on 2024-09-01. Use 'email' instead.",
        "see": "https://docs.example.com/migration/username"
      }
    ]
  }
}
```

### Best Practices

```
✅ Date every entry (ISO 8601: YYYY-MM-DD)
✅ Link changelog from your API documentation landing page
✅ Tag breaking changes with "BREAKING:" prefix
✅ Include migration guides for breaking changes
✅ Notify consumers via email/webhook before breaking changes
✅ Give at least 6 months notice before removing deprecated features
✅ Version the changelog alongside the API (same repo, same release)
```

**Benefits:**
- Consumers can review changes before updating their integrations
- Breaking changes come with advance notice and migration guides
- Deprecation timelines let consumers plan upgrades
- Reduces support burden by answering "what changed?" proactively

Reference: [Keep a Changelog](https://keepachangelog.com/)
