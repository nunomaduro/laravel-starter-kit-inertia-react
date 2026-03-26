---
title: Robots.txt Configuration
impact: HIGH
impactDescription: "Controls crawl budget and blocks sensitive paths"
tags: robots-txt, crawling, technical-seo
---

## Robots.txt Configuration

**Impact: HIGH (Controls crawl budget and blocks sensitive paths)**

Robots.txt controls which pages search engine crawlers can access. A misconfigured robots.txt can either block important content from being indexed or waste crawl budget on irrelevant pages. It must always reference your XML sitemap to aid discovery.

## Incorrect

```txt
# ❌ Bad: blocks CSS/JS (breaks rendering), no sitemap, too open

User-agent: *
Disallow: /css/
Disallow: /js/
Disallow: /images/

# No sitemap reference
# No blocking of admin, API, or internal paths
```

```txt
# ❌ Bad: blocks everything on staging (but staging is publicly accessible)

User-agent: *
Disallow: /

# This only prevents indexing — it does NOT prevent access.
# If staging is public, Google can still find and cache URLs via links.
```

**Problems:**
- Blocking `/css/` and `/js/` prevents Google from rendering the page, leading to poor indexing of JavaScript-heavy sites
- Blocking `/images/` removes images from Google Image Search traffic
- No `Sitemap:` directive makes it harder for crawlers to discover all pages
- Not blocking admin, API, or staging paths wastes crawl budget and risks exposing internal routes
- Using robots.txt alone to "hide" staging does not prevent access — it only prevents crawling

## Correct

```txt
# ✅ Good: robots.txt for production site

# Default rules for all crawlers
User-agent: *

# Block admin and internal paths
Disallow: /admin/
Disallow: /api/
Disallow: /internal/

# Block search result pages (thin/duplicate content)
Disallow: /search
Disallow: /*?s=

# Block user-specific pages
Disallow: /account/
Disallow: /cart
Disallow: /checkout

# Block duplicate filtered/sorted views
Disallow: /*?sort=
Disallow: /*?filter=

# Allow all static assets (CSS, JS, images)
Allow: /css/
Allow: /js/
Allow: /images/
Allow: /fonts/

# Sitemap reference (always absolute URL)
Sitemap: https://example.com/sitemap.xml
```

```txt
# ✅ Good: block AI training crawlers while allowing search engines

User-agent: GPTBot
Disallow: /

User-agent: ChatGPT-User
Disallow: /

User-agent: CCBot
Disallow: /

# Allow search engine crawlers
User-agent: Googlebot
Allow: /

User-agent: Bingbot
Allow: /

User-agent: *
Disallow: /admin/
Disallow: /api/

Sitemap: https://example.com/sitemap.xml
```

```php
// ✅ Laravel: dynamic robots.txt via route (routes/web.php)
use Illuminate\Support\Facades\App;

Route::get('/robots.txt', function () {
    $content = App::environment('production')
        ? view('seo.robots-production')->render()
        : "User-agent: *\nDisallow: /";

    return response($content, 200)
        ->header('Content-Type', 'text/plain');
});
```

```php
{{-- ✅ resources/views/seo/robots-production.blade.php --}}
User-agent: *
Disallow: /admin/
Disallow: /api/
Disallow: /account/
Disallow: /cart
Disallow: /checkout
Disallow: /search
Disallow: /*?sort=
Disallow: /*?filter=

Allow: /css/
Allow: /js/
Allow: /images/

Sitemap: {{ url('/sitemap.xml') }}
```

**Benefits:**
- Allowing CSS, JS, and images ensures Google can render pages accurately for indexing
- Blocking admin, API, and internal paths protects crawl budget and keeps sensitive routes out of search results
- Blocking search and filtered pages prevents thin or duplicate content from being indexed
- Sitemap reference helps crawlers discover all important pages efficiently
- Environment-aware generation prevents production rules from accidentally blocking staging, and vice versa

Reference: [Google's Robots.txt Specification](https://developers.google.com/search/docs/crawling-indexing/robots/intro)
