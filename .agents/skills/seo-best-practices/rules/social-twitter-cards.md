---
title: Twitter/X Card Meta Tags
impact: HIGH
impactDescription: "Controls how pages appear when shared on Twitter/X"
tags: twitter-cards, social-sharing, twitter
---

## Twitter/X Card Meta Tags

**Impact: HIGH (Controls how pages appear when shared on Twitter/X)**

Twitter Card meta tags control the preview shown when your URL is posted on Twitter/X. A properly configured `summary_large_image` card with a high-quality image significantly increases engagement compared to a plain text link. Twitter falls back to Open Graph tags for `title`, `description`, and `image`, so you only need Twitter-specific tags for the card type and site handle.

## Incorrect

```html
<!-- ❌ Bad: using summary card when large image is better, missing twitter:card -->
<head>
  <meta property="og:title" content="10 Tips for Better Running Form" />
  <meta property="og:description" content="Improve your running technique." />
  <meta property="og:image" content="https://example.com/images/running-tips.jpg" />
  <!-- No twitter:card tag at all - Twitter uses a minimal default preview -->
</head>
```

```html
<!-- ❌ Bad: duplicating all OG values in Twitter tags -->
<head>
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content="10 Tips for Better Running Form" />
  <meta name="twitter:description" content="Improve your running technique." />
  <meta name="twitter:image" content="https://example.com/images/running-tips.jpg" />
</head>
```

**Problems:**
- Without `twitter:card`, Twitter renders a minimal link preview with no image or only a tiny thumbnail
- The `summary` card type shows a small square thumbnail; `summary_large_image` displays a full-width image that drives more engagement
- Duplicating `title`, `description`, and `image` in both OG and Twitter tags is unnecessary since Twitter falls back to OG values

## Correct

```html
<!-- ✅ Good: summary_large_image card, relies on OG for title/description/image -->
<head>
  <!-- Open Graph tags (shared by Facebook, LinkedIn, Discord, and Twitter) -->
  <meta property="og:title" content="10 Tips for Better Running Form" />
  <meta
    property="og:description"
    content="Expert-backed advice to improve your stride, reduce injury risk, and run faster with less effort."
  />
  <meta property="og:image" content="https://example.com/images/running-tips-1200x630.jpg" />
  <meta property="og:image:alt" content="Runner demonstrating proper form on a track" />
  <meta property="og:url" content="https://example.com/blog/running-form-tips" />

  <!-- Twitter-specific: only what Twitter needs beyond OG -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:site" content="@SportShop" />
  <meta name="twitter:creator" content="@CoachMike" />
</head>
```

```tsx
// ✅ React SPA: Twitter card tags with Inertia.js Head component
import { Head } from '@inertiajs/react';

export default function RunningTipsPage() {
  return (
    <>
      <Head>
        <title>10 Tips for Better Running Form</title>
        <meta
          name="description"
          content="Expert-backed advice to improve your stride, reduce injury risk, and run faster."
        />
        <meta property="og:title" content="10 Tips for Better Running Form" />
        <meta
          property="og:description"
          content="Expert-backed advice to improve your stride, reduce injury risk, and run faster."
        />
        <meta
          property="og:image"
          content="https://example.com/images/running-tips-1200x630.jpg"
        />
        <meta
          property="og:image:alt"
          content="Runner demonstrating proper form on a track"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:site" content="@SportShop" />
        <meta name="twitter:creator" content="@CoachMike" />
      </Head>
      <main>
        <h1>10 Tips for Better Running Form</h1>
        {/* Page content */}
      </main>
    </>
  );
}
```

**Benefits:**
- `summary_large_image` displays a full-width image preview that is significantly more engaging than the `summary` card
- `twitter:site` attributes the content to your brand account, building recognition
- `twitter:creator` credits the author and links to their profile
- Relying on OG fallback for `title`, `description`, and `image` avoids duplication and simplifies maintenance

**Validation:** Preview your cards by composing a draft post on [X.com](https://x.com) with the URL. Use [Open Graph Debugger](https://developers.facebook.com/tools/debug/) for Facebook and [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/) for LinkedIn.

Reference: [Twitter Cards documentation](https://developer.x.com/en/docs/twitter-for-websites/cards/overview/abouts-cards)
