---
title: Product Schema for E-Commerce
impact: HIGH
impactDescription: "Enables product rich results with price and availability"
tags: product, e-commerce, structured-data, rich-results
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
