---
title: JSON-LD Structured Data Basics
impact: HIGH
impactDescription: "Enables rich results in search (stars, prices, FAQs)"
tags: json-ld, schema-org, structured-data, rich-results
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
