# SEO Best Practices - Complete Reference

**Version:** 1.2.0
**Organization:** Agent Skills Contributors
**Date:** March 2026
**License:** MIT

## Abstract

Comprehensive SEO patterns for web applications built with React and Laravel. Contains 31 rules across 8 categories covering Core Web Vitals, technical SEO, on-page optimization, structured data, performance, social sharing, React/SPA SEO, and mobile-first indexing. Supports SEO audit mode with PASS/FAIL checklist output. Each rule includes incorrect and correct code examples with practical HTML, React (Inertia.js), and Laravel implementations.

## How to Audit

When asked to "audit SEO" or "check SEO", run through each rule in this document as a checklist. For each item output **PASS**, **FAIL** (with `file:line` and fix), or **N/A**. End with a summary of pass/fail counts and top 3 priority fixes.

## References

- [Google Search Central](https://developers.google.com/search)
- [web.dev Core Web Vitals](https://web.dev/articles/vitals)
- [Schema.org](https://schema.org/)
- [Open Graph Protocol](https://ogp.me/)
- [Google Rich Results Test](https://search.google.com/test/rich-results)
- [Google PageSpeed Insights](https://pagespeed.web.dev/)
- [Google Search Console](https://search.google.com/search-console)
- [Mobile-First Indexing](https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing)

## Step 1: Detect Project Type

**Always check the project stack before giving advice.** Different stacks need different SEO approaches.

Check `package.json` and project structure:

| Signal | Project Type |
|--------|-------------|
| `@inertiajs/react` in dependencies | Laravel + Inertia + React |
| `resources/views/**/*.blade.php` only (no React) | Laravel Blade (server-rendered) |

**If Laravel Blade:** Apply `tech-`, `onpage-`, `schema-`, `perf-`, `social-`, `mobile-` rules. Meta tags go in Blade layouts. Sitemaps via `spatie/laravel-sitemap`. Skip `spa-` rules — pages are already server-rendered.

**If Laravel + Inertia + React:** Apply all rules. Meta tags via `@inertiaHead` in Blade layout + `<Head>` component from `@inertiajs/react` in React pages. For SSR, create `resources/js/ssr.jsx` using `createServer` from `@inertiajs/react/server`, add `ssr: 'resources/js/ssr.jsx'` to Vite config, build with `vite build && vite build --ssr`, and run `php artisan inertia:start-ssr`. Use `head-key` attribute on meta tags to prevent duplicates between layout and page. Focus on `schema-`, `social-`, and `perf-` rules.

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Core Web Vitals (cwv)

**Impact:** CRITICAL
**Description:** Google uses Core Web Vitals (LCP, INP, CLS) as ranking signals. Pages that fail these thresholds rank lower and provide poor user experience. Optimizing CWV is the highest-impact technical SEO work.

## 2. Technical SEO (tech)

**Impact:** CRITICAL
**Description:** Foundational SEO elements that search engines need to discover, crawl, and index pages correctly. Without proper meta tags, canonical URLs, sitemaps, and robots.txt, content cannot rank regardless of quality.

## 3. On-Page SEO (onpage)

**Impact:** HIGH
**Description:** Content structure and HTML semantics that help search engines understand page topics and relevance. Proper headings, semantic markup, internal linking, and image optimization directly affect rankings.

## 4. Structured Data (schema)

**Impact:** HIGH
**Description:** JSON-LD markup using Schema.org vocabulary that enables rich results in search (star ratings, FAQ accordions, breadcrumb trails, product prices). Structured data does not directly boost rankings but significantly improves click-through rates.

## 5. Performance SEO (perf)

**Impact:** HIGH
**Description:** Page speed and loading optimization that affects both Core Web Vitals scores and user experience. Modern image formats, lazy loading, font strategies, and resource hints reduce load times and improve search ranking.

## 6. Social Sharing (social)

**Impact:** HIGH
**Description:** Open Graph and Twitter Card meta tags that control how pages appear when shared on social media. Proper social meta tags increase click-through rates from social platforms and drive organic traffic.

## 7. React/SPA SEO (spa)

**Impact:** HIGH
**Description:** SEO patterns specific to single-page applications built with React. SPAs require special attention to rendering strategy, meta tag management, and routing to be crawlable by search engines.

## 8. Mobile-First (mobile)

**Impact:** MEDIUM
**Description:** Google uses mobile-first indexing, meaning it primarily crawls and indexes the mobile version of pages. Mobile viewport configuration, content parity, and UX requirements directly affect how pages are indexed and ranked.


---

## Largest Contentful Paint Optimization

**Impact: CRITICAL (Must be under 2.5s (Google ranking signal))**

LCP measures how long it takes for the largest visible element (usually a hero image or heading) to render. Google uses LCP as a direct ranking signal, and pages exceeding 2.5s risk lower search positions and higher bounce rates.

## Incorrect

```html
<!-- ❌ Bad: lazy-loading the hero image, no preload, render-blocking CSS -->
<head>
  <link rel="stylesheet" href="/css/all-styles.css" />
  <link rel="stylesheet" href="/css/animations.css" />
  <link rel="stylesheet" href="/css/third-party-widget.css" />
</head>
<body>
  <section class="hero">
    <img
      src="/images/hero-banner.jpg"
      loading="lazy"
      alt="Welcome to our platform"
    />
  </section>
</body>
```

**Problems:**
- `loading="lazy"` on the hero image delays the LCP element, as the browser defers loading until it enters the viewport
- No `<link rel="preload">` means the browser discovers the image only after parsing the HTML and CSS
- Multiple render-blocking stylesheets delay first render, pushing LCP further out

## Correct

```html
<!-- ✅ Good: preloaded hero image with fetchpriority, non-blocking CSS -->
<head>
  <link
    rel="preload"
    as="image"
    href="/images/hero-banner.webp"
    fetchpriority="high"
  />
  <link rel="stylesheet" href="/css/critical.css" />
  <link
    rel="stylesheet"
    href="/css/non-critical.css"
    media="print"
    onload="this.media='all'"
  />
</head>
<body>
  <section class="hero">
    <img
      src="/images/hero-banner.webp"
      fetchpriority="high"
      width="1200"
      height="600"
      alt="Welcome to our platform"
    />
  </section>
</body>
```

```tsx
// ✅ React: hero image with fetchPriority="high" and no lazy loading
export default function HeroSection() {
  return (
    <section className="hero">
      <img
        src="/images/hero-banner.webp"
        alt="Welcome to our platform"
        width={1200}
        height={600}
        fetchPriority="high"
      />
    </section>
  );
}
```

**Benefits:**
- `fetchpriority="high"` tells the browser to prioritize the hero image over other resources
- `<link rel="preload">` starts fetching the image before the browser encounters the `<img>` tag
- Non-critical CSS is deferred using the `media="print"` trick, unblocking initial render
- WebP format reduces image payload, further improving load time

Reference: [Optimize Largest Contentful Paint](https://web.dev/articles/optimize-lcp)


---

## Interaction to Next Paint Optimization

**Impact: CRITICAL (Must be under 200ms (replaced FID in March 2024))**

INP measures the latency of every click, tap, and keyboard interaction throughout a page visit, reporting the worst interaction. Since March 2024, INP replaced First Input Delay as a Core Web Vital ranking signal, making responsive interactions essential for SEO.

## Incorrect

```tsx
// ❌ Bad: synchronous heavy computation blocks the main thread on click
export default function ProductFilter({ products }: { products: Product[] }) {
  const handleFilter = (category: string) => {
    // Long-running synchronous task blocks UI for 500ms+
    const filtered = products.filter((product) => {
      // Expensive computation per item
      const score = calculateRelevanceScore(product, category);
      const normalized = normalizeAcrossDataset(score, products);
      return normalized > 0.5;
    });

    // DOM update only happens after entire computation finishes
    setFilteredProducts(filtered);
    updateURL(category);
    trackAnalytics("filter", category);
  };

  return (
    <button onClick={() => handleFilter("electronics")}>
      Filter Electronics
    </button>
  );
}
```

**Problems:**
- The entire filtering, normalization, and DOM update runs synchronously, blocking the main thread
- The browser cannot paint the next frame until the handler completes, causing visible lag
- Analytics and URL updates further extend the blocking time after the critical render

## Correct

```tsx
// ✅ Good: break work into chunks and yield to the main thread
export default function ProductFilter({ products }: { products: Product[] }) {
  const [isPending, startTransition] = useTransition();

  const handleFilter = async (category: string) => {
    // Immediately update UI to show pending state
    startTransition(() => {
      setCategory(category);
    });

    // Offload heavy computation to a Web Worker
    const filtered = await new Promise<Product[]>((resolve) => {
      filterWorker.onmessage = (e) => resolve(e.data);
      filterWorker.postMessage({ products, category });
    });

    startTransition(() => {
      setFilteredProducts(filtered);
    });

    // Defer non-critical work
    requestIdleCallback(() => {
      updateURL(category);
      trackAnalytics("filter", category);
    });
  };

  return (
    <button
      onClick={() => handleFilter("electronics")}
      disabled={isPending}
    >
      {isPending ? "Filtering..." : "Filter Electronics"}
    </button>
  );
}
```

```ts
// ✅ Web Worker: filter-worker.ts — runs off the main thread
self.onmessage = (event: MessageEvent) => {
  const { products, category } = event.data;

  const filtered = products.filter((product: Product) => {
    const score = calculateRelevanceScore(product, category);
    const normalized = normalizeAcrossDataset(score, products);
    return normalized > 0.5;
  });

  self.postMessage(filtered);
};
```

```ts
// ✅ Alternative: yield to main thread using scheduler.yield()
async function processInChunks<T>(
  items: T[],
  callback: (item: T) => boolean,
  chunkSize = 100
): Promise<T[]> {
  const results: T[] = [];

  for (let i = 0; i < items.length; i += chunkSize) {
    const chunk = items.slice(i, i + chunkSize);
    results.push(...chunk.filter(callback));

    // Yield to the main thread between chunks
    if ("scheduler" in globalThis) {
      await (globalThis as any).scheduler.yield();
    } else {
      await new Promise((resolve) => setTimeout(resolve, 0));
    }
  }

  return results;
}
```

**Benefits:**
- `useTransition` provides immediate visual feedback while deferring the expensive re-render
- Web Workers move heavy computation off the main thread entirely, keeping INP near zero
- `requestIdleCallback` defers analytics and URL updates until the browser is idle
- Chunked processing with yielding prevents any single task from blocking the main thread beyond 50ms

Reference: [Optimize Interaction to Next Paint](https://web.dev/articles/optimize-inp)


---

## Cumulative Layout Shift Prevention

**Impact: CRITICAL (Must be under 0.1 (Google ranking signal))**

CLS measures unexpected visual shifts during a page's lifecycle. A CLS score above 0.1 harms both user experience and search rankings, as Google treats it as a Core Web Vital ranking signal. Most layout shifts come from images without dimensions, late-loading fonts, and dynamically injected content.

## Incorrect

```html
<!-- ❌ Bad: images without dimensions, no font strategy, injected content -->
<head>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter&display=block"
    rel="stylesheet"
  />
</head>
<body>
  <header>
    <img src="/logo.png" alt="Company logo" />
  </header>

  <main>
    <article>
      <h1>Latest News</h1>
      <img src="/article-hero.jpg" alt="Article hero" />
      <p>Article content here...</p>
    </article>

    <!-- Ad banner injected above content without reserved space -->
    <div id="ad-banner"></div>
    <script>
      loadAdBanner(document.getElementById("ad-banner"));
    </script>

    <!-- Cookie consent pushes content down -->
    <div class="cookie-banner">
      <p>We use cookies...</p>
    </div>
  </main>
</body>
```

**Problems:**
- Images without `width` and `height` attributes cause the browser to reflow content once dimensions are known
- `font-display: block` causes an invisible text flash (FOIT) followed by a layout shift when the font loads
- The ad banner div has no reserved height, pushing content down when the ad loads
- Cookie consent banner inserted into the document flow shifts all content below it

## Correct

```html
<!-- ✅ Good: explicit dimensions, font strategy, reserved space -->
<head>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter&display=swap"
    rel="stylesheet"
  />
  <style>
    /* Size-adjust fallback to match Inter metrics */
    @font-face {
      font-family: "Inter Fallback";
      src: local("Arial");
      size-adjust: 107%;
      ascent-override: 90%;
      descent-override: 22%;
      line-gap-override: 0%;
    }

    body {
      font-family: "Inter", "Inter Fallback", sans-serif;
    }

    /* Reserve space for ad banner */
    .ad-slot {
      min-height: 250px;
      width: 100%;
      background: #f0f0f0;
    }
  </style>
</head>
<body>
  <header>
    <img src="/logo.png" alt="Company logo" width="180" height="40" />
  </header>

  <main>
    <article>
      <h1>Latest News</h1>
      <img
        src="/article-hero.jpg"
        alt="Article hero"
        width="1200"
        height="630"
        style="aspect-ratio: 1200 / 630; width: 100%; height: auto;"
      />
      <p>Article content here...</p>
    </article>

    <!-- Ad banner with reserved space -->
    <div class="ad-slot" id="ad-banner"></div>
    <script>
      loadAdBanner(document.getElementById("ad-banner"));
    </script>
  </main>

  <!-- Cookie consent as overlay, not in document flow -->
  <div
    class="cookie-banner"
    style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;"
  >
    <p>We use cookies...</p>
  </div>
</body>
```

```tsx
// ✅ React: explicit dimensions and aspect-ratio to prevent layout shift
interface Article {
  title: string;
  heroImage: string;
  heroAlt: string;
  content: string;
}

export default function ArticlePage({ article }: { article: Article }) {
  return (
    <article>
      <h1>{article.title}</h1>
      <img
        src={article.heroImage}
        alt={article.heroAlt}
        width={1200}
        height={630}
        style={{ aspectRatio: "1200 / 630", width: "100%", height: "auto" }}
        fetchPriority="high"
      />
      <div dangerouslySetInnerHTML={{ __html: article.content }} />
    </article>
  );
}
```

**Benefits:**
- Explicit `width` and `height` attributes let the browser calculate aspect ratio before the image loads
- `aspect-ratio` CSS property ensures responsive images maintain their space during layout
- `font-display: swap` with `size-adjust` eliminates both invisible text and font-swap layout shifts
- Fixed positioning on the cookie banner keeps it out of document flow, preventing content shifts
- Reserved `min-height` on ad slots prevents content from jumping when ads load

Reference: [Optimize Cumulative Layout Shift](https://web.dev/articles/optimize-cls)


---

## Essential HTML Meta Tags

**Impact: CRITICAL (Every page must have unique title and description)**

Title tags and meta descriptions are the most fundamental on-page SEO elements. The title tag is a confirmed ranking factor, and the meta description directly influences click-through rates in search results. Every page must have a unique, properly sized title (50-60 characters) and description (150-160 characters).

## Incorrect

```html
<!-- ❌ Bad: missing or poorly configured meta tags -->
<head>
  <title>Home</title>
  <!-- No charset declaration -->
  <!-- No viewport meta tag -->
  <!-- No meta description -->
  <!-- No Open Graph tags -->
</head>
```

```tsx
// ❌ Bad: React component with no meta tags
export default function ProductPage({ product }: { product: Product }) {
  return (
    <div>
      <h1>{product.name}</h1>
      <p>{product.description}</p>
    </div>
  );
}
```

```blade
{{-- ❌ Bad: Laravel Blade with hardcoded duplicate meta across pages --}}
<head>
    <title>My Website</title>
    <meta name="description" content="Welcome to my website">
</head>
```

**Problems:**
- Generic title like "Home" wastes the most valuable on-page ranking signal
- Missing `<meta name="viewport">` breaks mobile rendering and mobile-first indexing
- No meta description means Google auto-generates a snippet, often poorly
- Duplicate titles and descriptions across pages cause keyword cannibalization
- Missing charset can cause character encoding issues in search results

## Correct

```html
<!-- ✅ Good: complete meta tag setup with proper lengths -->
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Title: 50-60 characters, primary keyword near the front -->
  <title>Wireless Noise-Cancelling Headphones | AudioTech</title>

  <!-- Description: 150-160 characters, includes CTA -->
  <meta
    name="description"
    content="Shop AudioTech wireless noise-cancelling headphones with 40-hour battery life and premium sound. Free shipping on orders over $50. Compare models now."
  />

  <!-- Open Graph for social sharing -->
  <meta property="og:title" content="Wireless Noise-Cancelling Headphones" />
  <meta
    property="og:description"
    content="Premium sound with 40-hour battery life. Free shipping over $50."
  />
  <meta property="og:image" content="https://example.com/images/headphones-og.jpg" />
  <meta property="og:url" content="https://example.com/headphones" />
  <meta property="og:type" content="product" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />

  <link rel="canonical" href="https://example.com/headphones" />
</head>
```

```tsx
// ✅ React SPA: dynamic meta tags with Inertia.js Head component
import { Head } from '@inertiajs/react';

interface Product {
  name: string;
  slug: string;
  description: string;
  metaDescription: string;
  shortDescription: string;
  ogImage: string;
}

export default function ProductPage({ product }: { product: Product }) {
  return (
    <>
      <Head>
        <title>{`${product.name} | AudioTech`}</title>
        <meta head-key="description" name="description" content={product.metaDescription} />
        <meta property="og:title" content={product.name} />
        <meta property="og:description" content={product.shortDescription} />
        <meta property="og:image" content={product.ogImage} />
        <meta name="twitter:card" content="summary_large_image" />
        <link
          rel="canonical"
          href={`https://example.com/products/${product.slug}`}
        />
      </Head>
      <main>
        <h1>{product.name}</h1>
        <p>{product.description}</p>
      </main>
    </>
  );
}
```

```php
{{-- ✅ Laravel Blade: dynamic meta tags via layout (layouts/app.blade.php) --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Default Site Title')</title>
    <meta name="description" content="@yield('meta_description', 'Default site description under 160 characters.')">
    <meta property="og:title" content="@yield('og_title', 'Default Site Title')">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:image" content="@yield('og_image', asset('images/default-og.jpg'))">
    <link rel="canonical" href="@yield('canonical', url()->current())">
</head>

{{-- products/show.blade.php --}}
@extends('layouts.app')

@section('title', Str::limit($product->name . ' | AudioTech', 60))
@section('meta_description', Str::limit($product->meta_description, 160))
@section('og_title', $product->name)
@section('og_image', $product->og_image_url)
@section('canonical', route('products.show', $product->slug))
```

**Benefits:**
- Unique, keyword-rich titles on every page maximize ranking potential for target queries
- Properly sized meta descriptions improve CTR by giving searchers a compelling preview
- Open Graph and Twitter Card tags ensure rich previews when pages are shared on social media
- Dynamic metadata from the CMS or database prevents duplicate meta tags across pages
- Charset and viewport meta tags ensure correct rendering across devices and browsers

Reference: [Google's Title Links Documentation](https://developers.google.com/search/docs/appearance/title-link)


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


---

## XML Sitemap Best Practices

**Impact: HIGH (Helps search engines discover and index all pages)**

An XML sitemap is a roadmap for search engines, listing every page you want indexed along with metadata about when it was last updated. A well-maintained sitemap improves crawl efficiency, ensures new content is discovered quickly, and prevents important pages from being missed.

## Incorrect

```xml
<!-- ❌ Bad: includes noindex pages, stale dates, bloated sitemap -->
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- noindex page should not be in sitemap -->
  <url>
    <loc>https://example.com/admin/dashboard</loc>
    <lastmod>2020-01-01</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Stale lastmod date from years ago -->
  <url>
    <loc>https://example.com/products/widget</loc>
    <lastmod>2019-06-15</lastmod>
    <changefreq>always</changefreq>
    <priority>0.9</priority>
  </url>

  <!-- Non-canonical URL variation -->
  <url>
    <loc>https://example.com/products/widget?ref=homepage</loc>
    <lastmod>2019-06-15</lastmod>
  </url>

  <!-- 50,000+ URLs in a single file makes it slow to parse -->
</urlset>
```

**Problems:**
- Including noindex or admin pages in the sitemap sends contradictory signals to crawlers
- Stale `lastmod` dates cause crawlers to skip pages that may have been updated
- Non-canonical URL variations waste crawl budget and dilute link signals
- A single sitemap file with over 50,000 URLs exceeds the sitemap protocol limit
- `priority` and `changefreq` are largely ignored by Google and add noise

## Correct

```xml
<!-- ✅ Good: clean sitemap with only canonical, indexable URLs -->
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc>https://example.com/</loc>
    <lastmod>2026-03-10</lastmod>
  </url>

  <url>
    <loc>https://example.com/products/wireless-headphones</loc>
    <lastmod>2026-03-08</lastmod>
    <image:image>
      <image:loc>https://example.com/images/wireless-headphones.webp</image:loc>
      <image:title>Wireless Noise-Cancelling Headphones</image:title>
    </image:image>
  </url>

  <url>
    <loc>https://example.com/blog/seo-guide-2026</loc>
    <lastmod>2026-02-20</lastmod>
  </url>
</urlset>
```

```xml
<!-- ✅ Good: sitemap index for large sites (>50k URLs) -->
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>https://example.com/sitemaps/pages.xml</loc>
    <lastmod>2026-03-10</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemaps/products-001.xml</loc>
    <lastmod>2026-03-08</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemaps/products-002.xml</loc>
    <lastmod>2026-03-05</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemaps/blog.xml</loc>
    <lastmod>2026-02-20</lastmod>
  </sitemap>
</sitemapindex>
```

```php
// ✅ Laravel: using spatie/laravel-sitemap
// Install: composer require spatie/laravel-sitemap

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Post;

// app/Console/Commands/GenerateSitemap.php
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    public function handle(): void
    {
        $sitemapIndex = SitemapIndex::create();

        // Products sitemap
        $productSitemap = Sitemap::create();
        Product::query()
            ->where('is_published', true)
            ->where('is_indexable', true)
            ->cursor()
            ->each(function (Product $product) use ($productSitemap) {
                $productSitemap->add(
                    Url::create(route('products.show', $product->slug))
                        ->setLastModificationDate($product->updated_at)
                        ->addImage($product->image_url, $product->name)
                );
            });
        $productSitemap->writeToFile(public_path('sitemaps/products.xml'));
        $sitemapIndex->add('/sitemaps/products.xml');

        // Blog sitemap
        $blogSitemap = Sitemap::create();
        Post::query()
            ->where('status', 'published')
            ->cursor()
            ->each(function (Post $post) use ($blogSitemap) {
                $blogSitemap->add(
                    Url::create(route('blog.show', $post->slug))
                        ->setLastModificationDate($post->updated_at)
                );
            });
        $blogSitemap->writeToFile(public_path('sitemaps/blog.xml'));
        $sitemapIndex->add('/sitemaps/blog.xml');

        // Write sitemap index
        $sitemapIndex->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');
    }
}
```

**Benefits:**
- Only canonical, indexable URLs are included, preventing wasted crawl budget
- Accurate `lastmod` dates from the database help crawlers prioritize recently updated content
- Image sitemap extension improves visibility in Google Image Search
- Sitemap index pattern keeps individual files under the 50,000 URL / 50MB limit
- Automated generation via commands or build steps ensures the sitemap stays current

Reference: [Google's Sitemap Documentation](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview)


---

## SEO-Friendly URL Structure

**Impact: HIGH (Clean URLs improve CTR and crawlability)**

URLs are visible in search results and influence both click-through rates and crawl efficiency. Clean, descriptive URLs help users and search engines understand the page content before visiting it. Changing URLs without proper redirects causes 404 errors and lost link equity.

## Incorrect

```
❌ Bad URL patterns:

https://example.com/index.php?page=product&id=4827&cat=12
https://example.com/Products/Running_Shoes/ITEM-4827.html
https://example.com/shop/cat/12/subcat/45/product/4827/view/detail/ref/homepage
https://EXAMPLE.COM/Our-Amazing-Collection-Of-The-Best-Running-Shoes-For-Marathon-Training-2026
https://example.com/p/4827
```

```php
// ❌ Bad: Laravel routes with IDs and query params as primary URLs
Route::get('/product', function (Request $request) {
    $product = Product::findOrFail($request->query('id'));
    return view('product.show', compact('product'));
});
// Result: /product?id=4827
```

```tsx
// ❌ Bad: Inertia page using only numeric ID in URL
// Laravel route: Route::get('/products/{product}', ...)
// Result: /products/4827 — no keywords in URL

export default function ProductPage({ product }: { product: { id: number; name: string } }) {
  // URL is /products/4827 — no keyword context for search engines
  return <div>{product.name}</div>;
}
```

**Problems:**
- Query parameter URLs are harder for search engines to crawl and provide no keyword context
- Uppercase letters create duplicate URL variations (servers may treat `/Products` and `/products` differently)
- Underscores are not treated as word separators by Google (`running_shoes` is one token, not two)
- Excessively long URLs are truncated in search results and harder to share
- Numeric-only slugs provide no content signal to users or crawlers

## Correct

```
✅ Good URL patterns:

https://example.com/running-shoes
https://example.com/running-shoes/nike-air-zoom-pegasus
https://example.com/blog/marathon-training-guide
https://example.com/blog/marathon-training-guide/nutrition-tips
```

```php
// ✅ Laravel: slug-based routing with 301 redirects for old URLs
// routes/web.php
Route::get('/products/{product:slug}', [ProductController::class, 'show'])
    ->name('products.show');

// Redirect old query-param URLs to new slug URLs
Route::get('/product', function (Request $request) {
    $product = Product::findOrFail($request->query('id'));
    return redirect()->route('products.show', $product->slug, 301);
});

// app/Models/Product.php
class Product extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Auto-generate slug from name on creation
    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug = Str::slug($product->name);
        });
    }
}
```

```php
// ✅ Laravel: middleware to enforce lowercase URLs with 301 redirect
// app/Http/Middleware/LowercaseUrls.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LowercaseUrls
{
    public function handle(Request $request, Closure $next)
    {
        $url = $request->getRequestUri();
        $lowercase = strtolower($url);

        if ($url !== $lowercase) {
            return redirect($lowercase, 301);
        }

        return $next($request);
    }
}
```

**Benefits:**
- Hyphenated, lowercase slugs are treated as separate words by Google, improving keyword matching
- Short, descriptive URLs display fully in search results and improve click-through rates
- 301 redirects preserve link equity when URLs change, preventing SEO loss during migrations
- Slug-based routing eliminates duplicate content from query parameter variations
- Middleware enforcement ensures URL consistency across the entire application automatically

Reference: [Google's URL Structure Guidelines](https://developers.google.com/search/docs/crawling-indexing/url-structure)


---

## Heading Hierarchy and Structure

**Impact: HIGH (Crawlers and screen readers rely on heading structure)**

Search engines use headings to understand content hierarchy and topical relevance. A clear heading structure also improves accessibility for screen-reader users navigating by heading landmarks.

## Incorrect

```html
<!-- ❌ Multiple h1 tags on one page -->
<h1>Welcome to Our Store</h1>
<section>
  <h1>Latest Products</h1>
  <h4>Running Shoes</h4> <!-- Skipped h2 and h3 -->
  <p>High-performance running shoes for every terrain.</p>
  <h2>Customer Reviews</h2>
</section>
<section>
  <h1>About Us</h1> <!-- Third h1 on the same page -->
  <p>We have been selling shoes since 2010.</p>
</section>

<!-- ❌ Using headings purely for styling -->
<h3 class="big-text">Free shipping on orders over $50</h3>
```

**Problems:**
- Multiple `<h1>` tags dilute the primary topic signal for crawlers
- Skipping from `<h1>` to `<h4>` breaks the logical outline and confuses assistive technology
- Using heading tags for visual styling instead of structure misleads search engines about content importance

## Correct

```html
<!-- ✅ Single h1 with target keyword, logical nesting -->
<h1>Running Shoes for Every Terrain</h1>

<section>
  <h2>Latest Products</h2>
  <h3>Trail Running Shoes</h3>
  <p>Grip-focused shoes designed for off-road surfaces.</p>
  <h3>Road Running Shoes</h3>
  <p>Lightweight cushioned shoes for pavement.</p>
</section>

<section>
  <h2>Customer Reviews</h2>
  <h3>Top-Rated This Month</h3>
  <p>See what runners are saying about our best sellers.</p>
</section>

<!-- Use CSS classes for styling, not heading tags -->
<p class="promo-banner">Free shipping on orders over $50</p>
```

**Benefits:**
- Single `<h1>` clearly signals the page topic to search engines
- Logical `h1 > h2 > h3` nesting creates a scannable outline for crawlers and screen readers
- Heading levels are never skipped, preserving document structure integrity

Reference: [Google Search Central - Headings](https://developers.google.com/search/docs/appearance/title-link)


---

## Semantic HTML for SEO

**Impact: HIGH (Semantic elements help crawlers understand page structure)**

Semantic HTML gives meaning to your markup so search engines can distinguish navigation from content, sidebars from articles, and headers from footers. This improves indexing accuracy and accessibility compliance.

## Incorrect

```html
<!-- ❌ All divs with no semantic meaning -->
<div class="header">
  <div class="logo">My Site</div>
  <div class="nav">
    <div class="nav-item"><a href="/">Home</a></div>
    <div class="nav-item"><a href="/blog">Blog</a></div>
    <div class="nav-item"><a href="/contact">Contact</a></div>
  </div>
</div>

<div class="content">
  <div class="post">
    <div class="post-title">Understanding Semantic HTML</div>
    <div class="post-body">
      <p>Semantic HTML is important for SEO...</p>
    </div>
  </div>
  <div class="sidebar">
    <div class="widget">Related Posts</div>
  </div>
</div>

<div class="footer">
  <div class="copyright">&copy; 2026 My Site</div>
</div>
```

**Problems:**
- Crawlers cannot distinguish navigation, content, and supplementary sections
- Screen readers have no landmark regions to jump between
- The document structure is invisible without inspecting class names

## Correct

```html
<!-- ✅ Proper semantic elements with one main per page -->
<header>
  <a href="/" class="logo">My Site</a>
  <nav aria-label="Primary">
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/blog">Blog</a></li>
      <li><a href="/contact">Contact</a></li>
    </ul>
  </nav>
</header>

<main>
  <article>
    <h1>Understanding Semantic HTML</h1>
    <p>Semantic HTML is important for SEO...</p>

    <section>
      <h2>Why It Matters</h2>
      <p>Search engines use element types to weight content relevance.</p>
    </section>

    <section>
      <h2>Key Elements</h2>
      <p>The most impactful elements are main, article, nav, and section.</p>
    </section>
  </article>

  <aside aria-label="Sidebar">
    <h2>Related Posts</h2>
    <ul>
      <li><a href="/blog/html5-guide">HTML5 Guide</a></li>
    </ul>
  </aside>
</main>

<footer>
  <p>&copy; 2026 My Site</p>
</footer>
```

**Benefits:**
- Crawlers identify the primary content via `<main>` and `<article>`, boosting indexing accuracy
- `<nav>` signals navigation links, helping search engines discover internal pages
- `<aside>` marks supplementary content so it is not confused with the main topic
- Screen readers can jump between landmark regions (`header`, `main`, `footer`, `nav`)

Reference: [MDN - HTML Elements Reference](https://developer.mozilla.org/en-US/docs/Web/HTML/Element)


---

## Internal Linking Strategy

**Impact: HIGH (Distributes page authority and aids discoverability)**

Internal links pass PageRank between pages, help crawlers discover content, and establish topical relationships. A well-planned internal linking structure ensures every page is reachable and properly weighted.

## Incorrect

```html
<!-- ❌ Generic "click here" anchor text -->
<p>
  We wrote a guide about page speed optimization.
  <a href="/blog/page-speed">Click here</a> to read it.
</p>

<!-- ❌ Navigation driven entirely by JavaScript -->
<div onclick="window.location='/pricing'">View Pricing</div>

<!-- ❌ Orphan page — no internal links point to it -->
<!-- /blog/advanced-caching exists but is never linked from any other page -->
```

```tsx
// ❌ Inertia: using router.visit() instead of <Link>
import { router } from '@inertiajs/react';

function ProductCard({ product }: { product: Product }) {
  return (
    <div
      className="product-card"
      onClick={() => router.visit(`/products/${product.slug}`)}
    >
      <h3>{product.name}</h3>
      <p>{product.summary}</p>
      {/* No <a> tag — search engines cannot follow this link */}
    </div>
  );
}
```

**Problems:**
- "Click here" gives crawlers no context about the destination page's topic
- JavaScript-only navigation is invisible to crawlers that do not execute JS
- Orphan pages with zero internal links may never be crawled or indexed

## Correct

```html
<!-- ✅ Descriptive anchor text with contextual relevance -->
<p>
  Improve load times with our
  <a href="/blog/page-speed">page speed optimization guide</a>,
  covering image compression, caching, and lazy loading.
</p>

<!-- ✅ Breadcrumb navigation keeping pages within 3 clicks of homepage -->
<nav aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Home</a></li>
    <li><a href="/blog">Blog</a></li>
    <li><a href="/blog/page-speed" aria-current="page">Page Speed Optimization</a></li>
  </ol>
</nav>

<!-- ✅ Related posts section linking to deeper content -->
<section>
  <h2>Related Articles</h2>
  <ul>
    <li><a href="/blog/advanced-caching">Advanced Caching Strategies</a></li>
    <li><a href="/blog/image-formats">Choosing the Right Image Format</a></li>
  </ul>
</section>
```

```tsx
// ✅ Inertia.js: crawlable links with <Link> component
import { Link } from '@inertiajs/react';

function ProductCard({ product }: { product: Product }) {
  return (
    <article className="product-card">
      <h3>
        <Link href={`/products/${product.slug}`}>{product.name}</Link>
      </h3>
      <p>{product.summary}</p>
    </article>
  );
}
```

**Benefits:**
- Descriptive anchor text signals topic relevance to crawlers for both source and destination pages
- Breadcrumbs keep every page within 3 clicks of the homepage, improving crawl efficiency
- Using `<a>` tags ensures links are discoverable without JavaScript execution
- Related-content sections eliminate orphan pages and distribute link equity

Reference: [Google Search Central - Links](https://developers.google.com/search/docs/crawling-indexing/links-crawlable)


---

## Image SEO and Alt Text

**Impact: HIGH (Images appear in Google Image Search and affect CWV)**

Properly optimized images improve page load performance (directly affecting Core Web Vitals), surface content in Google Image Search, and provide essential context for screen-reader users.

## Incorrect

```html
<!-- ❌ Missing alt text, generic filename, no dimensions -->
<img src="/uploads/IMG_1234.jpg" />

<!-- ❌ Decorative alt text on a meaningful image -->
<img src="/photos/photo1.png" alt="image" />

<!-- ❌ No width/height causes layout shift (hurts CLS) -->
<img src="/products/shoe.jpg" alt="Running shoe" />

<!-- ❌ Serving oversized unoptimized images -->
<img
  src="/uploads/hero-banner-4000x2000.png"
  alt="Homepage banner"
  width="800"
  height="400"
/>
```

**Problems:**
- Missing `alt` text means crawlers and screen readers get zero context about the image
- Generic filenames like `IMG_1234.jpg` waste a ranking signal; Google reads filenames
- Omitting `width` and `height` causes Cumulative Layout Shift (CLS) as images load
- Serving a 4000px PNG when an 800px WebP would suffice wastes bandwidth and hurts LCP

## Correct

```html
<!-- ✅ Descriptive alt text, meaningful filename, explicit dimensions -->
<img
  src="/images/trail-running-shoe-side-view.webp"
  alt="Blue trail running shoe with Vibram sole, side view"
  width="800"
  height="600"
  loading="lazy"
/>

<!-- ✅ Responsive images with modern format sources -->
<picture>
  <source
    srcset="/images/hero-homepage.avif"
    type="image/avif"
  />
  <source
    srcset="/images/hero-homepage.webp"
    type="image/webp"
  />
  <img
    src="/images/hero-homepage.jpg"
    alt="Runner crossing a mountain trail at sunrise"
    width="1200"
    height="630"
    fetchpriority="high"
  />
</picture>

<!-- ✅ Decorative images use empty alt to be skipped by screen readers -->
<img src="/images/divider-line.svg" alt="" role="presentation" />
```

```tsx
// ✅ React component with proper image optimization
interface Product {
  slug: string;
  name: string;
  color: string;
  category: string;
}

function ProductImage({ product }: { product: Product }) {
  return (
    <img
      src={`/images/products/${product.slug}.webp`}
      alt={`${product.name} — ${product.color}, ${product.category}`}
      width={600}
      height={450}
      loading="lazy"
      decoding="async"
      sizes="(max-width: 768px) 100vw, 50vw"
    />
  );
}
```

**Benefits:**
- Descriptive `alt` text and filenames provide keyword signals for Google Image Search
- Explicit `width` and `height` eliminate layout shift, improving CLS scores
- `<picture>` with WebP/AVIF sources reduces file size by 25-50% over JPEG/PNG
- `loading="lazy"` defers off-screen images, improving LCP for above-the-fold content
- `fetchpriority="high"` on hero images tells the browser to prioritize the LCP element

Reference: [Google Search Central - Image SEO](https://developers.google.com/search/docs/appearance/google-images)


---

## JSON-LD Structured Data Basics

**Impact: HIGH (Enables rich results in search (stars, prices, FAQs))**

JSON-LD is Google's recommended format for structured data. It separates markup from HTML, is easier to maintain than Microdata or RDFa, and enables rich result features like star ratings, FAQ dropdowns, and sitelinks search boxes.

## Incorrect

```html
<!-- ❌ Using Microdata — harder to maintain, mixed into HTML -->
<div itemscope itemtype="https://schema.org/Organization">
  <span itemprop="name">Acme Corp</span>
  <span itemprop="url">https://acme.com</span>
  <div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
    <span itemprop="streetAddress">123 Main St</span>
  </div>
</div>

<!-- ❌ Structured data contradicts visible content -->
<!-- Page shows price as $29.99 but schema says $19.99 -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Widget",
  "offers": {
    "@type": "Offer",
    "price": "19.99"
  }
}
</script>

<!-- ❌ Missing @context, invalid structure -->
<script type="application/ld+json">
{
  "@type": "Organization",
  "name": "Acme Corp"
}
</script>
```

**Problems:**
- Microdata and RDFa interleave data attributes into HTML, making updates error-prone
- Structured data that contradicts visible page content violates Google's guidelines and can trigger a manual action
- Missing `@context` makes the entire block invalid and unreadable by crawlers

## Correct

```html
<!-- ✅ JSON-LD Organization + WebSite on homepage -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Acme Corp",
  "url": "https://acme.com",
  "logo": "https://acme.com/images/logo.png",
  "sameAs": [
    "https://twitter.com/acmecorp",
    "https://www.linkedin.com/company/acmecorp"
  ],
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+1-800-555-0100",
    "contactType": "customer service"
  }
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Acme Corp",
  "url": "https://acme.com",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://acme.com/search?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
```

```tsx
// ✅ React component for generating JSON-LD
interface JsonLdProps {
  data: Record<string, unknown>;
}

function JsonLd({ data }: JsonLdProps) {
  const jsonLd = {
    '@context': 'https://schema.org',
    ...data,
  };

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
    />
  );
}

// Usage on homepage
function HomePage() {
  return (
    <>
      <JsonLd
        data={{
          '@type': 'Organization',
          name: 'Acme Corp',
          url: 'https://acme.com',
          logo: 'https://acme.com/images/logo.png',
        }}
      />
      <JsonLd
        data={{
          '@type': 'WebSite',
          name: 'Acme Corp',
          url: 'https://acme.com',
          potentialAction: {
            '@type': 'SearchAction',
            target: 'https://acme.com/search?q={search_term_string}',
            'query-input': 'required name=search_term_string',
          },
        }}
      />
      <main>{/* Page content */}</main>
    </>
  );
}
```

```php
{{-- ✅ Laravel Blade partial for JSON-LD --}}
@php
$organization = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => config('app.name'),
    'url' => config('app.url'),
    'logo' => asset('images/logo.png'),
    'sameAs' => [
        'https://twitter.com/acmecorp',
        'https://www.linkedin.com/company/acmecorp',
    ],
];
@endphp

<script type="application/ld+json">
    {!! json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
```

**Benefits:**
- JSON-LD is decoupled from HTML, making it easy to add, update, and debug
- Organization schema enables knowledge panel features in search results
- WebSite schema with SearchAction enables the sitelinks search box in SERPs
- Validated structured data is eligible for rich results, increasing click-through rates

Reference: [Google Search Central - Structured Data](https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data)


---

## Article Schema Markup

**Impact: HIGH (Enables article rich results with author and date)**

Article structured data helps Google display rich results with headline, author name, publication date, and thumbnail image. This improves visibility in Google News, Discover, and standard search results.

## Incorrect

```html
<!-- ❌ Missing required fields, no author type, wrong date format -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "How to Optimize Your Website",
  "author": "Jane Smith",
  "datePublished": "March 10, 2026",
  "dateModified": "last week"
}
</script>
```

**Problems:**
- `author` must be a `Person` or `Organization` object, not a plain string
- `datePublished` and `dateModified` must be in ISO 8601 format (`YYYY-MM-DD` or full datetime)
- Missing `image` prevents the article from appearing in Google Discover and Top Stories
- Missing `publisher` omits the publishing organization from the rich result

## Correct

```html
<!-- ✅ Complete Article schema with all recommended properties -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "How to Optimize Your Website for Core Web Vitals",
  "description": "A practical guide to improving LCP, INP, and CLS scores for better search rankings.",
  "image": [
    "https://acme.com/images/cwv-guide-16x9.webp",
    "https://acme.com/images/cwv-guide-4x3.webp",
    "https://acme.com/images/cwv-guide-1x1.webp"
  ],
  "author": {
    "@type": "Person",
    "name": "Jane Smith",
    "url": "https://acme.com/authors/jane-smith"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Acme Corp",
    "logo": {
      "@type": "ImageObject",
      "url": "https://acme.com/images/logo.png"
    }
  },
  "datePublished": "2026-03-10T08:00:00+00:00",
  "dateModified": "2026-03-12T14:30:00+00:00",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "https://acme.com/blog/optimize-core-web-vitals"
  }
}
</script>
```

```tsx
// ✅ React component for Article JSON-LD
interface ArticleSchemaProps {
  title: string;
  description: string;
  images: string[];
  authorName: string;
  authorUrl: string;
  publishedAt: string; // ISO 8601
  modifiedAt: string;  // ISO 8601
  url: string;
}

