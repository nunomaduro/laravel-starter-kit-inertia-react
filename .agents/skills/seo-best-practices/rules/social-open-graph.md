---
title: Open Graph Meta Tags
impact: HIGH
impactDescription: "Controls how pages appear on Facebook, LinkedIn, Discord"
tags: open-graph, og-tags, social-sharing, facebook
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
