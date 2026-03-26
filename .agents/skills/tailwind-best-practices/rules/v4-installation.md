---
id: v4-installation
title: V4 Installation & Setup
priority: HIGH
category: V4 & Migration
---

## Why It Matters

Tailwind CSS v4 ships as a dedicated Vite plugin (`@tailwindcss/vite`) or PostCSS plugin (`@tailwindcss/postcss`). The old `tailwindcss` + `postcss` + `autoprefixer` setup no longer applies. Configuration moves entirely out of JavaScript and into CSS via `@import "tailwindcss"`.

## Incorrect

```bash
# ❌ v3 install — wrong for v4
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

```js
// ❌ v3 postcss.config.js — not needed in v4 with Vite
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

```css
/* ❌ v3 directives — do not use in v4 */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

## Correct

### With Vite (recommended)

```bash
npm install tailwindcss @tailwindcss/vite
```

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [tailwindcss()],
})
```

```css
/* app.css — single import replaces all three @tailwind directives */
@import "tailwindcss";
```

### With PostCSS (Laravel Mix, Webpack, etc.)

```bash
npm install tailwindcss @tailwindcss/postcss
```

```js
// postcss.config.js
module.exports = {
  plugins: {
    '@tailwindcss/postcss': {},
  },
}
```

```css
/* app.css */
@import "tailwindcss";
```

### Limiting scan scope with @source

By default, v4 auto-detects your template files. Override with `@source` if needed:

```css
@import "tailwindcss";

/* Only scan specific paths */
@source "./resources/js/**/*.{ts,tsx}";
@source "./resources/views/**/*.blade.php";

/* Exclude paths from scanning */
@source not "./resources/js/legacy/**";
```

### Importing without emitting CSS with @reference

Use `@reference` when you need access to theme values or `@apply` in a secondary CSS file without duplicating Tailwind's output:

```css
/* components/card.css — not the main entry point */
@reference "tailwindcss";

.card {
  @apply rounded-lg bg-white shadow-sm dark:bg-gray-800;
}
```

## Recommended Patterns

| Scenario | Tool | Install |
|----------|------|---------|
| React + Vite | `@tailwindcss/vite` | `npm i tailwindcss @tailwindcss/vite` |
| Laravel + Vite | `@tailwindcss/vite` | `npm i tailwindcss @tailwindcss/vite` |
| PostCSS-based | `@tailwindcss/postcss` | `npm i tailwindcss @tailwindcss/postcss` |
| CLI only | `@tailwindcss/cli` | `npm i tailwindcss @tailwindcss/cli` |

Reference: https://tailwindcss.com/docs/installation
