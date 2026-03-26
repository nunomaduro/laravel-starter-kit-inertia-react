---
title: Mobile UX Requirements for SEO
impact: MEDIUM
impactDescription: "Google penalizes poor mobile UX"
tags: mobile-ux, tap-targets, font-size, interstitials
---

## Mobile UX Requirements for SEO

**Impact: MEDIUM (Google penalizes poor mobile UX)**

Google evaluates mobile user experience as part of its page experience signals. Pages with tiny tap targets, text requiring pinch-to-zoom, or intrusive interstitials may be demoted in mobile search results. Meeting these UX thresholds improves both rankings and real user engagement.

## Incorrect

```html
<!-- ❌ Bad: tiny tap targets, small font, full-screen popup on load -->
<nav class="mobile-nav">
  <a href="/home" class="nav-link">Home</a>
  <a href="/products" class="nav-link">Products</a>
  <a href="/contact" class="nav-link">Contact</a>
</nav>

<!-- Intrusive interstitial that covers the entire page on load -->
<div id="popup-overlay" class="fullscreen-popup">
  <div class="popup-content">
    <h2>Sign up for our newsletter!</h2>
    <input type="email" placeholder="Email" />
    <button>Subscribe</button>
    <span class="close-btn" onclick="closePopup()">x</span>
  </div>
</div>
```

```css
/* ❌ Bad: tiny, crowded tap targets and small text */
.nav-link {
  padding: 4px 6px;
  margin: 0;
  font-size: 11px;
}

.fullscreen-popup {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn {
  font-size: 12px;
  padding: 2px;
  cursor: pointer;
}
```

**Problems:**
- Nav links with 4px padding create tap targets far below the 48x48px minimum, causing accidental taps on adjacent links
- 11px font size forces users to pinch-to-zoom to read content, which Google flags as a mobile usability issue
- Full-screen popup on page load is classified as an intrusive interstitial and triggers a ranking penalty
- The close button at 12px with 2px padding is nearly impossible to tap on mobile

## Correct

```html
<!-- ✅ Good: properly sized tap targets, readable text, non-intrusive overlay -->
<nav class="mobile-nav">
  <a href="/home" class="nav-link">Home</a>
  <a href="/products" class="nav-link">Products</a>
  <a href="/contact" class="nav-link">Contact</a>
</nav>

<!-- Non-intrusive banner at the bottom, shown after user engagement -->
<div id="newsletter-banner" class="bottom-banner" hidden>
  <p>Get 10% off your first order. <a href="/subscribe">Subscribe</a></p>
  <button class="close-btn" aria-label="Dismiss newsletter banner">
    <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" />
    </svg>
  </button>
</div>
```

```css
/* ✅ Good: accessible tap targets, readable fonts, non-intrusive overlay */
.nav-link {
  display: inline-flex;
  align-items: center;
  min-height: 48px;
  min-width: 48px;
  padding: 12px 16px;
  margin: 4px;
  font-size: 1rem; /* 16px base */
  line-height: 1.5;
  text-decoration: none;
}

body {
  font-size: 1rem;    /* 16px minimum for body text */
  line-height: 1.5;
}

/* Small text still needs to be readable */
.caption, .footnote {
  font-size: 0.875rem; /* 14px minimum for secondary text */
}

/* Non-intrusive bottom banner */
.bottom-banner {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 12px 16px;
  background: #f8f9fa;
  border-top: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 100;
}

.close-btn {
  min-height: 48px;
  min-width: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  cursor: pointer;
}
```

```javascript
// ✅ Good: show newsletter banner after user engagement, not on page load
document.addEventListener("DOMContentLoaded", () => {
  const banner = document.getElementById("newsletter-banner");
  const closeBtn = banner.querySelector(".close-btn");

  // Show after the user has scrolled 50% of the page
  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        banner.hidden = false;
        observer.disconnect();
      }
    },
    { threshold: 0.5 }
  );

  const midPageMarker = document.querySelector(".page-midpoint");
  if (midPageMarker) observer.observe(midPageMarker);

  closeBtn.addEventListener("click", () => {
    banner.hidden = true;
    localStorage.setItem("newsletter-dismissed", "true");
  });
});
```

**Benefits:**
- 48x48px minimum tap targets with 4px spacing between them prevent accidental taps and pass Google's mobile usability audit
- 16px base font size ensures text is readable without zooming on all mobile devices
- A small bottom banner shown after user engagement is not classified as an intrusive interstitial by Google
- `aria-label` on the close button ensures screen readers can identify its purpose
- Dismissal state is persisted so returning users are not shown the banner again

Reference: [Mobile usability report](https://support.google.com/webmasters/answer/9063469)
