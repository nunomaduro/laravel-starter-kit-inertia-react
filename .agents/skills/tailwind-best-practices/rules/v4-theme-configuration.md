---
id: v4-theme-configuration
title: V4 Theme Configuration with @theme
priority: HIGH
category: V4 & Migration
---

## Why It Matters

In Tailwind v4, `tailwind.config.js` is gone. All theme customisation happens in CSS using the `@theme` directive. Values defined in `@theme` become both CSS custom properties and Tailwind utility classes automatically — no JavaScript configuration required.

## Incorrect

```js
// ❌ tailwind.config.js does not exist in v4
module.exports = {
  theme: {
    extend: {
      colors: { brand: '#3b82f6' },
      fontFamily: { display: ['Satoshi', 'sans-serif'] },
    },
  },
}
```

## Correct

### Extend the default theme

```css
/* app.css */
@import "tailwindcss";

@theme {
  /* Colors — generates bg-brand-*, text-brand-*, border-brand-* etc. */
  --color-brand-500: #3b82f6;
  --color-brand-600: #2563eb;

  /* Fonts — generates font-display, font-mono etc. */
  --font-display: "Satoshi", sans-serif;
  --font-sans: "Inter", sans-serif;

  /* Breakpoints — generates 3xl:* variant */
  --breakpoint-3xl: 1920px;

  /* Spacing — generates p-18, m-18, w-18 etc. */
  --spacing-18: 4.5rem;

  /* Shadows — generates shadow-soft */
  --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);

  /* Custom animations */
  --animate-fade-in: fade-in 0.3s ease-out;

  @keyframes fade-in {
    from { opacity: 0; }
    to   { opacity: 1; }
  }
}
```

### Override an entire namespace (remove defaults)

```css
@import "tailwindcss";

@theme {
  /* Reset all default colors, then define only yours */
  --color-*: initial;
  --color-white: #fff;
  --color-black: #000;
  --color-primary: #3f3cbb;
  --color-secondary: #121063;
}
```

### Use theme values in arbitrary utilities

```html
<!-- Generated utility class -->
<div class="bg-brand-500 font-display shadow-soft">

<!-- Or reference the CSS variable directly -->
<div class="bg-[var(--color-brand-500)]">
```

### Reference theme variables in custom CSS

```css
.my-component {
  color: var(--color-brand-500);
  font-family: var(--font-display);
  padding: var(--spacing-18);
}
```

## Recommended Patterns

| v3 config | v4 @theme equivalent |
|-----------|---------------------|
| `colors.brand` | `--color-brand-*` |
| `fontFamily.display` | `--font-display` |
| `screens['3xl']` | `--breakpoint-3xl` |
| `spacing['18']` | `--spacing-18` |
| `boxShadow.soft` | `--shadow-soft` |
| `keyframes` + `animation` | `--animate-*` + `@keyframes` inside `@theme` |

Reference: https://tailwindcss.com/docs/theme
