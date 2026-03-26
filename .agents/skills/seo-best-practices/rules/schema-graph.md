---
title: Combining Multiple Schema Types with @graph
impact: HIGH
impactDescription: "Pages need multiple schema types — @graph combines them cleanly"
tags: schema-org, graph, json-ld, structured-data
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
