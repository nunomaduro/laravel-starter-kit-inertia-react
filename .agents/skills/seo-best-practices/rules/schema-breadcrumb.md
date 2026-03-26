---
title: BreadcrumbList Navigation Markup
impact: HIGH
impactDescription: "Shows navigation path in SERPs (Home > Category > Page)"
tags: breadcrumb, navigation, structured-data
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
