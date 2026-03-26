---
id: responsive-aspect-ratio
title: Responsive Aspect Ratios
priority: CRITICAL
category: Responsive Design
---

# Responsive Aspect Ratios

Use Tailwind's aspect ratio utilities to maintain consistent proportions for images, videos, and containers across different screen sizes.

## Bad Example

```html
<!-- Hardcoded padding hack (outdated approach) -->
<div class="relative h-0 pb-[56.25%]">
  <img class="absolute inset-0 w-full h-full object-cover" src="image.jpg">
</div>

<!-- Fixed dimensions that break responsiveness -->
<video class="w-640 h-360" src="video.mp4"></video>

<!-- Inconsistent aspect ratios at different breakpoints -->
<div class="h-48 md:h-64 lg:h-96 w-full">
  <img class="w-full h-full object-cover" src="image.jpg">
</div>
```

## Good Example

```html
<!-- Native aspect ratio utility -->
<div class="aspect-video">
  <img class="w-full h-full object-cover" src="image.jpg">
</div>

<!-- Responsive aspect ratios -->
<div class="aspect-square md:aspect-video lg:aspect-[21/9]">
  <video class="w-full h-full object-cover" src="video.mp4"></video>
</div>

<!-- Image with automatic aspect ratio -->
<img class="aspect-[4/3] w-full object-cover" src="image.jpg" alt="">

<!-- Card with consistent thumbnail ratio -->
<article class="overflow-hidden rounded-lg">
  <div class="aspect-[3/2]">
    <img class="w-full h-full object-cover" src="thumbnail.jpg" alt="">
  </div>
  <div class="p-4">
    <h3>Card Title</h3>
  </div>
</article>

<!-- Iframe embed with aspect ratio -->
<div class="aspect-video w-full">
  <iframe class="w-full h-full" src="https://youtube.com/embed/..." allowfullscreen></iframe>
</div>
```

## Why

1. **Prevents layout shift**: Content reserves space before loading, improving CLS (Cumulative Layout Shift).

2. **Consistent design**: Maintains proportions across different content and screen sizes.

3. **Simpler markup**: Native `aspect-ratio` CSS property is cleaner than padding hacks.

4. **Responsive flexibility**: Easily change ratios at different breakpoints.

5. **Better performance**: Browser can allocate space before images load.

## Built-in Aspect Ratios

| Class | Ratio | Use Case |
|-------|-------|----------|
| `aspect-auto` | auto | Native image/video ratio |
| `aspect-square` | 1/1 | Profile pictures, icons |
| `aspect-video` | 16/9 | Videos, presentations |

## Custom Aspect Ratios

```html
<!-- Common aspect ratios -->
<div class="aspect-[4/3]">Standard photo</div>
<div class="aspect-[3/2]">Classic photo</div>
<div class="aspect-[21/9]">Ultrawide/cinema</div>
<div class="aspect-[9/16]">Mobile/portrait video</div>
<div class="aspect-[1/1.414]">A4 paper ratio</div>

<!-- Decimal ratios -->
<div class="aspect-[1.618/1]">Golden ratio</div>
```

## Configuration

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      aspectRatio: {
        '4/3': '4 / 3',
        '3/2': '3 / 2',
        '21/9': '21 / 9',
        'golden': '1.618 / 1',
      },
    },
  },
}
```

## Responsive Pattern

```html
<!-- Portrait on mobile, landscape on desktop -->
<div class="aspect-[9/16] sm:aspect-square md:aspect-video lg:aspect-[21/9]">
  <img class="w-full h-full object-cover" src="hero.jpg" alt="">
</div>
```
