---
title: Canonical URL Implementation
impact: CRITICAL
impactDescription: "Prevents duplicate content penalties"
tags: canonical, duplicate-content, technical-seo
---

## Canonical URL Implementation

**Impact: CRITICAL (Prevents duplicate content penalties)**

Canonical tags tell search engines which version of a URL is the "master" copy. Without them, duplicate content from www/non-www variations, query parameters, and pagination fragments dilutes link equity and can trigger ranking penalties.

## Incorrect

```html
<!-- ❌ Bad: missing canonical on a page accessible via multiple URLs -->
<!-- This page is reachable at:
     https://example.com/shoes
     https://example.com/shoes?color=red
     https://example.com/shoes?color=red&sort=price
     https://www.example.com/shoes
     http://example.com/shoes
-->
<head>
  <title>Running Shoes | ShoeStore</title>
  <!-- No canonical tag — search engines must guess which URL to index -->
</head>
```

```html
<!-- ❌ Bad: wrong canonical on paginated pages -->
<!-- Page 3 of product listing points canonical to page 1 -->
<head>
  <title>Running Shoes - Page 3 | ShoeStore</title>
  <link rel="canonical" href="https://example.com/shoes" />
  <!-- This tells Google page 3 is a duplicate of page 1 -->
</head>
```

```html
<!-- ❌ Bad: relative canonical URL -->
<head>
  <link rel="canonical" href="/shoes" />
  <!-- Relative URLs can be misinterpreted by crawlers -->
</head>
```

**Problems:**
- Without a canonical tag, Google indexes multiple URL variations and splits ranking signals
- Pointing paginated pages to page 1 tells Google to ignore pages 2+ entirely, de-indexing that content
- Relative canonical URLs may resolve incorrectly depending on the base URL context
- Query parameter variations create potentially unlimited duplicate URLs

## Correct

```html
<!-- ✅ Good: self-referencing canonical on every page -->
<head>
  <title>Running Shoes | ShoeStore</title>
  <link rel="canonical" href="https://example.com/shoes" />
</head>

<!-- ✅ Good: filtered page canonicalizes to the base (non-filtered) version -->
<!-- URL: https://example.com/shoes?color=red&sort=price -->
<head>
  <title>Red Running Shoes | ShoeStore</title>
  <link rel="canonical" href="https://example.com/shoes" />
</head>

<!-- ✅ Good: paginated page uses self-referencing canonical -->
<!-- URL: https://example.com/shoes?page=3 -->
<head>
  <title>Running Shoes - Page 3 | ShoeStore</title>
  <link rel="canonical" href="https://example.com/shoes?page=3" />
</head>
```

```tsx
// ✅ Inertia.js: canonical URL with <Head> component
import { Head } from '@inertiajs/react';

interface CategoryPageProps {
  category: string;
  currentPage: number;
}

export default function CategoryPage({ category, currentPage }: CategoryPageProps) {
  const baseUrl = "https://example.com";

  // Paginated pages get self-referencing canonical
  // Filter/sort params are excluded from canonical
  const canonical = currentPage > 1
    ? `${baseUrl}/${category}?page=${currentPage}`
    : `${baseUrl}/${category}`;

  return (
    <>
      <Head>
        <title>{`${category} | ShoeStore`}</title>
        <link rel="canonical" href={canonical} />
      </Head>
      <main>
        <h1>{category}</h1>
        {/* Product listing */}
      </main>
    </>
  );
}
```

```php
{{-- ✅ Laravel: canonical URL middleware + Blade directive --}}

// app/Http/Middleware/SetCanonicalUrl.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCanonicalUrl
{
    public function handle(Request $request, Closure $next)
    {
        // Strip tracking params, keep meaningful ones like page
        $allowed = ['page'];
        $params = collect($request->query())
            ->only($allowed)
            ->filter()
            ->all();

        $canonical = $params
            ? $request->url() . '?' . http_build_query($params)
            : $request->url();

        // Force HTTPS and non-www
        $canonical = preg_replace('/^http:/', 'https:', $canonical);
        $canonical = preg_replace('/\/\/www\./', '//', $canonical);

        view()->share('canonical', $canonical);

        return $next($request);
    }
}

{{-- layouts/app.blade.php --}}
<head>
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}" />
</head>
```

**Benefits:**
- Self-referencing canonicals on every page prevent ambiguity for search engine crawlers
- Stripping tracking and filter query parameters consolidates link equity to the main URL
- Paginated pages retain their own canonical so their content remains indexed
- Middleware-based approach ensures consistent canonical URLs across the entire site
- Forcing HTTPS and non-www in the canonical prevents protocol and subdomain duplication

Reference: [Google's Canonical Documentation](https://developers.google.com/search/docs/crawling-indexing/canonicalization)