function ArticleSchema({
  title,
  description,
  images,
  authorName,
  authorUrl,
  publishedAt,
  modifiedAt,
  url,
}: ArticleSchemaProps) {
  const schema = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: title,
    description,
    image: images,
    author: {
      '@type': 'Person',
      name: authorName,
      url: authorUrl,
    },
    publisher: {
      '@type': 'Organization',
      name: 'Acme Corp',
      logo: {
        '@type': 'ImageObject',
        url: 'https://acme.com/images/logo.png',
      },
    },
    datePublished: publishedAt,
    dateModified: modifiedAt,
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': url,
    },
  };

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
    />
  );
}
```

```php
{{-- ✅ Laravel Blade for BlogPosting schema --}}
@php
$articleSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post->title,
    'description' => $post->excerpt,
    'image' => [
        asset("storage/{$post->featured_image}"),
    ],
    'author' => [
        '@type' => 'Person',
        'name' => $post->author->name,
        'url' => route('authors.show', $post->author->slug),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => config('app.name'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('images/logo.png'),
        ],
    ],
    'datePublished' => $post->published_at->toIso8601String(),
    'dateModified' => $post->updated_at->toIso8601String(),
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('posts.show', $post->slug),
    ],
];
@endphp

<script type="application/ld+json">
    {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
```

**Benefits:**
- Complete Article schema is eligible for rich results in Google Search, News, and Discover
- Providing multiple image aspect ratios (16:9, 4:3, 1:1) maximizes display compatibility
- ISO 8601 dates ensure consistent parsing across all search engines
- Author as a `Person` object with a URL builds author entity recognition over time

Reference: [Google Search Central - Article Structured Data](https://developers.google.com/search/docs/appearance/structured-data/article)


---

## Product Schema for E-Commerce

**Impact: HIGH (Enables product rich results with price and availability)**

Product structured data enables rich results showing price, availability, star ratings, and review counts directly in search results. These enhanced listings significantly improve click-through rates for e-commerce pages.

## Incorrect

```html
<!-- ❌ Missing offers, no availability, wrong price format -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Trail Running Shoe X1",
  "description": "A high-performance trail running shoe.",
  "price": "$129.99",
  "rating": "4.5"
}
</script>
```

**Problems:**
- `price` is not a valid top-level Product property; it must be nested inside an `Offer` object
- Dollar sign in price value causes parsing errors; `price` must be a numeric string
- Missing `priceCurrency` makes the price ambiguous across markets
- `rating` as a string is invalid; ratings require an `AggregateRating` object with `ratingValue` and `reviewCount`
- Missing `availability` means Google cannot show stock status in rich results

## Correct

```html
<!-- ✅ Complete Product schema with AggregateRating and multiple offers -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Trail Running Shoe X1",
  "description": "High-performance trail running shoe with Vibram outsole and waterproof membrane.",
  "image": [
    "https://store.acme.com/images/shoe-x1-front.webp",
    "https://store.acme.com/images/shoe-x1-side.webp",
    "https://store.acme.com/images/shoe-x1-sole.webp"
  ],
  "sku": "SHOE-X1-BLU-42",
  "brand": {
    "@type": "Brand",
    "name": "Acme Running"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.5",
    "reviewCount": "312",
    "bestRating": "5"
  },
  "review": {
    "@type": "Review",
    "reviewRating": {
      "@type": "Rating",
      "ratingValue": "5",
      "bestRating": "5"
    },
    "author": {
      "@type": "Person",
      "name": "Alex Johnson"
    },
    "reviewBody": "Best trail shoe I have ever owned. Excellent grip on wet rock."
  },
  "offers": [
    {
      "@type": "Offer",
      "url": "https://store.acme.com/products/shoe-x1?color=blue",
      "priceCurrency": "USD",
      "price": "129.99",
      "priceValidUntil": "2026-12-31",
      "availability": "https://schema.org/InStock",
      "itemCondition": "https://schema.org/NewCondition",
      "seller": {
        "@type": "Organization",
        "name": "Acme Running Store"
      }
    },
    {
      "@type": "Offer",
      "url": "https://store.acme.com/products/shoe-x1?color=red",
      "priceCurrency": "USD",
      "price": "129.99",
      "priceValidUntil": "2026-12-31",
      "availability": "https://schema.org/OutOfStock",
      "itemCondition": "https://schema.org/NewCondition",
      "seller": {
        "@type": "Organization",
        "name": "Acme Running Store"
      }
    }
  ]
}
</script>
```

```tsx
// ✅ React component for Product JSON-LD
interface ProductOffer {
  url: string;
  price: number;
  currency: string;
  availability: 'InStock' | 'OutOfStock' | 'PreOrder';
}

