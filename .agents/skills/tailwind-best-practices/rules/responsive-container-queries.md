---
id: responsive-container-queries
title: Container Queries
priority: CRITICAL
category: Responsive Design
---

# Container Queries

Use container queries to create components that respond to their parent container's size rather than the viewport.

## Bad Example

```html
<!-- Using viewport-based breakpoints for component layout -->
<div class="card">
  <div class="flex flex-col md:flex-row">
    <img class="w-full md:w-1/3" src="image.jpg" alt="">
    <div class="p-4 md:p-6">
      <h2 class="text-lg md:text-xl">Title</h2>
      <p class="text-sm md:text-base">Description</p>
    </div>
  </div>
</div>

<!-- Component breaks when placed in narrow sidebar -->
<aside class="w-64">
  <!-- Card still uses md: breakpoint based on viewport, not container -->
  <div class="card">...</div>
</aside>
```

## Good Example

```html
<!-- Define a container -->
<div class="@container">
  <div class="flex flex-col @md:flex-row">
    <img class="w-full @md:w-1/3" src="image.jpg" alt="">
    <div class="p-4 @lg:p-6">
      <h2 class="text-lg @md:text-xl">Title</h2>
      <p class="text-sm @md:text-base">Description</p>
    </div>
  </div>
</div>

<!-- Named containers for nested queries -->
<div class="@container/sidebar">
  <div class="@container/card">
    <div class="@lg/card:flex-row @xl/sidebar:gap-8">
      Content adapts to both containers
    </div>
  </div>
</div>

<!-- Inline size container (width only) -->
<div class="@container/main inline-size">
  <article class="@sm/main:columns-2 @lg/main:columns-3">
    Multi-column text
  </article>
</div>
```

## Why

1. **True component reusability**: Components adapt to their container regardless of where they're placed in the layout.

2. **Sidebar-aware components**: Cards and widgets behave correctly in narrow sidebars without viewport hacks.

3. **Design system flexibility**: Build components once that work in any context.

4. **Better composition**: Nested components can each respond to their own container.

5. **Future-proof**: Container queries are the modern approach to responsive components.

## Container Query Breakpoints

| Prefix | Min-width |
|--------|-----------|
| `@xs`  | 20rem (320px) |
| `@sm`  | 24rem (384px) |
| `@md`  | 28rem (448px) |
| `@lg`  | 32rem (512px) |
| `@xl`  | 36rem (576px) |
| `@2xl` | 42rem (672px) |
| `@3xl` | 48rem (768px) |
| `@4xl` | 56rem (896px) |
| `@5xl` | 64rem (1024px) |
| `@6xl` | 72rem (1152px) |
| `@7xl` | 80rem (1280px) |

## Configuration

### v3 — requires plugin

```bash
npm install @tailwindcss/container-queries
```

```js
// tailwind.config.js
module.exports = {
  plugins: [require('@tailwindcss/container-queries')],
  theme: {
    containers: {
      'xs': '20rem',
      'sm': '24rem',
      'md': '28rem',
      'lg': '32rem',
      'xl': '36rem',
      // Add custom sizes
      'prose': '65ch',
    },
  },
}
```

### v4 — built into core, no plugin needed

Container queries are native in v4. No plugin to install:

```css
@import "tailwindcss";

/* Custom container sizes via @theme */
@theme {
  --container-prose: 65ch;
}
```

v4 also adds range variants for container queries:

```html
<!-- Min-width container query (same as v3) -->
<div class="@container">
  <div class="@md:flex-row flex-col">...</div>
</div>

<!-- Max-width container query (v4 only) -->
<div class="@container">
  <div class="@max-sm:flex-col">Only stacks below @sm</div>
</div>

<!-- Range: between two sizes (v4 only) -->
<div class="@container">
  <div class="@min-sm:@max-lg:grid-cols-2">2 cols between sm and lg</div>
</div>
```
