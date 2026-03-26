---
section: page-components
priority: critical
description: Manage document head with title and meta tags using Inertia's Head component
keywords: [head, title, meta, seo, document, open-graph]
---

# Page Head Management

Use Inertia's Head component to manage document head elements like title, meta tags, and Open Graph data on a per-page basis.

## Bad Example

```tsx
// Anti-pattern: Using document.title directly
import { useEffect } from 'react';

export default function ProductPage({ product }) {
  useEffect(() => {
    document.title = product.name + ' | My Store';
  }, [product.name]);

  return <div>{product.name}</div>;
}

// Anti-pattern: Missing head management entirely
export default function ProductPage({ product }) {
  return (
    <div>
      <h1>{product.name}</h1>
    </div>
  );
}
```

## Good Example

```tsx
// resources/js/Pages/Products/Show.tsx
import { Head } from '@inertiajs/react';

interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  image_url: string;
  category: string;
}

interface ProductShowProps {
  product: Product;
}

export default function Show({ product }: ProductShowProps) {
  return (
    <>
      <Head>
        <title>{product.name}</title>
        <meta name="description" content={product.description.substring(0, 160)} />

        {/* Open Graph */}
        <meta property="og:title" content={product.name} />
        <meta property="og:description" content={product.description.substring(0, 160)} />
        <meta property="og:image" content={product.image_url} />
        <meta property="og:type" content="product" />

        {/* Twitter Card */}
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content={product.name} />
        <meta name="twitter:description" content={product.description.substring(0, 160)} />
        <meta name="twitter:image" content={product.image_url} />

        {/* Structured Data */}
        <script type="application/ld+json">
          {JSON.stringify({
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: product.name,
            description: product.description,
            image: product.image_url,
            offers: {
              '@type': 'Offer',
              price: product.price,
              priceCurrency: 'USD',
            },
          })}
        </script>
      </Head>

      <div className="product-page">
        <h1>{product.name}</h1>
        <p>{product.description}</p>
      </div>
    </>
  );
}

// Simple usage with title shorthand
export default function About() {
  return (
    <>
      <Head title="About Us" />
      <div>About page content</div>
    </>
  );
}
```

## Why

Using Inertia's Head component is crucial for:

1. **SEO**: Search engines need proper titles and meta descriptions to index pages correctly
2. **Social Sharing**: Open Graph and Twitter Card meta tags control how pages appear when shared
3. **SSR Compatibility**: Head component works with server-side rendering, unlike direct DOM manipulation
4. **Automatic Cleanup**: Inertia automatically removes head elements when navigating away
5. **Template Support**: You can set a title template in app.tsx like `titleTemplate="%s | My App"`
6. **Accessibility**: Proper page titles help screen reader users understand page context