interface ProductSchemaProps {
  name: string;
  description: string;
  images: string[];
  sku: string;
  brand: string;
  rating: number;
  reviewCount: number;
  offers: ProductOffer[];
}

function ProductSchema({
  name,
  description,
  images,
  sku,
  brand,
  rating,
  reviewCount,
  offers,
}: ProductSchemaProps) {
  const schema = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name,
    description,
    image: images,
    sku,
    brand: { '@type': 'Brand', name: brand },
    aggregateRating: {
      '@type': 'AggregateRating',
      ratingValue: String(rating),
      reviewCount: String(reviewCount),
      bestRating: '5',
    },
    offers: offers.map((offer) => ({
      '@type': 'Offer',
      url: offer.url,
      priceCurrency: offer.currency,
      price: String(offer.price),
      availability: `https://schema.org/${offer.availability}`,
      itemCondition: 'https://schema.org/NewCondition',
    })),
  };

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
    />
  );
}
```

```php
{{-- ✅ Laravel Blade for Product schema --}}
@php
$productSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => $product->description,
    'image' => $product->images->map(fn($img) => asset("storage/{$img->path}"))->toArray(),
    'sku' => $product->sku,
    'brand' => [
        '@type' => 'Brand',
        'name' => $product->brand->name,
    ],
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => (string) $product->average_rating,
        'reviewCount' => (string) $product->reviews_count,
        'bestRating' => '5',
    ],
    'offers' => $product->variants->map(fn($variant) => [
        '@type' => 'Offer',
        'url' => route('products.show', [$product->slug, 'variant' => $variant->id]),
        'priceCurrency' => 'USD',
        'price' => number_format($variant->price, 2, '.', ''),
        'availability' => $variant->in_stock
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition',
    ])->toArray(),
];
@endphp

