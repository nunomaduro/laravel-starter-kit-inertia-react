---
title: Identify Third-Party Integrations
impact: MEDIUM
impactDescription: "External dependencies introduce risk and cost"
tags: technical, integrations, third-party, external-services
---

## Identify Third-Party Integrations

**Impact: MEDIUM (External dependencies introduce risk and cost)**

List all third-party services, APIs, and libraries the feature depends on. For each, document the purpose, cost, rate limits, and what happens if it's unavailable.

## Incorrect

```markdown
<!-- Bad: undocumented external dependencies -->
## Technical Notes
We'll use some API for geolocation.
We might need an image processing library.
```

**Problems:**
- "Some API" — which one? What does it cost?
- "Might need" — is it required or not?
- No fallback plan if the API goes down
- No rate limit awareness — could fail silently in production

## Correct

```markdown
<!-- Good: documented with fallbacks -->
## Third-Party Integrations

| Service | Purpose | Tier | Rate Limit | Cost | Fallback |
|---------|---------|------|-----------|------|----------|
| **ip-api.com** | Geolocation (country/city from IP) | Free | 45 req/min | $0 | Record click without geo data |
| **Intervention Image** | OG image resize/crop to 1200x630 | Composer package | N/A | $0 | Require pre-sized images |

### ip-api.com Details
- **Used for:** Resolving visitor IP → country and city in click tracking
- **Called from:** RecordLinkClick queued job (async, not blocking redirect)
- **Timeout:** 3 seconds, best-effort
- **Failure handling:** Log warning, save click without geo fields
- **Upgrade path:** If free tier limit hit, paid plan at $13/month for 5K req/min

### Intervention Image Details
- **Used for:** Resizing uploaded OG images to 1200x630 (JPEG, 85% quality)
- **Called from:** ImageService on file upload
- **Failure handling:** Reject upload with validation error
- **Dependency:** Requires PHP GD or Imagick extension

### No Integration Required
- **Email:** Not needed for MVP (no notification system)
- **CDN:** Not needed (single-server deployment)
- **Payment:** No paid features in this release
```

**Benefits:**
- Budget is known before development starts
- Rate limits inform architecture (async vs sync)
- Fallback plans prevent production outages
- "No integration required" prevents unnecessary work

## Why

1. **Cost visibility**: Stakeholders approve budget before development
2. **Risk management**: Every external call is a potential failure point
3. **Architecture decisions**: Rate limits drive async/queue patterns
4. **Deployment readiness**: Required extensions and API keys are known upfront
