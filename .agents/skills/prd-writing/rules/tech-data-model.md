---
title: Document the Data Model and Relationships
impact: MEDIUM
impactDescription: "Bridges product requirements and database design"
tags: technical, data-model, database, entities, relationships
---

## Document the Data Model and Relationships

**Impact: MEDIUM (Bridges product requirements and database design)**

Include a high-level data model showing the key entities, their relationships, and important attributes. This is not a full schema — it's enough for engineering to understand the data structure before implementation.

## Incorrect

```markdown
<!-- Bad: no data model or too implementation-specific -->
## Technical Details
We need a database.

<!-- Or the opposite extreme: -->
## Database Schema
CREATE TABLE short_urls (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  destination_url VARCHAR(2048) NOT NULL,
  ...40 more columns...
);
```

**Problems:**
- "We need a database" tells engineering nothing
- Full SQL DDL is too prescriptive — that's implementation, not requirements
- No relationships shown — how do entities connect?
- Engineer can't see the big picture

## Correct

````markdown
<!-- Good: high-level model with relationships and key fields -->
## Data Model

### Entity Relationship

```
User (1) ──→ (N) ShortUrl (1) ──→ (N) GeneratedLink (1) ──→ (N) LinkClick
```

### Entities

**User**
- name, email, password, is_admin
- Has many ShortUrls

**ShortUrl**
- title, destination_url, is_active
- SEO fields: seo_title, seo_description, seo_image
- default_expiry_days (1-365, optional)
- Belongs to User, has many GeneratedLinks

**GeneratedLink**
- slug (unique, 4-10 chars alphanumeric)
- label, expires_at, is_active
- SEO overrides: seo_title, seo_description, seo_image
- Belongs to ShortUrl, has many LinkClicks

**LinkClick**
- ip_address, user_agent, referer
- device_type, browser, os, country, city
- clicked_at timestamp
- Belongs to GeneratedLink

### Key Behaviors
- Deleting a ShortUrl cascades to GeneratedLinks and LinkClicks
- GeneratedLink inherits SEO from parent ShortUrl if not overridden
- Slug generation: 6 chars default, retry with longer length on collision
````

**Benefits:**
- Engineering sees the full picture in 30 seconds
- Relationship diagram prevents missing foreign keys
- Key behaviors catch cascade and inheritance logic early
- Detailed enough to start, not so detailed it constrains

## Why

1. **Shared understanding**: Product and engineering agree on the data shape
2. **Reveals complexity**: Relationships expose features that seem simple but aren't
3. **Guides estimation**: More entities = more work; relationships add complexity
4. **Prevents rework**: Catching a missing relationship in the PRD is cheaper than in code