<script type="application/ld+json">
    {!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
```

**Benefits:**
- Product rich results show price, availability, and ratings directly in SERPs
- `AggregateRating` displays star ratings, which can double click-through rates
- Multiple `Offer` entries represent product variants (color, size) with individual stock status
- `priceValidUntil` signals to Google when to re-crawl for updated pricing

Reference: [Google Search Central - Product Structured Data](https://developers.google.com/search/docs/appearance/structured-data/product)


---

## BreadcrumbList Navigation Markup

**Impact: HIGH (Shows navigation path in SERPs (Home > Category > Page))**

Breadcrumb structured data displays the page's navigation path directly in search results, replacing the raw URL. This helps users understand site hierarchy before clicking and improves click-through rates.

## Incorrect

```html
<!-- ❌ Missing position property, not matching visible breadcrumb UI -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "name": "Home",
      "item": "https://acme.com"
    },
    {
      "@type": "ListItem",
      "name": "Shoes",
      "item": "https://acme.com/shoes"
    }
  ]
}
</script>

<!-- Visible breadcrumb shows: Home > Products > Running Shoes > Trail X1 -->
<!-- But the schema only lists Home > Shoes — mismatch -->
<nav aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Home</a></li>
    <li><a href="/products">Products</a></li>
    <li><a href="/products/running-shoes">Running Shoes</a></li>
    <li>Trail X1</li>
  </ol>
</nav>
```

**Problems:**
- Missing `position` property makes the item order undefined; Google requires it
- Schema breadcrumb path does not match the visible breadcrumb UI, violating Google's consistency guidelines
- Mismatched paths may trigger a structured data warning in Search Console

## Correct

```html
<!-- ✅ Complete BreadcrumbList matching visible breadcrumbs -->
<nav aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Home</a></li>
    <li><a href="/products">Products</a></li>
    <li><a href="/products/running-shoes">Running Shoes</a></li>
    <li><span aria-current="page">Trail X1</span></li>
  </ol>
</nav>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "https://acme.com"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Products",
      "item": "https://acme.com/products"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "Running Shoes",
      "item": "https://acme.com/products/running-shoes"
    },
    {
      "@type": "ListItem",
      "position": 4,
      "name": "Trail X1"
    }
  ]
}
</script>
```

```tsx
// ✅ React component for Breadcrumb with JSON-LD
interface BreadcrumbItem {
  name: string;
  href?: string;
}

interface BreadcrumbProps {
  items: BreadcrumbItem[];
  baseUrl: string;
}

function Breadcrumb({ items, baseUrl }: BreadcrumbProps) {
  const schema = {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      ...(item.href ? { item: `${baseUrl}${item.href}` } : {}),
    })),
  };

  return (
    <>
      <nav aria-label="Breadcrumb">
        <ol className="breadcrumb">
          {items.map((item, index) => (
            <li key={index} className="breadcrumb-item">
              {item.href ? (
                <a href={item.href}>{item.name}</a>
              ) : (
                <span aria-current="page">{item.name}</span>
              )}
            </li>
          ))}
        </ol>
      </nav>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
      />
    </>
  );
}

// Usage
<Breadcrumb
  baseUrl="https://acme.com"
  items={[
    { name: 'Home', href: '/' },
    { name: 'Products', href: '/products' },
    { name: 'Running Shoes', href: '/products/running-shoes' },
    { name: 'Trail X1' },
  ]}
/>
```

```php
{{-- ✅ Laravel Blade breadcrumb component --}}
@props(['items' => []])

@php
$schemaItems = collect($items)->map(function ($item, $index) {
    $entry = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $item['name'],
    ];
    if (isset($item['url'])) {
        $entry['item'] = $item['url'];
    }
    return $entry;
});

$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $schemaItems->toArray(),
];
@endphp

<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        @foreach ($items as $item)
            <li class="breadcrumb-item">
                @if (isset($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                @else
                    <span aria-current="page">{{ $item['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- Usage: <x-breadcrumb :items="[
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Products', 'url' => route('products.index')],
    ['name' => 'Running Shoes', 'url' => route('products.category', 'running-shoes')],
    ['name' => 'Trail X1'],
]" /> --}}
```

**Benefits:**
- Breadcrumb rich results replace raw URLs in SERPs, improving readability and click-through rates
- `position` property ensures correct ordering regardless of DOM structure
- Last item without `item` URL correctly represents the current page
- Schema matches visible breadcrumbs, satisfying Google's consistency requirements

Reference: [Google Search Central - Breadcrumb Structured Data](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb)


---

## Combining Multiple Schema Types with @graph

**Impact: HIGH (Pages need multiple schema types — @graph combines them cleanly)**

Most pages need more than one schema type — a blog post page typically needs Organization, WebSite, BreadcrumbList, and Article. Using `@graph` combines them into a single structured block that search engines parse as a connected entity graph, improving how Google understands relationships between entities on your page.

## Incorrect

```html
<!-- ❌ Multiple separate script blocks for each schema type -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Acme Corp",
  "url": "https://acme.com",
  "logo": "https://acme.com/images/logo.png"
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://acme.com" },
    { "@type": "ListItem", "position": 2, "name": "Blog", "item": "https://acme.com/blog" }
  ]
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "Getting Started with Laravel",
  "author": "Jane Smith",
  "datePublished": "2026-03-10"
}
</script>
```

**Problems:**
- Multiple `<script>` blocks repeat `@context` and fragment the entity graph
- Google cannot infer relationships between separate schema blocks (e.g., the Article's publisher is the Organization)
- The `author` field is a plain string instead of a `Person` object — no entity linking
- Harder to maintain and debug across templates when schema is scattered

## Correct

```html
<!-- ✅ Single @graph combining Organization, WebSite, BreadcrumbList, and Article -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://acme.com/#organization",
      "name": "Acme Corp",
      "url": "https://acme.com",
      "logo": {
        "@type": "ImageObject",
        "url": "https://acme.com/images/logo.png"
      }
    },
    {
      "@type": "WebSite",
      "@id": "https://acme.com/#website",
      "name": "Acme Corp",
      "url": "https://acme.com",
      "publisher": { "@id": "https://acme.com/#organization" }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://acme.com/blog/getting-started-with-laravel#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "https://acme.com"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Blog",
          "item": "https://acme.com/blog"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Getting Started with Laravel"
        }
      ]
    },
    {
      "@type": "Article",
      "@id": "https://acme.com/blog/getting-started-with-laravel#article",
      "headline": "Getting Started with Laravel",
      "description": "A beginner-friendly guide to building your first Laravel application.",
      "image": [
        "https://acme.com/images/laravel-guide-16x9.webp",
        "https://acme.com/images/laravel-guide-4x3.webp"
      ],
      "author": {
        "@type": "Person",
        "name": "Jane Smith",
        "url": "https://acme.com/authors/jane-smith"
      },
      "publisher": { "@id": "https://acme.com/#organization" },
      "datePublished": "2026-03-10T08:00:00+00:00",
      "dateModified": "2026-03-12T14:30:00+00:00",
      "isPartOf": { "@id": "https://acme.com/#website" },
      "breadcrumb": { "@id": "https://acme.com/blog/getting-started-with-laravel#breadcrumb" },
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://acme.com/blog/getting-started-with-laravel"
      }
    }
  ]
}
</script>
```

```php
{{-- ✅ Laravel Blade component that builds @graph from a $schemas array --}}
{{-- Usage: <x-schema-graph :schemas="[$orgSchema, $websiteSchema, $breadcrumbSchema, $articleSchema]" /> --}}

