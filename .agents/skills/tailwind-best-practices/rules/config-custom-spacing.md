---
id: config-custom-spacing
title: Custom Spacing Configuration
priority: HIGH
category: Custom Configuration
---

# Custom Spacing Configuration

Extend Tailwind's spacing scale with custom values for consistent layouts.

## Bad Example

```js
// tailwind.config.js
module.exports = {
  theme: {
    // DANGER: Replaces ALL spacing values
    spacing: {
      'small': '8px',
      'medium': '16px',
      'large': '32px',
    },
  },
}
```

```html
<!-- Using arbitrary values instead of config -->
<div class="p-[13px] m-[27px] gap-[19px]">
  Magic numbers everywhere
</div>

<!-- Inconsistent spacing across components -->
<div class="p-4">Card 1</div>
<div class="p-[18px]">Card 2</div>
<div class="p-5">Card 3</div>
```

## Good Example

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      spacing: {
        // Fill gaps in default scale
        '13': '3.25rem',   // 52px
        '15': '3.75rem',   // 60px
        '18': '4.5rem',    // 72px
        '22': '5.5rem',    // 88px

        // Large spacing for sections
        '128': '32rem',    // 512px
        '144': '36rem',    // 576px

        // Semantic spacing
        'header': '4rem',  // 64px - consistent header height
        'sidebar': '16rem', // 256px - sidebar width

        // Fractional spacing
        '4.5': '1.125rem', // 18px
        '5.5': '1.375rem', // 22px
      },
    },
  },
}
```

## Why

1. **Consistency**: Predefined values ensure uniform spacing across the app.

2. **Design system alignment**: Spacing values match design specifications.

3. **Maintainability**: Change spacing in one place, update everywhere.

4. **Prevents arbitrary values**: Team uses config values instead of magic numbers.

5. **Preserves defaults**: Tailwind's 0-96 scale remains available.

## Usage

```html
<!-- Using custom spacing values -->
<header class="h-header px-6">
  Header with consistent height
</header>

<aside class="w-sidebar">
  Sidebar with consistent width
</aside>

<div class="space-y-18">
  <section>Section with custom gap</section>
  <section>Another section</section>
</div>

<div class="p-4.5">
  Card with fractional padding
</div>
```

## Tailwind's Default Spacing Scale

| Class | Value | Pixels |
|-------|-------|--------|
| `0` | 0 | 0px |
| `px` | 1px | 1px |
| `0.5` | 0.125rem | 2px |
| `1` | 0.25rem | 4px |
| `2` | 0.5rem | 8px |
| `3` | 0.75rem | 12px |
| `4` | 1rem | 16px |
| `5` | 1.25rem | 20px |
| `6` | 1.5rem | 24px |
| `8` | 2rem | 32px |
| `10` | 2.5rem | 40px |
| `12` | 3rem | 48px |
| `16` | 4rem | 64px |
| `20` | 5rem | 80px |
| `24` | 6rem | 96px |
| `32` | 8rem | 128px |
| `40` | 10rem | 160px |
| `48` | 12rem | 192px |
| `56` | 14rem | 224px |
| `64` | 16rem | 256px |
| `72` | 18rem | 288px |
| `80` | 20rem | 320px |
| `96` | 24rem | 384px |

## CSS Variables for Dynamic Spacing

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      spacing: {
        'header': 'var(--header-height)',
        'sidebar': 'var(--sidebar-width)',
        'gutter': 'var(--gutter)',
      },
    },
  },
}
```

```css
:root {
  --header-height: 4rem;
  --sidebar-width: 16rem;
  --gutter: 1.5rem;
}

@media (min-width: 1024px) {
  :root {
    --sidebar-width: 20rem;
    --gutter: 2rem;
  }
}
```

## Layout-Specific Spacing

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      spacing: {
        // Page layout
        'page-x': 'clamp(1rem, 5vw, 3rem)',
        'page-y': 'clamp(2rem, 8vh, 6rem)',

        // Section spacing
        'section': 'clamp(4rem, 10vh, 8rem)',

        // Component spacing
        'card-padding': '1.5rem',
        'card-gap': '1rem',
      },
    },
  },
}
```

## Using with Width and Height

Custom spacing works with all spacing-related utilities:

```html
<!-- Padding/Margin -->
<div class="p-18 m-22">Custom spacing</div>

<!-- Width/Height -->
<div class="w-sidebar h-header">Layout element</div>

<!-- Max/Min dimensions -->
<div class="max-w-128 min-h-144">Constrained element</div>

<!-- Gap -->
<div class="grid gap-18">Grid with custom gap</div>

<!-- Inset (positioning) -->
<div class="absolute inset-18">Positioned element</div>

<!-- Space between -->
<div class="space-y-18">Stacked elements</div>
```

## Negative Spacing

Custom spacing values automatically get negative variants:

```html
<div class="-mt-18">Negative margin</div>
<div class="-translate-x-sidebar">Negative transform</div>
```

## Best Practices

1. **Use rem units**: Scales with user font preferences
2. **Follow naming convention**: Numbers for scale, names for semantic values
3. **Document custom values**: Add comments explaining usage
4. **Avoid too many custom values**: Extend only when needed
5. **Consider responsiveness**: Some values may need responsive variants
