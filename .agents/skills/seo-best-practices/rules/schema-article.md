---
title: Article Schema Markup
impact: HIGH
impactDescription: "Enables article rich results with author and date"
tags: article, blog, structured-data, rich-results
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