@props(['schemas' => []])

@php
$graph = [
    '@context' => 'https://schema.org',
    '@graph' => $schemas,
];
@endphp

<script type="application/ld+json">
    {!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
```

```php
{{-- ✅ Using the component in a blog post Blade view --}}
@php
$orgSchema = [
    '@type' => 'Organization',
    '@id' => url('/') . '/#organization',
    'name' => config('app.name'),
    'url' => url('/'),
    'logo' => [
        '@type' => 'ImageObject',
        'url' => asset('images/logo.png'),
    ],
];

$websiteSchema = [
    '@type' => 'WebSite',
    '@id' => url('/') . '/#website',
    'name' => config('app.name'),
    'url' => url('/'),
    'publisher' => ['@id' => url('/') . '/#organization'],
];

$breadcrumbSchema = [
    '@type' => 'BreadcrumbList',
    '@id' => route('posts.show', $post->slug) . '#breadcrumb',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('posts.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title],
    ],
];

$articleSchema = [
    '@type' => 'Article',
    '@id' => route('posts.show', $post->slug) . '#article',
    'headline' => $post->title,
    'description' => $post->excerpt,
    'image' => [asset("storage/{$post->featured_image}")],
    'author' => [
        '@type' => 'Person',
        'name' => $post->author->name,
        'url' => route('authors.show', $post->author->slug),
    ],
    'publisher' => ['@id' => url('/') . '/#organization'],
    'datePublished' => $post->published_at->toIso8601String(),
    'dateModified' => $post->updated_at->toIso8601String(),
    'isPartOf' => ['@id' => url('/') . '/#website'],
    'breadcrumb' => ['@id' => route('posts.show', $post->slug) . '#breadcrumb'],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('posts.show', $post->slug),
    ],
];
@endphp

<x-schema-graph :schemas="[$orgSchema, $websiteSchema, $breadcrumbSchema, $articleSchema]" />
```

```tsx
// ✅ React component that builds @graph dynamically
import { Head } from '@inertiajs/react';

type SchemaEntity = Record<string, unknown>;

interface SchemaGraphProps {
  schemas: SchemaEntity[];
}

function SchemaGraph({ schemas }: SchemaGraphProps) {
  const graph = {
    '@context': 'https://schema.org',
    '@graph': schemas,
  };

  return (
    <Head>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(graph) }}
      />
    </Head>
  );
}

// ✅ Using SchemaGraph in a blog post page
interface Post {
  title: string;
  slug: string;
  excerpt: string;
  featured_image: string;
  published_at: string;
  updated_at: string;
  author: { name: string; slug: string };
}

function BlogPostPage({ post }: { post: Post }) {
  const baseUrl = 'https://acme.com';
  const postUrl = `${baseUrl}/blog/${post.slug}`;

  const schemas: SchemaEntity[] = [
    {
      '@type': 'Organization',
      '@id': `${baseUrl}/#organization`,
      name: 'Acme Corp',
      url: baseUrl,
      logo: { '@type': 'ImageObject', url: `${baseUrl}/images/logo.png` },
    },
    {
      '@type': 'WebSite',
      '@id': `${baseUrl}/#website`,
      name: 'Acme Corp',
      url: baseUrl,
      publisher: { '@id': `${baseUrl}/#organization` },
    },
    {
      '@type': 'BreadcrumbList',
      '@id': `${postUrl}#breadcrumb`,
      itemListElement: [
        { '@type': 'ListItem', position: 1, name: 'Home', item: baseUrl },
        { '@type': 'ListItem', position: 2, name: 'Blog', item: `${baseUrl}/blog` },
        { '@type': 'ListItem', position: 3, name: post.title },
      ],
    },
    {
      '@type': 'Article',
      '@id': `${postUrl}#article`,
      headline: post.title,
      description: post.excerpt,
      image: [`${baseUrl}/storage/${post.featured_image}`],
      author: {
        '@type': 'Person',
        name: post.author.name,
        url: `${baseUrl}/authors/${post.author.slug}`,
      },
      publisher: { '@id': `${baseUrl}/#organization` },
      datePublished: post.published_at,
      dateModified: post.updated_at,
      isPartOf: { '@id': `${baseUrl}/#website` },
      breadcrumb: { '@id': `${postUrl}#breadcrumb` },
      mainEntityOfPage: { '@type': 'WebPage', '@id': postUrl },
    },
  ];

  return (
    <>
      <SchemaGraph schemas={schemas} />
      <Head>
        <title>{post.title}</title>
        <meta head-key="description" name="description" content={post.excerpt} />
      </Head>
      {/* Page content */}
    </>
  );
}
```

**Benefits:**
- Single `@graph` array keeps all schema entities in one block with one `@context` declaration
- `@id` references link entities together — Google understands the Article's publisher is the Organization
- Easier to maintain with a reusable Blade component or React component
- Entity linking via `@id` strengthens the knowledge graph and improves rich result eligibility

Reference: [Google Search Central - Structured Data General Guidelines](https://developers.google.com/search/docs/appearance/structured-data/sd-policies)


---

## FAQ Page Schema Markup

**Impact: HIGH (Enables expandable FAQ accordion in Google search results)**

FAQPage structured data tells Google your page contains a list of questions and answers, enabling expandable FAQ rich results directly in search. While Google restricted FAQ rich results to well-known authoritative sites in August 2023, the markup still helps search engines understand your content structure and can improve visibility for eligible domains.

## Incorrect

```html
<!-- ❌ FAQ content with no structured data — Google shows a plain snippet -->
<div class="faq-section">
  <h2>Frequently Asked Questions</h2>

  <div class="faq-item">
    <h3>What is Laravel?</h3>
    <p>Laravel is a PHP web application framework with expressive, elegant syntax.</p>
  </div>

  <div class="faq-item">
    <h3>Is Laravel free?</h3>
    <p>Yes, Laravel is open-source and free to use under the MIT license.</p>
  </div>

  <div class="faq-item">
    <h3>What PHP version does Laravel 13 require?</h3>
    <p>Laravel 13 requires PHP 8.3 or higher.</p>
  </div>
</div>
```

**Problems:**
- No structured data means Google cannot generate FAQ rich results for this page
- Search engines must guess which content is a question and which is an answer
- The page misses the opportunity for expanded SERP real estate with accordion dropdowns
- Competitors with FAQ schema will occupy more visual space in the same search results

## Correct

```html
<!-- ✅ FAQPage schema with mainEntity array of Question/acceptedAnswer pairs -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What is Laravel?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Laravel is a PHP web application framework with expressive, elegant syntax. It provides tools for routing, authentication, database management, and more, making it one of the most popular frameworks for building modern web applications."
      }
    },
    {
      "@type": "Question",
      "name": "Is Laravel free?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, Laravel is open-source and free to use under the MIT license. You can use it for personal and commercial projects without any licensing fees."
      }
    },
    {
      "@type": "Question",
      "name": "What PHP version does Laravel 13 require?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Laravel 13 requires PHP 8.3 or higher. It is recommended to use the latest stable PHP release for optimal performance and security."
      }
    }
  ]
}
</script>
```

```php
{{-- ✅ Laravel Blade generating FAQ schema from a $faqs collection --}}
{{-- $faqs is a collection of objects with ->question and ->answer properties --}}

@php
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq->question,
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => strip_tags($faq->answer),
        ],
    ])->values()->all(),
];
@endphp

<script type="application/ld+json">
    {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- Visible FAQ content must match the schema --}}
<section class="faq-section">
    <h2>Frequently Asked Questions</h2>
    @foreach ($faqs as $faq)
        <details>
            <summary>{{ $faq->question }}</summary>
            <div>{!! $faq->answer !!}</div>
        </details>
    @endforeach
</section>
```

```tsx
// ✅ Inertia/React FAQ page with schema markup
import { Head } from '@inertiajs/react';

interface Faq {
  id: number;
  question: string;
  answer: string;
}

interface FaqPageProps {
  faqs: Faq[];
  title: string;
  description: string;
}

function stripHtml(html: string): string {
  return html.replace(/<[^>]*>/g, '');
}

export default function FaqPage({ faqs, title, description }: FaqPageProps) {
  const faqSchema = {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.map((faq) => ({
      '@type': 'Question',
      name: faq.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: stripHtml(faq.answer),
      },
    })),
  };

  return (
    <>
      <Head>
        <title>{title}</title>
        <meta head-key="description" name="description" content={description} />
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(faqSchema) }}
        />
      </Head>

      <section className="faq-section">
        <h2>Frequently Asked Questions</h2>
        {faqs.map((faq) => (
          <details key={faq.id}>
            <summary>{faq.question}</summary>
            <div dangerouslySetInnerHTML={{ __html: faq.answer }} />
          </details>
        ))}
      </section>
    </>
  );
}
```

**Benefits:**
- FAQPage schema makes the page eligible for expandable FAQ rich results on eligible sites
- Structured Q&A pairs help Google understand content even when rich results are not displayed
- The `acceptedAnswer.text` uses `strip_tags` to provide clean plaintext for the schema while the visible HTML can use rich formatting
- Visible FAQ content uses `<details>`/`<summary>` elements for native accordion behavior, matching the schema content

> **Note:** Since the August 2023 update, Google only shows FAQ rich results for well-known, authoritative government and health websites. However, FAQPage markup still helps Google understand your content structure and may influence how snippets are generated. The markup remains valid and recommended by Google's documentation.

Reference: [Google Search Central - FAQ Structured Data](https://developers.google.com/search/docs/appearance/structured-data/faqpage)


---

## Structured Data Validation and Monitoring

**Impact: HIGH (Invalid schema silently fails — broken markup gets zero rich results)**

Structured data errors are completely silent — there is no browser console warning when your JSON-LD has a missing required field or a trailing comma. The only way to catch issues is through proactive validation and monitoring. Without a validation workflow, broken schema can go undetected for months while competitors capture your rich result slots.

## Incorrect

```php
// ❌ Deploying schema without any validation or monitoring
class BlogPostController extends Controller
{
    public function show(Post $post)
    {
        // Schema built inline, never tested, never validated
        return Inertia::render('Blog/Show', [
            'post' => $post,
            // No one checks if this produces valid JSON-LD
            // No tests verify schema presence
            // Search Console errors go unchecked for months
        ]);
    }
}
```

**Problems:**
- No automated tests verify that JSON-LD is present and parseable on rendered pages
- No pre-deployment validation catches missing required fields or syntax errors
- Search Console enhancement reports are never reviewed — errors accumulate silently
- A template change can break schema across hundreds of pages with no alert

## Correct

### 1. Validate Before Deploying

Always test structured data with Google's Rich Results Test before deploying changes. Paste the rendered HTML or a live URL to verify all schema types are detected and have no errors or warnings.

### 2. Write Automated Tests

```php
// ✅ Laravel feature test verifying Article schema is present and valid
namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_post_has_valid_article_schema(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertStatus(200);
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('"headline"', false);
    }

    public function test_blog_post_schema_contains_required_fields(): void
    {
        $author = User::factory()->create(['name' => 'Jane Smith']);
        $post = Post::factory()->create([
            'title' => 'Test Post Title',
            'user_id' => $author->id,
        ]);

        $response = $this->get(route('posts.show', $post));
        $content = $response->getContent();

        // Extract JSON-LD blocks from rendered HTML
        preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $content,
            $matches
        );

        $this->assertNotEmpty($matches[1], 'No JSON-LD blocks found on page');

        $hasArticle = false;
        foreach ($matches[1] as $jsonLd) {
            $data = json_decode($jsonLd, true);
            $this->assertNotNull($data, 'JSON-LD block is not valid JSON');

            // Check @graph array or single type
            $entities = isset($data['@graph']) ? $data['@graph'] : [$data];

            foreach ($entities as $entity) {
                if (($entity['@type'] ?? '') === 'Article') {
                    $hasArticle = true;
                    $this->assertArrayHasKey('headline', $entity);
                    $this->assertArrayHasKey('datePublished', $entity);
                    $this->assertArrayHasKey('author', $entity);
                    $this->assertArrayHasKey('image', $entity);
                    $this->assertEquals('Test Post Title', $entity['headline']);
                }
            }
        }

        $this->assertTrue($hasArticle, 'No Article schema found in JSON-LD');
    }

    public function test_faq_page_has_valid_faq_schema(): void
    {
        $response = $this->get(route('faq.index'));

        $response->assertStatus(200);
        $response->assertSee('"@type":"FAQPage"', false);
        $response->assertSee('"mainEntity"', false);
        $response->assertSee('"acceptedAnswer"', false);
    }
}
```

### 3. Monitor Search Console Weekly

Check Google Search Console **Enhancements** reports at least weekly for schema errors:

- Navigate to **Search Console > Enhancements** and review each structured data type
- Look for **Error** and **Warning** counts — both prevent rich results
- Click into specific issues to see affected URLs
- After fixing, use **Validate Fix** to request re-crawling

### 4. Common Validation Errors

| Error | Cause | Fix |
|---|---|---|
| Missing required field `image` | Article schema without image property | Add at least one image URL to the `image` array |
| Invalid date format | Using `"March 10, 2026"` instead of ISO 8601 | Use `"2026-03-10T08:00:00+00:00"` or `->toIso8601String()` |
| URL is not absolute | Using `/blog/my-post` instead of full URL | Use `url()` or `route()` helpers to generate absolute URLs |
| Content mismatch | Schema `headline` differs from visible `<h1>` | Ensure schema values match on-page content exactly |
| Trailing comma in JSON | `{"name": "Acme",}` — invalid JSON syntax | Use `json_encode()` in PHP or `JSON.stringify()` in JS instead of manual strings |
| `author` is a string | `"author": "Jane"` instead of a Person object | Use `{"@type": "Person", "name": "Jane"}` |
| Missing `@context` | JSON-LD without `"@context": "https://schema.org"` | Always include `@context` at the top level or in `@graph` wrapper |

### 5. CI Pipeline Validation

```php
// ✅ Base test helper for reusable JSON-LD validation
namespace Tests;

