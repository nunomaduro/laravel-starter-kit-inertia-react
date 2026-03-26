---
title: Cumulative Layout Shift Prevention
impact: CRITICAL
impactDescription: "Must be under 0.1 (Google ranking signal)"
tags: core-web-vitals, cls, layout-shift
---

## Cumulative Layout Shift Prevention

**Impact: CRITICAL (Must be under 0.1 (Google ranking signal))**

CLS measures unexpected visual shifts during a page's lifecycle. A CLS score above 0.1 harms both user experience and search rankings, as Google treats it as a Core Web Vital ranking signal. Most layout shifts come from images without dimensions, late-loading fonts, and dynamically injected content.

## Incorrect

```html
<!-- ❌ Bad: images without dimensions, no font strategy, injected content -->
<head>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter&display=block"
    rel="stylesheet"
  />
</head>
<body>
  <header>
    <img src="/logo.png" alt="Company logo" />
  </header>

  <main>
    <article>
      <h1>Latest News</h1>
      <img src="/article-hero.jpg" alt="Article hero" />
      <p>Article content here...</p>
    </article>

    <!-- Ad banner injected above content without reserved space -->
    <div id="ad-banner"></div>
    <script>
      loadAdBanner(document.getElementById("ad-banner"));
    </script>

    <!-- Cookie consent pushes content down -->
    <div class="cookie-banner">
      <p>We use cookies...</p>
    </div>
  </main>
</body>
```

**Problems:**
- Images without `width` and `height` attributes cause the browser to reflow content once dimensions are known
- `font-display: block` causes an invisible text flash (FOIT) followed by a layout shift when the font loads
- The ad banner div has no reserved height, pushing content down when the ad loads
- Cookie consent banner inserted into the document flow shifts all content below it

## Correct

```html
<!-- ✅ Good: explicit dimensions, font strategy, reserved space -->
<head>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter&display=swap"
    rel="stylesheet"
  />
  <style>
    /* Size-adjust fallback to match Inter metrics */
    @font-face {
      font-family: "Inter Fallback";
      src: local("Arial");
      size-adjust: 107%;
      ascent-override: 90%;
      descent-override: 22%;
      line-gap-override: 0%;
    }

    body {
      font-family: "Inter", "Inter Fallback", sans-serif;
    }

    /* Reserve space for ad banner */
    .ad-slot {
      min-height: 250px;
      width: 100%;
      background: #f0f0f0;
    }
  </style>
</head>
<body>
  <header>
    <img src="/logo.png" alt="Company logo" width="180" height="40" />
  </header>

  <main>
    <article>
      <h1>Latest News</h1>
      <img
        src="/article-hero.jpg"
        alt="Article hero"
        width="1200"
        height="630"
        style="aspect-ratio: 1200 / 630; width: 100%; height: auto;"
      />
      <p>Article content here...</p>
    </article>

    <!-- Ad banner with reserved space -->
    <div class="ad-slot" id="ad-banner"></div>
    <script>
      loadAdBanner(document.getElementById("ad-banner"));
    </script>
  </main>

  <!-- Cookie consent as overlay, not in document flow -->
  <div
    class="cookie-banner"
    style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;"
  >
    <p>We use cookies...</p>
  </div>
</body>
```

```tsx
// ✅ React: explicit dimensions and aspect-ratio to prevent layout shift
interface Article {
  title: string;
  heroImage: string;
  heroAlt: string;
  content: string;
}

export default function ArticlePage({ article }: { article: Article }) {
  return (
    <article>
      <h1>{article.title}</h1>
      <img
        src={article.heroImage}
        alt={article.heroAlt}
        width={1200}
        height={630}
        style={{ aspectRatio: "1200 / 630", width: "100%", height: "auto" }}
        fetchPriority="high"
      />
      <div dangerouslySetInnerHTML={{ __html: article.content }} />
    </article>
  );
}
```

**Benefits:**
- Explicit `width` and `height` attributes let the browser calculate aspect ratio before the image loads
- `aspect-ratio` CSS property ensures responsive images maintain their space during layout
- `font-display: swap` with `size-adjust` eliminates both invisible text and font-swap layout shifts
- Fixed positioning on the cookie banner keeps it out of document flow, preventing content shifts
- Reserved `min-height` on ad slots prevents content from jumping when ads load

Reference: [Optimize Cumulative Layout Shift](https://web.dev/articles/optimize-cls)
