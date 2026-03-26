---
title: Dynamic Meta Tag Management in SPAs
impact: HIGH
impactDescription: "Every route must have unique, server-rendered meta tags"
tags: meta-tags, inertia-head, spa
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
