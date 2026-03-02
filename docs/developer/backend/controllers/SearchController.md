# SearchController

## Purpose

Provides a global search API endpoint that searches across multiple searchable models and returns unified, grouped results.

## Location

`app/Http/Controllers/SearchController.php`

## Methods

| Method | HTTP Method | Route | Purpose |
|--------|------------|-------|---------|
| `__invoke` | GET | `/search` | Search across all searchable models |

## Routes

- `search`: `GET /search` - Global search endpoint (requires auth, verified, tenant middleware)

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | Yes | Search query string |
| `type` | string | No | Filter by category: `users`, `posts`, `help_articles`, `changelog_entries` |

### Response Format

```json
{
  "users": [
    { "id": 1, "title": "User Name", "subtitle": "user@example.com", "url": "/users/1", "type": "user" }
  ],
  "posts": [
    { "id": 1, "title": "Post Title", "subtitle": "Excerpt...", "url": "/blog/post-slug", "type": "post" }
  ],
  "help_articles": [
    { "id": 1, "title": "Article Title", "subtitle": "Excerpt...", "url": "/help/article-slug", "type": "help_article" }
  ],
  "changelog_entries": [
    { "id": 1, "title": "Entry Title", "subtitle": "v1.0.0", "url": "/changelog", "type": "changelog_entry" }
  ]
}
```

## Search Behavior

- **Users**: Scoped to current organization members via tenant context
- **Posts**: Only published posts within the current organization (respects `BelongsToOrganization` scope)
- **Help Articles**: Only published articles within the current organization
- **Changelog Entries**: Only published entries within the current organization
- **Feature Flags**: Posts require `blog` feature, help articles require `help` feature, changelog entries require `changelog` feature
- **Limits**: Maximum 5 results per category, 20 total results

## Validation

No Form Request used — the endpoint accepts a simple `q` query string parameter.

## Related Components

- **Models**: `User`, `Post`, `HelpArticle`, `ChangelogEntry` (all use Laravel Scout `Searchable` trait)
- **Services**: `TenantContext` (organization scoping for users), `FeatureHelper` (feature flag checks)
- **Routes**: `search` (defined in routes/web.php)