use Illuminate\Testing\TestResponse;

trait ValidatesJsonLd
{
    /**
     * Assert that a response contains valid JSON-LD with the given @type.
     */
    protected function assertHasJsonLdType(TestResponse $response, string $type): array
    {
        $content = $response->getContent();

        preg_match_all(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $content,
            $matches
        );

        $this->assertNotEmpty($matches[1], 'No JSON-LD blocks found');

        foreach ($matches[1] as $jsonLd) {
            $data = json_decode($jsonLd, true);
            $this->assertNotNull($data, "Invalid JSON in JSON-LD block: {$jsonLd}");

            $entities = isset($data['@graph']) ? $data['@graph'] : [$data];

            foreach ($entities as $entity) {
                if (($entity['@type'] ?? '') === $type) {
                    return $entity;
                }
            }
        }

        $this->fail("No JSON-LD entity with @type \"{$type}\" found");
    }
}
```

```tsx
// ✅ React: verify schema is rendered correctly in development
import { Head } from '@inertiajs/react';

interface SchemaScriptProps {
  schema: Record<string, unknown>;
}

export function SchemaScript({ schema }: SchemaScriptProps) {
  const jsonString = JSON.stringify(schema);

  // Validate in development — catch issues before they reach production
  if (import.meta.env.DEV) {
    try {
      const parsed = JSON.parse(jsonString);
      if (!parsed['@context'] && !parsed['@graph']) {
        console.warn('[Schema] Missing @context in JSON-LD:', parsed);
      }
    } catch {
      console.error('[Schema] Invalid JSON-LD:', jsonString);
    }
  }

  return (
    <Head>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: jsonString }}
      />
    </Head>
  );
}
```

**Benefits:**
- Automated tests catch schema regressions on every pull request before they reach production
- The `ValidatesJsonLd` trait makes it easy to add schema assertions to any feature test
- Weekly Search Console monitoring catches issues that automated tests cannot (e.g., crawl-time rendering differences)
- Development-mode validation in React gives immediate console feedback during local development

Reference: [Google Rich Results Test](https://search.google.com/test/rich-results)


---

## Modern Image Formats (WebP/AVIF)

**Impact: HIGH (30-50% smaller files vs JPEG/PNG)**

Serving images in modern formats like AVIF and WebP dramatically reduces file sizes without visible quality loss. Smaller images mean faster page loads, lower bandwidth costs, and better Core Web Vitals scores. Using the `<picture>` element with fallbacks ensures all browsers receive the best format they support.

## Incorrect

```html
<!-- ❌ Bad: serving only JPEG with no responsive sizing -->
<img src="/images/product-hero.jpg" alt="Product showcase" />

<img
  src="/images/team-photo.png"
  alt="Our team"
  width="1920"
  height="1080"
/>
```

**Problems:**
- JPEG and PNG files are significantly larger than WebP/AVIF equivalents at the same visual quality
- No `srcset` means the browser downloads the full-size image on all devices, wasting bandwidth on mobile
- No fallback chain means you cannot adopt newer formats without breaking older browsers

## Correct

```html
<!-- ✅ Good: picture element with AVIF/WebP/JPEG fallback and responsive srcset -->
<picture>
  <source
    type="image/avif"
    srcset="
      /images/product-hero-400.avif 400w,
      /images/product-hero-800.avif 800w,
      /images/product-hero-1200.avif 1200w
    "
    sizes="(max-width: 600px) 100vw, (max-width: 1024px) 80vw, 1200px"
  />
  <source
    type="image/webp"
    srcset="
      /images/product-hero-400.webp 400w,
      /images/product-hero-800.webp 800w,
      /images/product-hero-1200.webp 1200w
    "
    sizes="(max-width: 600px) 100vw, (max-width: 1024px) 80vw, 1200px"
  />
  <img
    src="/images/product-hero-1200.jpg"
    srcset="
      /images/product-hero-400.jpg 400w,
      /images/product-hero-800.jpg 800w,
      /images/product-hero-1200.jpg 1200w
    "
    sizes="(max-width: 600px) 100vw, (max-width: 1024px) 80vw, 1200px"
    alt="Product showcase"
    width="1200"
    height="800"
    loading="lazy"
    decoding="async"
  />
</picture>
```

```tsx
// ✅ React: responsive image with modern format sources
export default function ProductCard({ product }: { product: { name: string; image: string } }) {
  return (
    <picture>
      <source
        type="image/avif"
        srcSet={`${product.imageBase}-400.avif 400w, ${product.imageBase}-800.avif 800w`}
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
      />
      <source
        type="image/webp"
        srcSet={`${product.imageBase}-400.webp 400w, ${product.imageBase}-800.webp 800w`}
        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
      />
      <img
        src={product.imageUrl}
        alt={product.name}
        width={800}
        height={600}
        loading="lazy"
        decoding="async"
      />
    </picture>
  );
}
```

**Benefits:**
- AVIF offers 50% savings over JPEG; WebP offers 30% savings, with automatic fallback to JPEG for older browsers
- Responsive `srcset` with `sizes` ensures the browser downloads only the appropriate resolution for each viewport
- Explicit `width` and `height` prevent layout shifts (CLS) while the image loads
- `decoding="async"` prevents the image decode from blocking the main thread

Reference: [Use modern image formats](https://web.dev/articles/choose-the-right-image-format)


---

## Lazy Loading Implementation

**Impact: HIGH (Reduces initial page weight and improves LCP)**

Native lazy loading defers off-screen images until the user scrolls near them, reducing initial page weight and speeding up the critical rendering path. However, applying lazy loading to the LCP element delays the most important visual content and directly hurts your Core Web Vitals score.

## Incorrect

```html
<!-- ❌ Bad: lazy loading the hero/LCP image -->
<section class="hero">
  <img
    src="/images/hero-banner.jpg"
    loading="lazy"
    alt="Welcome to our store"
  />
</section>

<!-- ❌ Bad: using a custom JS lazy loader for all images -->
<img data-src="/images/product-1.jpg" class="lazyload" alt="Product 1" />
<img data-src="/images/product-2.jpg" class="lazyload" alt="Product 2" />
<script src="/js/lazysizes.min.js"></script>
```

**Problems:**
- `loading="lazy"` on the hero image causes the browser to deprioritize the LCP element, delaying the largest paint
- Custom JavaScript lazy loaders add bundle weight, introduce a runtime dependency, and are invisible to the browser's preload scanner
- Images using `data-src` instead of `src` are not discoverable by the browser until JavaScript executes

## Correct

```html
<!-- ✅ Good: hero image loads eagerly with high priority -->
<section class="hero">
  <img
    src="/images/hero-banner.webp"
    fetchpriority="high"
    width="1200"
    height="600"
    alt="Welcome to our store"
  />
</section>

<!-- ✅ Good: below-fold images use native lazy loading -->
<section class="product-grid">
  <img
    src="/images/product-1.webp"
    loading="lazy"
    decoding="async"
    width="400"
    height="300"
    alt="Product 1"
  />
  <img
    src="/images/product-2.webp"
    loading="lazy"
    decoding="async"
    width="400"
    height="300"
    alt="Product 2"
  />
</section>
```

```tsx
// ✅ React: eager loading for LCP image, lazy loading for below-fold images
export default function HomePage() {
  return (
    <>
      {/* LCP element: no lazy loading, high fetch priority */}
      <img
        src="/images/hero-banner.webp"
        alt="Welcome to our store"
        width={1200}
        height={600}
        fetchPriority="high"
      />

      {/* Below-fold: native lazy loading */}
      <div className="product-grid">
        <img
          src="/images/product-1.webp"
          alt="Product 1"
          width={400}
          height={300}
          loading="lazy"
          decoding="async"
        />
      </div>
    </>
  );
}
```

**Benefits:**
- `fetchpriority="high"` on the LCP element ensures it is fetched before other resources, improving LCP
- Native `loading="lazy"` requires no JavaScript, works with the browser's preload scanner, and is supported by all modern browsers
- Real `src` attributes allow the browser to discover images immediately, unlike `data-src` patterns
- Explicit `width` and `height` reserve layout space and prevent CLS

Reference: [Browser-level image lazy loading](https://web.dev/articles/browser-level-image-lazy-loading)


---

## Web Font Loading Strategy

**Impact: HIGH (Prevents FOIT/FOUT and reduces CLS)**

Web fonts cause Flash of Invisible Text (FOIT) or Flash of Unstyled Text (FOUT) that shifts layout and degrades user experience. A proper font loading strategy uses `font-display: swap`, preloads critical fonts, subsets character sets, and self-hosts to eliminate third-party round trips.

## Incorrect

```css
/* ❌ Bad: no font-display, loading all weights from third-party CDN */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=block');

body {
  font-family: 'Inter', sans-serif;
}
```

```html
<!-- ❌ Bad: loading fonts from third-party CDN with no preconnect -->
<head>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Playfair+Display:wght@400;700&display=block"
    rel="stylesheet"
  />
</head>
```

**Problems:**
- `font-display: block` causes FOIT, hiding text for up to 3 seconds while the font downloads
- Loading all 9 font weights downloads hundreds of kilobytes that are never used
- Third-party CSS from Google Fonts requires DNS lookup, TCP connection, and TLS handshake to `fonts.googleapis.com`, then another connection to `fonts.gstatic.com`
- `@import` in CSS is render-blocking and delays font discovery further

## Correct

```css
/* ✅ Good: self-hosted, subsetted fonts with font-display: swap and size-adjust */
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter-latin-400.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}

@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter-latin-700.woff2') format('woff2');
  font-weight: 700;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F;
}

/* Fallback font with size-adjust to minimize CLS */
@font-face {
  font-family: 'Inter Fallback';
  src: local('Arial');
  size-adjust: 107%;
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}

body {
  font-family: 'Inter', 'Inter Fallback', system-ui, sans-serif;
}
```

```html
<!-- ✅ Good: preload critical font files -->
<head>
  <link
    rel="preload"
    as="font"
    type="font/woff2"
    href="/fonts/inter-latin-400.woff2"
    crossorigin
  />
  <link rel="stylesheet" href="/css/fonts.css" />
</head>
```

**Benefits:**
- `font-display: swap` shows text immediately in the fallback font, eliminating FOIT
- `size-adjust` on the fallback font matches metrics to the web font, minimizing layout shift (CLS) during the swap
- Self-hosting eliminates third-party DNS lookups, connection overhead, and potential downtime
- Subsetting with `unicode-range` reduces each font file to only the characters needed
- Preloading the regular weight font starts the download before CSS is parsed

Reference: [Best practices for fonts](https://web.dev/articles/font-best-practices)


---

## Resource Hints (Preconnect, Preload, Prefetch)

**Impact: HIGH (Reduces connection latency and speeds up critical resources)**

Resource hints tell the browser about resources it will need, allowing it to start DNS lookups, TCP connections, or full downloads before they would normally be discovered. Used correctly, they shave hundreds of milliseconds off critical resource loading. Used excessively, they waste bandwidth and compete with truly critical resources.

## Incorrect

```html
<!-- ❌ Bad: no resource hints, or preloading everything -->
<head>
  <!-- No preconnect for critical third-party origins -->
  <link rel="stylesheet" href="https://cdn.example.com/styles.css" />
  <script src="https://analytics.example.com/tracker.js"></script>

  <!-- Preloading non-critical resources wastes bandwidth -->
  <link rel="preload" as="image" href="/images/footer-bg.jpg" />
  <link rel="preload" as="image" href="/images/sidebar-ad.jpg" />
  <link rel="preload" as="image" href="/images/testimonial-1.jpg" />
  <link rel="preload" as="image" href="/images/testimonial-2.jpg" />
  <link rel="preload" as="font" href="/fonts/decorative.woff2" />
  <link rel="preload" as="script" href="/js/chat-widget.js" />
</head>
```

**Problems:**
- Without `preconnect`, the browser must wait until it discovers each third-party resource before starting DNS + TCP + TLS (300-500ms per origin)
- Preloading non-critical resources (footer images, chat widgets) competes with the LCP image and critical CSS for bandwidth
- Too many preload hints cause the browser to ignore priority signals, negating the benefit entirely

## Correct

```html
<!-- ✅ Good: targeted preconnect and preload for critical path only -->
<head>
  <!-- Preconnect to critical third-party origins (limit to 2-4) -->
  <link rel="preconnect" href="https://cdn.example.com" crossorigin />
  <link rel="preconnect" href="https://api.example.com" />

  <!-- dns-prefetch as fallback for browsers that don't support preconnect -->
  <link rel="dns-prefetch" href="https://cdn.example.com" />

  <!-- Preload only critical, above-the-fold resources -->
  <link
    rel="preload"
    as="image"
    href="/images/hero-banner.webp"
    fetchpriority="high"
  />
  <link
    rel="preload"
    as="font"
    type="font/woff2"
    href="/fonts/inter-400.woff2"
    crossorigin
  />
  <link rel="preload" as="style" href="/css/critical.css" />

  <!-- Prefetch resources needed for likely next navigation -->
  <link rel="prefetch" href="/js/checkout.js" />
</head>
```

```tsx
// ✅ React SPA: resource hints via Inertia.js Head component
import { Head } from '@inertiajs/react';

export default function AppLayout({ children }: { children: React.ReactNode }) {
  return (
    <>
      <Head>
        <link rel="preconnect" href="https://cdn.example.com" crossOrigin="" />
        <link rel="dns-prefetch" href="https://cdn.example.com" />
      </Head>
      {children}
    </>
  );
}
```

**Benefits:**
- `preconnect` eliminates 300-500ms of connection setup for critical third-party origins
- `dns-prefetch` provides a graceful fallback in older browsers that do not support `preconnect`
- Limiting preloads to 2-3 critical resources ensures they receive maximum bandwidth priority
- `prefetch` prepares resources for future navigations during idle time without competing with the current page
- `fetchpriority="high"` on the preloaded LCP image ensures it is prioritized above other preloaded resources

Reference: [Preconnect to required origins](https://developer.chrome.com/docs/lighthouse/performance/uses-rel-preconnect)


---

## Open Graph Meta Tags

**Impact: HIGH (Controls how pages appear on Facebook, LinkedIn, Discord)**

Open Graph tags control the title, description, and image shown when your page is shared on social platforms. Without them, platforms guess from page content and often produce unappealing or incorrect previews, reducing click-through rates from social traffic.

## Incorrect

```html
<!-- ❌ Bad: missing og:image, relative URLs, incomplete tags -->
<head>
  <meta property="og:title" content="My Website - Home - Welcome - Best Products - Buy Now" />
  <meta property="og:url" content="/about" />
  <meta property="og:image" content="/images/logo-small.png" />
