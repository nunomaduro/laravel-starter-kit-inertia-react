---
id: v4-migration
title: Migrating from Tailwind v3 to v4
priority: HIGH
category: V4 & Migration
---

## Why It Matters

Tailwind v4 is a full rewrite with a CSS-first architecture. Most v3 projects can be upgraded automatically using the official upgrade tool, but understanding the breaking changes prevents surprises and helps when the tool cannot auto-migrate everything.

## Step 1 — Run the Upgrade Tool

Always start here. It handles most of the migration automatically:

```bash
npx @tailwindcss/upgrade
```

This will:
- Update dependencies (`tailwindcss`, add `@tailwindcss/vite` or `@tailwindcss/postcss`)
- Convert `tailwind.config.js` → `@theme {}` in CSS
- Rename deprecated utilities in template files
- Replace `@tailwind` directives with `@import "tailwindcss"`

> Requires Node.js 20+.

## Step 2 — Manual Changes

The upgrade tool handles most cases, but verify these manually:

### 1. Remove `tailwind.config.js` — use `@theme {}` in CSS

```js
// ❌ Before (v3)
module.exports = {
  content: ['./resources/**/*.{blade.php,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: { brand: '#3b82f6' },
      fontFamily: { display: ['Satoshi', 'sans-serif'] },
    },
  },
  plugins: [require('@tailwindcss/forms')],
}
```

```css
/* ✅ After (v4) — app.css */
@import "tailwindcss";
@plugin "@tailwindcss/forms";

@theme {
  --color-brand: #3b82f6;
  --font-display: "Satoshi", sans-serif;
}
```

### 2. Replace `@tailwind` directives

```css
/* ❌ v3 */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* ✅ v4 */
@import "tailwindcss";
```

### 3. Renamed utilities — update in all template files

| v3 class | v4 class | Change |
|----------|----------|--------|
| `shadow-sm` | `shadow-xs` | Scale shifted down |
| `shadow` | `shadow-sm` | Bare → named |
| `shadow-md` | `shadow-md` | No change |
| `blur-sm` | `blur-xs` | Scale shifted down |
| `blur` | `blur-sm` | Bare → named |
| `rounded-sm` | `rounded-xs` | Scale shifted down |
| `rounded` | `rounded-sm` | Bare → named |
| `outline-none` | `outline-hidden` | Semantic rename |
| `ring` | `ring-3` | Bare → explicit width |
| `transform-none` | `scale-none` / `rotate-none` | Individual properties |

### 4. Transform utilities — individual properties

```html
<!-- ❌ v3 -->
<button class="scale-150 focus:transform-none">
<button class="transition-[opacity,transform] hover:scale-150">

<!-- ✅ v4 -->
<button class="scale-150 focus:scale-none">
<button class="transition-[opacity,scale] hover:scale-150">
```

### 5. Grid arbitrary values — underscores instead of commas

```html
<!-- ❌ v3 -->
<div class="grid-cols-[max-content,auto]">

<!-- ✅ v4 -->
<div class="grid-cols-[max-content_auto]">
```

### 6. Replace JS plugins with CSS directives

```js
// ❌ v3 — tailwind.config.js
plugins: [
  plugin(({ addUtilities, addVariant }) => {
    addUtilities({ '.scrollbar-hide': { 'scrollbar-width': 'none' } })
    addVariant('hocus', ['&:hover', '&:focus'])
  }),
]
```

```css
/* ✅ v4 — app.css */
@utility scrollbar-hide {
  scrollbar-width: none;
}

@custom-variant hocus (&:hover, &:focus);
```

### 7. Replace `@layer components` with `@utility`

```css
/* ❌ v3 */
@layer components {
  .btn { border-radius: 0.5rem; padding: 0.5rem 1rem; }
}

/* ✅ v4 — @utility sorts by property count for correct specificity */
@utility btn {
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
}
```

### 8. Plugins removed in v4 (built into core)

These v3 plugins are no longer needed — their features are native in v4:

| v3 Plugin | v4 Status |
|-----------|-----------|
| `@tailwindcss/aspect-ratio` | Native `aspect-*` utilities |
| `@tailwindcss/container-queries` | Native `@container` support with `@min-*`/`@max-*` range variants |

## Step 3 — New v4 Features to Adopt

These are new in v4 — not breaking changes, but worth adopting:

```html
<!-- Dynamic values — no config needed -->
<div class="grid-cols-15 px-17 w-23">

<!-- 3D transforms -->
<div class="perspective-distant rotate-x-12 transform-3d">

<!-- Composable variants -->
<div class="group-has-focus:opacity-100">

<!-- not-* variant -->
<div class="not-hover:opacity-50">

<!-- inert variant -->
<div class="inert:opacity-30">

<!-- field-sizing for auto-growing inputs -->
<textarea class="field-sizing-content">

<!-- starting: variant for CSS @starting-style (animate initial appearance) -->
<div popover class="transition-discrete starting:open:opacity-0 open:opacity-100">

<!-- forced-colors: variant for Windows High Contrast accessibility -->
<input type="checkbox" class="appearance-none forced-colors:appearance-auto">

<!-- color-mix for opacity modifiers (works with CSS variables) -->
<div class="bg-blue-500/50">
  <!-- v4 uses color-mix(in oklab, ...) under the hood -->

<!-- @variant directive for nesting variants in custom CSS -->
```

```css
/* Nest variants in custom CSS */
.my-card {
  background: white;
  @variant dark {
    background: #1e293b;
    @variant hover {
      background: #334155;
    }
  }
}
```

## Checklist

- [ ] Run `npx @tailwindcss/upgrade`
- [ ] Remove `tailwind.config.js` (or keep for remaining v3 projects)
- [ ] Replace `@tailwind` directives with `@import "tailwindcss"`
- [ ] Verify renamed utilities: shadow, blur, rounded, outline, ring
- [ ] Fix transform utilities: `transform-none` → individual resets
- [ ] Fix grid arbitrary values: commas → underscores
- [ ] Convert JS plugins to `@utility` / `@custom-variant` in CSS
- [ ] Switch official plugins to `@plugin` imports in CSS
- [ ] Remove `@tailwindcss/aspect-ratio` and `@tailwindcss/container-queries` plugins (now in core)
- [ ] Replace `@layer components` with `@utility` for custom component classes
- [ ] Run `npm run build` and check for warnings

Reference: https://tailwindcss.com/docs/upgrade-guide
