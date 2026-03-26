---
id: responsive-fluid-typography
title: Fluid Typography
priority: CRITICAL
category: Responsive Design
---

# Fluid Typography

Use fluid typography that scales smoothly between breakpoints rather than jumping between fixed sizes.

## Bad Example

```html
<!-- Abrupt size jumps at breakpoints -->
<h1 class="text-2xl md:text-4xl lg:text-6xl">
  Heading with jarring size changes
</h1>

<!-- Fixed sizes that don't adapt smoothly -->
<p class="text-sm md:text-base lg:text-lg">
  Body text with noticeable jumps
</p>

<!-- Inconsistent scaling ratios -->
<article>
  <h1 class="text-3xl md:text-5xl">Title</h1>      <!-- 1.67x jump -->
  <h2 class="text-xl md:text-2xl">Subtitle</h2>   <!-- 1.2x jump -->
  <p class="text-base md:text-lg">Content</p>      <!-- 1.125x jump -->
</article>
```

## Good Example

```html
<!-- Using clamp() for fluid typography -->
<h1 class="text-[clamp(1.5rem,4vw,3rem)]">
  Smoothly scaling heading
</h1>

<!-- Tailwind v4 fluid type scale -->
<h1 class="text-4xl/fluid">
  Fluid heading
</h1>

<!-- Custom fluid sizes with arbitrary values -->
<p class="text-[clamp(1rem,2.5vw,1.25rem)]">
  Body text that scales smoothly
</p>

<!-- Fluid typography with consistent scale -->
<article>
  <h1 class="text-[clamp(2rem,5vw+1rem,4rem)]">Title</h1>
  <h2 class="text-[clamp(1.5rem,3vw+0.5rem,2.5rem)]">Subtitle</h2>
  <p class="text-[clamp(1rem,1vw+0.75rem,1.25rem)]">Content</p>
</article>

<!-- Using CSS custom properties for maintainability -->
<div class="[--fluid-min:1rem] [--fluid-max:1.5rem]">
  <p class="text-[clamp(var(--fluid-min),3vw,var(--fluid-max))]">
    Configurable fluid text
  </p>
</div>
```

## Why

1. **Smooth user experience**: No jarring size changes when resizing the browser or rotating devices.

2. **Better readability**: Text size adjusts naturally to available space.

3. **Reduced breakpoints**: One fluid declaration replaces multiple breakpoint-specific sizes.

4. **Accessibility**: Users with various screen sizes get appropriately sized text.

5. **Cleaner code**: Less class clutter from multiple responsive variants.

## Clamp() Formula

```
clamp(minimum, preferred, maximum)
```

- **minimum**: Smallest the text should be
- **preferred**: Ideal size (usually viewport-relative)
- **maximum**: Largest the text should be

## Common Fluid Typography Scale

```html
<!-- Headings -->
<h1 class="text-[clamp(2.25rem,6vw,4.5rem)]">Display</h1>
<h2 class="text-[clamp(1.875rem,5vw,3rem)]">H1</h2>
<h3 class="text-[clamp(1.5rem,4vw,2.25rem)]">H2</h3>
<h4 class="text-[clamp(1.25rem,3vw,1.875rem)]">H3</h4>
<h5 class="text-[clamp(1.125rem,2.5vw,1.5rem)]">H4</h5>

<!-- Body -->
<p class="text-[clamp(1rem,1.5vw,1.125rem)]">Body large</p>
<p class="text-[clamp(0.875rem,1.25vw,1rem)]">Body</p>
<p class="text-[clamp(0.75rem,1vw,0.875rem)]">Small</p>
```

## Configuration Extension

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      fontSize: {
        'fluid-sm': 'clamp(0.875rem, 1.5vw, 1rem)',
        'fluid-base': 'clamp(1rem, 2vw, 1.25rem)',
        'fluid-lg': 'clamp(1.25rem, 3vw, 1.875rem)',
        'fluid-xl': 'clamp(1.5rem, 4vw, 2.5rem)',
        'fluid-2xl': 'clamp(2rem, 5vw, 3.5rem)',
        'fluid-3xl': 'clamp(2.5rem, 6vw, 4.5rem)',
      },
    },
  },
}
```
