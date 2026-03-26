---
id: responsive-grid-system
title: Responsive Grid System
priority: CRITICAL
category: Responsive Design
---

# Responsive Grid System

Use CSS Grid with Tailwind for flexible, responsive layouts that adapt to different screen sizes and content needs.

## Bad Example

```html
<!-- Using floats (outdated) -->
<div class="clearfix">
  <div class="float-left w-1/3">Column 1</div>
  <div class="float-left w-1/3">Column 2</div>
  <div class="float-left w-1/3">Column 3</div>
</div>

<!-- Fixed column widths that don't adapt -->
<div class="flex">
  <div class="w-64">Sidebar</div>
  <div class="w-[calc(100%-16rem)]">Content</div>
</div>

<!-- Too many breakpoint-specific classes -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
  <!-- Items -->
</div>
```

## Good Example

```html
<!-- Auto-fit grid that adapts to available space -->
<div class="grid grid-cols-[repeat(auto-fit,minmax(250px,1fr))] gap-6">
  <div>Card 1</div>
  <div>Card 2</div>
  <div>Card 3</div>
  <div>Card 4</div>
</div>

<!-- Responsive 12-column grid -->
<div class="grid grid-cols-12 gap-4">
  <aside class="col-span-12 md:col-span-3 lg:col-span-2">
    Sidebar
  </aside>
  <main class="col-span-12 md:col-span-9 lg:col-span-7">
    Main content
  </main>
  <aside class="col-span-12 lg:col-span-3">
    Secondary sidebar
  </aside>
</div>

<!-- Named grid areas for complex layouts -->
<div class="grid grid-cols-1 md:grid-cols-[200px_1fr_200px] grid-rows-[auto_1fr_auto] min-h-screen gap-4">
  <header class="md:col-span-3 bg-gray-100">Header</header>
  <nav class="bg-gray-200">Navigation</nav>
  <main class="bg-white">Main Content</main>
  <aside class="bg-gray-200">Sidebar</aside>
  <footer class="md:col-span-3 bg-gray-100">Footer</footer>
</div>

<!-- Fluid grid with minimum item size -->
<div class="grid grid-cols-[repeat(auto-fill,minmax(min(100%,300px),1fr))] gap-6">
  <!-- Cards that never break on small screens -->
</div>
```

## Why

1. **True two-dimensional layout**: Grid handles both rows and columns simultaneously.

2. **Content-aware sizing**: `auto-fit` and `auto-fill` create responsive grids without breakpoints.

3. **Gap management**: `gap` utilities handle spacing consistently without margin hacks.

4. **Alignment control**: Easy vertical and horizontal alignment with `place-items` and `place-content`.

5. **Named lines and areas**: Complex layouts become readable and maintainable.

## Grid Patterns

### Auto-Responsive Cards
```html
<div class="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-4">
  <!-- Cards automatically wrap and resize -->
</div>
```

### Holy Grail Layout
```html
<div class="grid grid-cols-1 md:grid-cols-[250px_1fr_250px] grid-rows-[auto_1fr_auto] min-h-screen">
  <header class="md:col-span-3">Header</header>
  <nav>Nav</nav>
  <main>Content</main>
  <aside>Sidebar</aside>
  <footer class="md:col-span-3">Footer</footer>
</div>
```

### Masonry-like Grid
```html
<div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-4 space-y-4">
  <div class="break-inside-avoid">Item 1</div>
  <div class="break-inside-avoid">Item 2</div>
  <!-- Items of varying heights -->
</div>
```

### Feature Grid
```html
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <div class="col-span-2 row-span-2">Featured</div>
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
  <div>Item 4</div>
</div>
```

## Configuration

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      gridTemplateColumns: {
        'sidebar': '250px 1fr',
        'sidebar-right': '1fr 300px',
        'footer': '200px minmax(900px, 1fr) 200px',
      },
      gridTemplateRows: {
        'layout': 'auto 1fr auto',
      },
    },
  },
}
```
