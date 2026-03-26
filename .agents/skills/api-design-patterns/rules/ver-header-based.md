---
title: Version via Accept Header
impact: HIGH
impactDescription: "Clean URLs with content negotiation-based versioning for API evolution"
tags: versioning, headers, content-negotiation
---

## Version via Accept Header

**Impact: HIGH (Clean URLs with content negotiation-based versioning for API evolution)**

Header-based versioning uses the `Accept` header to specify the desired API version, keeping URLs clean and resource-centric. This approach aligns with HTTP content negotiation semantics and is preferred when URL aesthetics matter or when the same resource should be accessible across versions without changing its canonical URL.

## Incorrect

```http
# No version information at all — client gets whatever the current version is
GET /api/users/1
Accept: application/json
```

```http
# Or: custom non-standard header
GET /api/users/1
X-API-Version: 2
```

**Problems:**
- No standard mechanism to request a specific version of the response format
- Custom headers are not part of HTTP content negotiation and may be stripped by proxies
- Clients have no guarantee about the response shape they will receive
- `X-` prefixed headers are deprecated by RFC 6648 and signal non-standard behavior

## Correct

```http
# Request version 1
GET /api/users/1
Accept: application/vnd.myapi.v1+json
```

```json
{
  "id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com"
}
```

```http
# Request version 2
GET /api/users/1
Accept: application/vnd.myapi.v2+json
```

```json
{
  "id": 1,
  "full_name": "Jane Smith",
  "email_address": "jane@example.com",
  "profile": {
    "avatar_url": "https://cdn.example.com/avatars/1.jpg"
  }
}
```

```http
# Server responds with matching Content-Type
HTTP/1.1 200 OK
Content-Type: application/vnd.myapi.v2+json
```

| Aspect | URL Path (`/v1/`) | Accept Header |
|---|---|---|
| Visibility | Version in every URL | Hidden in headers |
| Caching | Simple (URL-based) | Requires `Vary: Accept` |
| Browser testing | Easy (type URL) | Needs tool (curl, Postman) |
| URL cleanliness | Extra path segment | Clean resource URLs |
| HTTP semantics | Convention-based | Proper content negotiation |

**Benefits:**
- URLs remain clean and resource-focused — `/api/users/1` is the canonical identifier
- Follows HTTP content negotiation standards (`Accept` / `Content-Type`)
- Server can default to the latest version when no version header is sent
- Multiple representations of the same resource at the same URL, which aligns with REST principles

Reference: [GitHub API - Media Types](https://docs.github.com/en/rest/using-the-rest-api/getting-started-with-the-rest-api#accept)
