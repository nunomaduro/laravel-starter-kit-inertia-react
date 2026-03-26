---
id: v4-custom-utilities
title: V4 Custom Utilities and Variants
priority: HIGH
category: V4 & Migration
---

## Why It Matters

Tailwind v4 replaces JavaScript plugins with CSS-native directives: `@utility` for custom utility classes and `@custom-variant` for custom variants. No `plugin()` API, no JavaScript — everything lives in your CSS file.

## Incorrect

```js
// ❌ v3 plugin API — does not work in v4
const plugin = require('tailwindcss/plugin')

module.exports = {
  plugins: [
    plugin(function ({ addUtilities, addVariant }) {
      addUtilities({
        '.scrollbar-hide': { '-ms-overflow-style': 'none', 'scrollbar-width': 'none' },
      })
      addVariant('hocus', ['&:hover', '&:focus'])
    }),
  ],
}
```

## Correct

### Custom utilities with @utility

```css
@import "tailwindcss";

/* Simple custom utility */
@utility scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;

  &::-webkit-scrollbar {
    display: none;
  }
}

/* Utility with variant support */
@utility text-balance {
  text-wrap: balance;
}
```

```html
<!-- Use like any built-in utility -->
<div class="scrollbar-hide overflow-y-auto">
<p class="text-balance">
```

### Custom variants with @custom-variant

```css
@import "tailwindcss";

/* Combine hover + focus into one variant */
@custom-variant hocus (&:hover, &:focus);

/* Target elements inside a specific parent */
@custom-variant sidebar-open (.sidebar-open &);

/* Dark mode scoped to a class (if you need custom selector) */
@custom-variant dark (&:where(.dark, .dark *));

/* RTL support */
@custom-variant rtl ([dir="rtl"] &);
```

```html
<!-- hocus: applies on both hover and focus -->
<button class="hocus:bg-blue-600 hocus:text-white">

<!-- sidebar-open: applies when ancestor has .sidebar-open -->
<nav class="sidebar-open:translate-x-0 -translate-x-full">
```

### Using official plugins in v4

```bash
npm install @tailwindcss/typography @tailwindcss/forms
```

```css
/* app.css — import plugins directly in CSS */
@import "tailwindcss";
@plugin "@tailwindcss/typography";
@plugin "@tailwindcss/forms";
```

### Nesting variants in custom CSS with @variant

Use `@variant` to apply Tailwind variants inside custom CSS blocks:

```css
.my-element {
  background: white;

  /* Nest multiple variants */
  @variant dark {
    background: #1e293b;

    @variant hover {
      background: #334155;
    }
  }

  /* Single variant */
  @variant focus {
    outline: 2px solid var(--color-blue-500);
  }
}
```

### Replacing @layer components with @utility

In v3, custom component classes used `@layer components`. In v4, use `@utility` instead — it sorts based on property count so utilities can override it:

```css
/* ❌ v3 */
@layer components {
  .btn {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: ButtonFace;
  }
}

/* ✅ v4 */
@utility btn {
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: ButtonFace;
}
```

### Importing without emitting CSS with @reference

Use `@reference` to access theme values and utilities for `@apply` without duplicating Tailwind's output. Useful in CSS files that are not your main entry point:

```css
/* components/card.css */
@reference "tailwindcss";

.card {
  @apply rounded-lg bg-white shadow-sm p-6;
}
```

## Recommended Patterns

| v3 (JS plugin API) | v4 (CSS directive) |
|--------------------|--------------------|
| `addUtilities({ '.foo': {...} })` | `@utility foo { ... }` |
| `addVariant('bar', '...')` | `@custom-variant bar (...)` |
| `@layer components { .foo {...} }` | `@utility foo { ... }` |
| Nesting variants in JS | `@variant dark { @variant hover { ... } }` |
| `require('@tailwindcss/forms')` | `@plugin "@tailwindcss/forms"` |
| `require('@tailwindcss/typography')` | `@plugin "@tailwindcss/typography"` |
| `@import` for `@apply` access | `@reference "tailwindcss"` (no CSS output) |

Reference: https://tailwindcss.com/docs/adding-custom-styles
