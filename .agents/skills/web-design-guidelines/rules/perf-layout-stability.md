---
title: Prevent Cumulative Layout Shift
impact: MEDIUM
impactDescription: "Layout shifts frustrate users and hurt Core Web Vitals"
tags: performance, cls, layout-shift, skeleton, aspect-ratio
---

## Prevent Cumulative Layout Shift

**Impact: MEDIUM (Layout shifts frustrate users and hurt Core Web Vitals)**

Cumulative Layout Shift (CLS) measures unexpected movement of visible page content. High CLS causes misclicks, reading disruption, and user frustration. Reserve space for dynamic content, stabilize fonts, and use skeletons to keep layouts predictable.

## Incorrect

```css
/* ❌ Bad: no space reserved for dynamic content */
.ad-banner {
  /* No dimensions — shifts page when ad loads */
}

.video-wrapper {
  /* No aspect ratio — collapses until video loads */
}

/* ❌ Bad: font swap without size adjustment causes text reflow */
@font-face {
  font-family: 'BrandFont';
  src: url('/fonts/brand.woff2') format('woff2');
  font-display: swap;
  /* No size-adjust — fallback font has different metrics */
}
```

```tsx
// ❌ Bad: dynamic content injected without reserved space
function Feed() {
  const [banner, setBanner] = useState<Banner | null>(null)

  useEffect(() => {
    fetchBanner().then(setBanner)
  }, [])

  return (
    <div>
      <h1>Latest News</h1>
      {/* Banner pops in and pushes content down */}
      {banner && (
        <div>
          <img src={banner.image} alt={banner.alt} />
        </div>
      )}
      <ArticleList />
    </div>
  )
}

// ❌ Bad: embed without dimensions
function VideoSection() {
  return (
    <div>
      <iframe
        src="https://www.youtube.com/embed/dQw4w9WgXcQ"
        title="Video"
      />
    </div>
  )
}
```

**Problems:**
- Ads and banners without reserved space push content down when they load
- Embeds and media without dimensions collapse to zero height then expand
- Font swap without `size-adjust` causes text to reflow as the web font loads (FOUT)
- Users accidentally click the wrong element when content shifts beneath their cursor

## Correct

```css
/* ✅ Good: reserve space for media with aspect-ratio */
.video-wrapper {
  aspect-ratio: 16 / 9;
  width: 100%;
  background-color: #f0f0f0;
}

.video-wrapper iframe {
  width: 100%;
  height: 100%;
}

/* ✅ Good: reserve fixed space for ad banners */
.ad-banner {
  min-height: 250px;
  width: 100%;
  background-color: #fafafa;
  contain: layout;
}

/* ✅ Good: font swap with size-adjust to match fallback metrics */
@font-face {
  font-family: 'BrandFont';
  src: url('/fonts/brand.woff2') format('woff2');
  font-display: swap;
  size-adjust: 105%;
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}

/* ✅ Good: aspect-ratio for responsive images */
.thumbnail {
  aspect-ratio: 4 / 3;
  width: 100%;
  object-fit: cover;
  background-color: #e5e7eb;
}

/* ✅ Good: skeleton placeholder animation */
.skeleton {
  background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

```tsx
// ✅ Good: skeleton placeholder reserves space for async content
function BannerSkeleton() {
  return (
    <div
      className="w-full rounded-lg bg-gray-200 animate-pulse"
      style={{ minHeight: 200 }}
      aria-hidden="true"
    />
  )
}

function Feed() {
  const [banner, setBanner] = useState<Banner | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchBanner()
      .then(setBanner)
      .finally(() => setIsLoading(false))
  }, [])

  return (
    <div>
      <h1>Latest News</h1>
      {/* Space is always reserved — no layout shift */}
      <div style={{ minHeight: 200 }}>
        {isLoading ? (
          <BannerSkeleton />
        ) : banner ? (
          <img
            src={banner.image}
            alt={banner.alt}
            width={800}
            height={200}
          />
        ) : null}
      </div>
      <ArticleList />
    </div>
  )
}

// ✅ Good: embed with aspect-ratio wrapper
function VideoSection() {
  return (
    <div style={{ aspectRatio: '16 / 9', width: '100%', backgroundColor: '#f0f0f0' }}>
      <iframe
        src="https://www.youtube.com/embed/dQw4w9WgXcQ"
        title="Video"
        width="100%"
        height="100%"
        loading="lazy"
      />
    </div>
  )
}

// ✅ Good: ad slot with reserved dimensions and containment
function AdSlot({ slotId }: { slotId: string }) {
  return (
    <div
      style={{
        minHeight: 250,
        width: '100%',
        contain: 'layout',
        backgroundColor: '#fafafa',
      }}
      data-ad-slot={slotId}
    >
      {/* Ad script injects content here without shifting layout */}
    </div>
  )
}
```

**Benefits:**
- `aspect-ratio` reserves the correct space for media before it loads
- Skeleton placeholders prevent content from jumping as data arrives
- `font-display: swap` with `size-adjust` minimizes text reflow during font loading
- `contain: layout` prevents ad content from affecting surrounding layout
- Reserved `min-height` on dynamic slots keeps content below them stable
- Improved CLS score directly benefits search ranking and user satisfaction

Reference: [web.dev - Cumulative Layout Shift](https://web.dev/articles/cls)
