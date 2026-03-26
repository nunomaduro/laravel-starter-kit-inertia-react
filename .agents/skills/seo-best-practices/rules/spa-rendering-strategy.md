---
title: Rendering Strategy for SEO
impact: HIGH
impactDescription: "Wrong rendering strategy can make content invisible to search engines"
tags: ssr, csr, rendering, react, laravel, inertia
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