</head>
```

**Problems:**
- `og:title` exceeds 60 characters and reads like keyword stuffing; platforms truncate it
- `og:url` uses a relative path; platforms cannot resolve it to the correct page
- `og:image` uses a relative URL and likely references a small logo instead of a 1200x630 share image
- Missing `og:description`, `og:type`, and `og:image:alt` means platforms must guess or show nothing

## Correct

```html
<!-- ✅ Good: complete OG tags with absolute URLs and proper image dimensions -->
<head>
  <meta property="og:title" content="Premium Running Shoes | SportShop" />
  <meta
    property="og:description"
    content="Lightweight, responsive running shoes designed for road and trail. Free shipping on orders over $75."
  />
  <meta property="og:image" content="https://www.sportshop.com/images/og/running-shoes.jpg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:alt" content="A pair of blue running shoes on a forest trail" />
  <meta property="og:url" content="https://www.sportshop.com/shoes/running" />
  <meta property="og:type" content="product" />
  <meta property="og:site_name" content="SportShop" />
  <meta property="og:locale" content="en_US" />
</head>
```

```tsx
// ✅ React SPA: Open Graph tags with Inertia.js Head component
import { Head } from '@inertiajs/react';

interface Product {
  name: string;
  slug: string;
  shortDescription: string;
  ogImageUrl: string;
  imageAlt: string;
}

export default function ProductPage({ product }: { product: Product }) {
  return (
    <>
      <Head>
        <title>{`${product.name} | SportShop`}</title>
        <meta head-key="description" name="description" content={product.shortDescription} />
        <meta property="og:title" content={`${product.name} | SportShop`} />
        <meta property="og:description" content={product.shortDescription} />
        <meta
          property="og:url"
          content={`https://www.sportshop.com/shoes/${product.slug}`}
        />
        <meta property="og:site_name" content="SportShop" />
        <meta property="og:image" content={product.ogImageUrl} />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:alt" content={product.imageAlt} />
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="website" />
      </Head>
      <main>
        <h1>{product.name}</h1>
      </main>
    </>
  );
}
```

```blade
{{-- ✅ Laravel Blade: OG tags in the layout --}}
<head>
  <meta property="og:title" content="{{ $ogTitle ?? Str::limit($title, 60) }}" />
  <meta property="og:description" content="{{ $ogDescription ?? Str::limit($description, 200) }}" />
  <meta property="og:image" content="{{ $ogImage ?? asset('images/og-default.jpg') }}" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:alt" content="{{ $ogImageAlt ?? $title }}" />
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:type" content="{{ $ogType ?? 'website' }}" />
  <meta property="og:site_name" content="{{ config('app.name') }}" />
  <meta property="og:locale" content="en_US" />
</head>
```

**Benefits:**
- `og:title` under 60 characters displays fully on all platforms without truncation
- `og:description` between 100-200 characters provides enough context to drive clicks
- `og:image` at 1200x630px with an absolute URL renders correctly as a large preview card on Facebook, LinkedIn, and Discord
- `og:image:alt` improves accessibility and provides context when the image fails to load
- `og:url` with an absolute URL ensures the canonical page receives all share counts

Reference: [Open Graph Protocol](https://ogp.me/)


---

## Twitter/X Card Meta Tags

**Impact: HIGH (Controls how pages appear when shared on Twitter/X)**

Twitter Card meta tags control the preview shown when your URL is posted on Twitter/X. A properly configured `summary_large_image` card with a high-quality image significantly increases engagement compared to a plain text link. Twitter falls back to Open Graph tags for `title`, `description`, and `image`, so you only need Twitter-specific tags for the card type and site handle.

## Incorrect

```html
<!-- ❌ Bad: using summary card when large image is better, missing twitter:card -->
<head>
  <meta property="og:title" content="10 Tips for Better Running Form" />
  <meta property="og:description" content="Improve your running technique." />
  <meta property="og:image" content="https://example.com/images/running-tips.jpg" />
  <!-- No twitter:card tag at all - Twitter uses a minimal default preview -->
</head>
```

```html
<!-- ❌ Bad: duplicating all OG values in Twitter tags -->
<head>
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content="10 Tips for Better Running Form" />
  <meta name="twitter:description" content="Improve your running technique." />
  <meta name="twitter:image" content="https://example.com/images/running-tips.jpg" />
</head>
```

**Problems:**
- Without `twitter:card`, Twitter renders a minimal link preview with no image or only a tiny thumbnail
- The `summary` card type shows a small square thumbnail; `summary_large_image` displays a full-width image that drives more engagement
- Duplicating `title`, `description`, and `image` in both OG and Twitter tags is unnecessary since Twitter falls back to OG values

## Correct

```html
<!-- ✅ Good: summary_large_image card, relies on OG for title/description/image -->
<head>
  <!-- Open Graph tags (shared by Facebook, LinkedIn, Discord, and Twitter) -->
  <meta property="og:title" content="10 Tips for Better Running Form" />
  <meta
    property="og:description"
    content="Expert-backed advice to improve your stride, reduce injury risk, and run faster with less effort."
  />
  <meta property="og:image" content="https://example.com/images/running-tips-1200x630.jpg" />
  <meta property="og:image:alt" content="Runner demonstrating proper form on a track" />
  <meta property="og:url" content="https://example.com/blog/running-form-tips" />

  <!-- Twitter-specific: only what Twitter needs beyond OG -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:site" content="@SportShop" />
  <meta name="twitter:creator" content="@CoachMike" />
</head>
```

```tsx
// ✅ React SPA: Twitter card tags with Inertia.js Head component
import { Head } from '@inertiajs/react';

export default function RunningTipsPage() {
  return (
    <>
      <Head>
        <title>10 Tips for Better Running Form</title>
        <meta
          name="description"
          content="Expert-backed advice to improve your stride, reduce injury risk, and run faster."
        />
        <meta property="og:title" content="10 Tips for Better Running Form" />
        <meta
          property="og:description"
          content="Expert-backed advice to improve your stride, reduce injury risk, and run faster."
        />
        <meta
          property="og:image"
          content="https://example.com/images/running-tips-1200x630.jpg"
        />
        <meta
          property="og:image:alt"
          content="Runner demonstrating proper form on a track"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="@SportShop" />
        <meta name="twitter:creator" content="@CoachMike" />
      </Head>
      <main>
        <h1>10 Tips for Better Running Form</h1>
        {/* Page content */}
      </main>
    </>
  );
}
```

**Benefits:**
- `summary_large_image` displays a full-width image preview that is significantly more engaging than the `summary` card
- `twitter:site` attributes the content to your brand account, building recognition
- `twitter:creator` credits the author and links to their profile
- Relying on OG fallback for `title`, `description`, and `image` avoids duplication and simplifies maintenance

**Validation:** Preview your cards by composing a draft post on [X.com](https://x.com) with the URL. Use [Open Graph Debugger](https://developers.facebook.com/tools/debug/) for Facebook and [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/) for LinkedIn.

Reference: [Twitter Cards documentation](https://developer.x.com/en/docs/twitter-for-websites/cards/overview/abouts-cards)


---

## Rendering Strategy for SEO

**Impact: HIGH (Wrong rendering strategy can make content invisible to search engines)**

Search engines can execute JavaScript, but CSR-only pages are slower to crawl, have a secondary indexing queue, and risk incomplete rendering. Choosing the right rendering strategy for each page type ensures your content is immediately visible to crawlers and loads fast for users.

| Strategy | Best For | SEO | Performance |
|----------|----------|-----|-------------|
| **Laravel (server-rendered)** | Content pages, blogs, marketing, products | Excellent | Excellent |
| **Laravel + Inertia.js** | Full-stack apps needing SPA feel with server rendering | Excellent | Good |
| **React SPA (Vite)** | Authenticated dashboards, admin panels | Poor | Varies |

## Incorrect

```tsx
// ❌ Bad: CSR-only for public content pages
import { useState, useEffect } from "react";

export default function ProductPage({ slug }: { slug: string }) {
  const [product, setProduct] = useState(null);

  useEffect(() => {
    fetch(`/api/products/${slug}`)
      .then((res) => res.json())
      .then(setProduct);
  }, [slug]);

  if (!product) return <div>Loading...</div>;

  return (
    <div>
      <h1>{product.name}</h1>
      <p>{product.description}</p>
    </div>
  );
}
```

**Problems:**
- Search engine crawlers see "Loading..." instead of product content on the initial HTML response
- Content depends on client-side JavaScript execution, which Googlebot processes in a deferred rendering queue
- No meta tags are available on the server-rendered HTML, so social sharing previews are empty
- Time to first meaningful content is delayed by JavaScript download, parse, and API fetch

## Correct

```php
{{-- ✅ Laravel Blade: server-rendered product page with full HTML in initial response --}}
{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('title', $product->name . ' | SportShop')
@section('meta_description', $product->short_description)
@section('og_title', $product->name)
@section('og_image', $product->og_image_url)

@section('content')
<main>
    <h1>{{ $product->name }}</h1>
    <p>{{ $product->description }}</p>
    <span>${{ number_format($product->price, 2) }}</span>
</main>
@endsection
```

```tsx
// ✅ Laravel + Inertia.js: server-rendered React pages with SPA navigation
// resources/js/Pages/Products/Show.tsx

import { Head } from "@inertiajs/react";

interface Product {
  name: string;
  slug: string;
  description: string;
  short_description: string;
  price: number;
  og_image_url: string;
}

export default function ProductShow({ product }: { product: Product }) {
  return (
    <>
      <Head>
        <title>{`${product.name} | SportShop`}</title>
        <meta head-key="description" name="description" content={product.short_description} />
        <meta property="og:title" content={product.name} />
        <meta property="og:description" content={product.short_description} />
        <meta property="og:image" content={product.og_image_url} />
      </Head>
      <main>
        <h1>{product.name}</h1>
        <p>{product.description}</p>
        <span>${product.price.toFixed(2)}</span>
      </main>
    </>
  );
}
```

```php
// ✅ Laravel + Inertia.js: controller passes data to the React page
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        return Inertia::render('Products/Show', [
            'product' => $product->only([
                'name', 'slug', 'description',
                'short_description', 'price', 'og_image_url',
            ]),
        ]);
    }
}
```

**Benefits:**
- Full HTML content is present in the initial server response, immediately visible to crawlers
- Meta tags are server-rendered, enabling rich social sharing previews
- Laravel Blade pages are fully server-rendered with zero JavaScript dependency for crawlers
- Inertia.js combines Laravel's server-side rendering with React's SPA navigation experience
- CSR is reserved for authenticated pages where SEO is not a concern

Reference: [Rendering on the Web](https://web.dev/articles/rendering-on-the-web)


---

## Dynamic Meta Tag Management in SPAs

**Impact: HIGH (Every route must have unique, server-rendered meta tags)**

Every page in your application needs a unique `<title>` and `<meta name="description">` that accurately describes its content. In single-page applications, meta tags must be updated on every route change and rendered on the server so that search engine crawlers and social media scrapers see the correct values in the initial HTML response.

## Incorrect

```html
<!-- ❌ Bad: single set of meta tags for all routes -->
<!DOCTYPE html>
<html>
  <head>
    <title>My App</title>
    <meta name="description" content="Welcome to my app" />
  </head>
  <body>
    <div id="root"></div>
    <script src="/bundle.js"></script>
  </body>
</html>
```

```tsx
// ❌ Bad: client-only meta updates with useEffect
import { useEffect } from "react";

export default function ProductPage({ product }) {
  useEffect(() => {
    document.title = product.name;
    document
      .querySelector('meta[name="description"]')
      ?.setAttribute("content", product.description);
  }, [product]);

  return <h1>{product.name}</h1>;
}
```

**Problems:**
- A single `<title>` and `<meta>` description for the entire app means all pages show "My App" in search results
- Client-side `document.title` updates happen after JavaScript executes; crawlers that read the initial HTML see the default values
- Social media scrapers (Facebook, Twitter, LinkedIn) never execute JavaScript, so shared links always show "Welcome to my app"
- No `og:title` or `og:description` means social previews are generic or empty

## Correct

```tsx
// ✅ React SPA: Inertia.js Head component for per-route meta tags
import { Head } from '@inertiajs/react';

interface Product {
  name: string;
  category: string;
  slug: string;
  shortDescription: string;
  description: string;
  ogImageUrl: string;
  imageAlt: string;
}

export default function ProductPage({ product }: { product: Product }) {
  return (
    <>
      <Head>
        <title>{`${product.name} - ${product.category} | SportShop`}</title>
        <meta head-key="description" name="description" content={product.shortDescription} />
        <link
          rel="canonical"
          href={`https://www.sportshop.com/products/${product.slug}`}
        />
        <meta property="og:title" content={product.name} />
        <meta property="og:description" content={product.shortDescription} />
        <meta property="og:image" content={product.ogImageUrl} />
        <meta
          property="og:url"
          content={`https://www.sportshop.com/products/${product.slug}`}
        />
        <meta name="twitter:card" content="summary_large_image" />
      </Head>
      <main>
        <h1>{product.name}</h1>
        <p>{product.description}</p>
      </main>
    </>
  );
}

