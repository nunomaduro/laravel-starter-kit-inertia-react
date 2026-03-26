---
title: FAQ Page Schema Markup
impact: HIGH
impactDescription: "Enables expandable FAQ accordion in Google search results"
tags: faq, schema-org, structured-data, rich-results
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