// No provider wrapper needed — Inertia.js handles Head management automatically
```

```blade
{{-- ✅ Laravel Blade: dynamic meta tags via layout sections --}}
{{-- layouts/app.blade.php --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Default description')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:image" content="@yield('og_image', asset('images/default-og.jpg'))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta name="twitter:card" content="summary_large_image">
</head>

{{-- products/show.blade.php --}}
@extends('layouts.app')

@section('title', $product->name . ' - ' . $product->category . ' | SportShop')
@section('meta_description', $product->short_description)
@section('canonical', route('products.show', $product->slug))
@section('og_title', $product->name)
@section('og_image', $product->og_image_url)
```

**Benefits:**
- Every route has a unique `<title>` and `<meta>` description, giving search engines accurate information for each page
- Laravel Blade `@section` directives provide server-rendered meta tags visible in the initial HTML
- Inertia.js Head component updates meta tags on every client-side route change in React SPAs
- Canonical URLs prevent duplicate content issues across pagination, filters, and query parameters
- Open Graph and Twitter Card tags ensure rich previews when pages are shared on social platforms

Reference: [Google - Control your title links](https://developers.google.com/search/docs/appearance/title-link)


---

## SPA Routing and Crawlability

**Impact: HIGH (Search engines need real links and unique URLs to crawl)**

Search engines discover pages by following links. If your application uses click handlers instead of real anchor tags, crawlers cannot discover or index your pages. With Laravel + Inertia.js, routing is handled server-side by Laravel, but the frontend must still use proper `<Link>` components to render crawlable `<a>` elements.

## Incorrect

```tsx
// ❌ Bad: onClick-only navigation with no real links
import { router } from '@inertiajs/react';

export default function Navigation() {
  return (
    <nav>
      <button onClick={() => router.visit('/products')}>Products</button>
      <span onClick={() => router.visit('/about')} style={{ cursor: "pointer" }}>
        About Us
      </span>
      <div onClick={() => window.location.hash = "#contact"}>Contact</div>
    </nav>
  );
}
```

```tsx
// ❌ Bad: product card with no crawlable link
import { router } from '@inertiajs/react';

function ProductCard({ product }: { product: { slug: string; name: string } }) {
  return (
    <div
      className="product-card"
      onClick={() => router.visit(`/products/${product.slug}`)}
    >
      <h3>{product.name}</h3>
      {/* No <a> tag — search engines cannot follow this link */}
    </div>
  );
}
```

**Problems:**
- `<button>` and `<span>` with `onClick` or `router.visit()` are not crawlable; search engines cannot follow them
- No `<a href>` means users cannot right-click to open in a new tab, copy the link, or share the URL
- `router.visit()` works for navigation but does not render a crawlable HTML element

## Correct

```tsx
// ✅ Good: Inertia <Link> renders real <a> tags
import { Link } from '@inertiajs/react';

export default function Navigation() {
  return (
    <nav>
      <Link href="/products">Products</Link>
      <Link href="/about">About Us</Link>
      <Link href="/contact">Contact</Link>
    </nav>
  );
}
```

```tsx
// ✅ Good: product card with crawlable Inertia Link
import { Link } from '@inertiajs/react';

function ProductCard({ product }: { product: { slug: string; name: string; summary: string } }) {
  return (
    <article className="product-card">
      <h3>
        <Link href={`/products/${product.slug}`}>{product.name}</Link>
      </h3>
      <p>{product.summary}</p>
    </article>
  );
}
```

```php
// ✅ Laravel: proper route definitions with named routes
// routes/web.php
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/about', [AboutController::class, 'index'])->name('about');

// 404 handling is automatic — Laravel returns a 404 response for undefined routes
// Customize with resources/views/errors/404.blade.php
```

**Benefits:**
- Inertia's `<Link>` renders real `<a href>` elements that crawlers can follow to discover all pages
- Laravel handles routing server-side, so every page has a unique, indexable URL by default
- Inertia's first page load is server-rendered HTML, making content immediately visible to crawlers
- Users can right-click, open in new tab, copy link, and share URLs naturally
- Laravel's built-in 404 handling returns proper status codes for undefined routes

Reference: [Google - Links crawlable](https://developers.google.com/search/docs/crawling-indexing/links-crawlable)


---

## Viewport and Responsive Configuration

**Impact: MEDIUM (Required for mobile-first indexing)**

Google uses mobile-first indexing, meaning it primarily uses the mobile version of your page for ranking and indexing. A properly configured viewport meta tag is the foundation of responsive design and is required for your page to render correctly on mobile devices and pass Google's mobile-friendliness checks.

## Incorrect

```html
<!-- ❌ Bad: missing viewport meta tag entirely -->
<head>
  <title>My Website</title>
  <!-- No viewport tag — mobile browsers render at desktop width (typically 980px) -->
</head>
```

```html
<!-- ❌ Bad: user-scalable=no and maximum-scale restriction -->
<head>
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
  />
</head>
```

```html
<!-- ❌ Bad: fixed-width layout -->
<head>
  <meta name="viewport" content="width=1024" />
</head>
<body>
  <div style="width: 1024px; margin: 0 auto;">
    <!-- Fixed-width content that requires horizontal scrolling on mobile -->
  </div>
</body>
```

**Problems:**
- Without a viewport tag, mobile browsers render at a default width of ~980px and zoom out, making text unreadable
- `user-scalable=no` and `maximum-scale=1.0` prevent users from zooming, which is an accessibility violation (WCAG 1.4.4)
- A fixed pixel width like `width=1024` forces horizontal scrolling on any device narrower than 1024px
- Google's mobile-friendliness test will flag all of these as failures

## Correct

```html
<!-- ✅ Good: standard viewport tag with no zoom restrictions -->
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
```

```css
/* ✅ Good: responsive layout using relative units and media queries */
.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Use relative units for typography */
body {
  font-size: 1rem; /* 16px base */
  line-height: 1.5;
}

h1 {
  font-size: clamp(1.5rem, 4vw, 2.5rem);
}

/* Prevent images from overflowing */
img {
  max-width: 100%;
  height: auto;
}
```

**Benefits:**
- `width=device-width` tells the browser to match the page width to the screen width, eliminating horizontal scrolling
- No `maximum-scale` or `user-scalable` restriction ensures users can zoom for accessibility
- Relative units (`rem`, `%`, `vw`) and CSS Grid/Flexbox adapt layout to any screen size
- `clamp()` for typography scales text smoothly between breakpoints without media queries
- `max-width: 100%` on images prevents overflow on narrow screens

Reference: [Responsive web design basics](https://web.dev/articles/responsive-web-design-basics)


---

## Content Parity Between Mobile and Desktop

**Impact: MEDIUM (Google indexes mobile version -- hidden content won't rank)**

With mobile-first indexing, Google primarily crawls and indexes the mobile version of your page. If content, structured data, or images are present on desktop but hidden on mobile, Google may never see them. Both versions must contain the same meaningful content, even if the layout differs.

## Incorrect

```html
<!-- ❌ Bad: hiding content on mobile with display:none -->
<div class="product-details">
  <h1>Premium Running Shoes</h1>
  <p>Lightweight and responsive design.</p>

  <!-- This content is invisible to Google's mobile-first crawler -->
  <div class="desktop-only">
    <h2>Technical Specifications</h2>
    <table>
      <tr><td>Weight</td><td>245g</td></tr>
      <tr><td>Drop</td><td>8mm</td></tr>
      <tr><td>Cushioning</td><td>React foam</td></tr>
    </table>
  </div>
</div>
```

```css
/* ❌ Bad: removing content from mobile view */
@media (max-width: 768px) {
  .desktop-only {
    display: none;
  }

  /* Different heading text per viewport */
  .hero h1 .full-title {
    display: none;
  }
  .hero h1 .short-title {
    display: block;
  }
}

@media (min-width: 769px) {
  .hero h1 .full-title {
    display: block;
  }
  .hero h1 .short-title {
    display: none;
  }
}
```

**Problems:**
- `display: none` on mobile removes the technical specifications from the indexed version of the page
- Different heading text per viewport means Google indexes the shorter mobile heading, not the more descriptive desktop version
- Structured data referencing desktop-only content creates a mismatch that Google may penalize
- Hidden images on mobile are not indexed, losing image search traffic

## Correct

```html
<!-- ✅ Good: same content on both versions, responsive layout only -->
<div class="product-details">
  <h1>Premium Running Shoes</h1>
  <p>Lightweight and responsive design.</p>

  <div class="specs">
    <h2>Technical Specifications</h2>
    <table>
      <tr><td>Weight</td><td>245g</td></tr>
      <tr><td>Drop</td><td>8mm</td></tr>
      <tr><td>Cushioning</td><td>React foam</td></tr>
    </table>
  </div>
</div>
```

```css
/* ✅ Good: CSS changes layout and presentation, never removes content */
.product-details {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 768px) {
  .product-details {
    grid-template-columns: 1fr 1fr;
  }
}

/* Reorder sections visually without hiding them */
.specs {
  order: 2;
}

@media (min-width: 768px) {
  .specs {
    order: 1;
  }
}

/* Use a scrollable container instead of hiding wide tables */
.specs table {
  width: 100%;
}

@media (max-width: 768px) {
  .specs {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
```

```tsx
// ✅ Good: same content rendered for all viewports, styled responsively
export default function ProductSpecs({ specs }: { specs: { label: string; value: string }[] }) {
  return (
    <section className="specs">
      <h2>Technical Specifications</h2>
      <div className="specs-table-wrapper">
        <table>
          <tbody>
            {specs.map((spec) => (
              <tr key={spec.label}>
                <td>{spec.label}</td>
                <td>{spec.value}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}
```

**Benefits:**
- All content is present in the DOM for both mobile and desktop, ensuring Google indexes everything
- CSS Grid and `order` allow visual rearrangement without removing elements from the document flow
- Horizontal scrolling for wide tables preserves all data on small screens without hiding it
- Consistent headings across viewports mean Google always indexes the same, descriptive title
- Structured data matches the visible content on both versions, avoiding indexing penalties

Reference: [Mobile-first indexing best practices](https://developers.google.com/search/docs/crawling-indexing/mobile/mobile-sites-mobile-first-indexing)


---

## Mobile UX Requirements for SEO

**Impact: MEDIUM (Google penalizes poor mobile UX)**

Google evaluates mobile user experience as part of its page experience signals. Pages with tiny tap targets, text requiring pinch-to-zoom, or intrusive interstitials may be demoted in mobile search results. Meeting these UX thresholds improves both rankings and real user engagement.

## Incorrect

```html
<!-- ❌ Bad: tiny tap targets, small font, full-screen popup on load -->
<nav class="mobile-nav">
  <a href="/home" class="nav-link">Home</a>
  <a href="/products" class="nav-link">Products</a>
  <a href="/contact" class="nav-link">Contact</a>
</nav>

<!-- Intrusive interstitial that covers the entire page on load -->
<div id="popup-overlay" class="fullscreen-popup">
  <div class="popup-content">
    <h2>Sign up for our newsletter!</h2>
    <input type="email" placeholder="Email" />
    <button>Subscribe</button>
    <span class="close-btn" onclick="closePopup()">x</span>
  </div>
</div>
```

```css
/* ❌ Bad: tiny, crowded tap targets and small text */
.nav-link {
  padding: 4px 6px;
  margin: 0;
  font-size: 11px;
}

.fullscreen-popup {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn {
  font-size: 12px;
  padding: 2px;
  cursor: pointer;
}
```

**Problems:**
- Nav links with 4px padding create tap targets far below the 48x48px minimum, causing accidental taps on adjacent links
- 11px font size forces users to pinch-to-zoom to read content, which Google flags as a mobile usability issue
- Full-screen popup on page load is classified as an intrusive interstitial and triggers a ranking penalty
- The close button at 12px with 2px padding is nearly impossible to tap on mobile

## Correct

```html
<!-- ✅ Good: properly sized tap targets, readable text, non-intrusive overlay -->
<nav class="mobile-nav">
  <a href="/home" class="nav-link">Home</a>
  <a href="/products" class="nav-link">Products</a>
  <a href="/contact" class="nav-link">Contact</a>
</nav>

<!-- Non-intrusive banner at the bottom, shown after user engagement -->
<div id="newsletter-banner" class="bottom-banner" hidden>
  <p>Get 10% off your first order. <a href="/subscribe">Subscribe</a></p>
  <button class="close-btn" aria-label="Dismiss newsletter banner">
    <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" />
    </svg>
  </button>
</div>
```

```css
/* ✅ Good: accessible tap targets, readable fonts, non-intrusive overlay */
.nav-link {
  display: inline-flex;
  align-items: center;
  min-height: 48px;
  min-width: 48px;
  padding: 12px 16px;
  margin: 4px;
  font-size: 1rem; /* 16px base */
  line-height: 1.5;
  text-decoration: none;
}

body {
  font-size: 1rem;    /* 16px minimum for body text */
  line-height: 1.5;
}

/* Small text still needs to be readable */
.caption, .footnote {
  font-size: 0.875rem; /* 14px minimum for secondary text */
}

/* Non-intrusive bottom banner */
.bottom-banner {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 12px 16px;
  background: #f8f9fa;
  border-top: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 100;
}

.close-btn {
  min-height: 48px;
  min-width: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  cursor: pointer;
}
```

```javascript
// ✅ Good: show newsletter banner after user engagement, not on page load
document.addEventListener("DOMContentLoaded", () => {
  const banner = document.getElementById("newsletter-banner");
  const closeBtn = banner.querySelector(".close-btn");

  // Show after the user has scrolled 50% of the page
  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        banner.hidden = false;
        observer.disconnect();
      }
    },
    { threshold: 0.5 }
  );

  const midPageMarker = document.querySelector(".page-midpoint");
  if (midPageMarker) observer.observe(midPageMarker);

  closeBtn.addEventListener("click", () => {
    banner.hidden = true;
    localStorage.setItem("newsletter-dismissed", "true");
  });
});
```

**Benefits:**
- 48x48px minimum tap targets with 4px spacing between them prevent accidental taps and pass Google's mobile usability audit
- 16px base font size ensures text is readable without zooming on all mobile devices
- A small bottom banner shown after user engagement is not classified as an intrusive interstitial by Google
- `aria-label` on the close button ensures screen readers can identify its purpose
- Dismissal state is persisted so returning users are not shown the banner again

Reference: [Mobile usability report](https://support.google.com/webmasters/answer/9063469)


---

