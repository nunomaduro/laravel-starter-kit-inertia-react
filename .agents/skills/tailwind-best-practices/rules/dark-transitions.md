---
id: dark-transitions
title: Dark Mode Transitions
priority: MEDIUM
category: Dark Mode
---

# Dark Mode Transitions

Add smooth transitions when switching between light and dark modes for a polished user experience.

## Bad Example

```html
<!-- No transition - jarring instant change -->
<div class="bg-white dark:bg-gray-900 text-black dark:text-white">
  Content flashes when theme changes
</div>

<!-- Transitioning everything (performance issue) -->
<div class="transition-all duration-500 bg-white dark:bg-gray-900">
  Transitioning all properties is expensive
</div>

<!-- Inconsistent transition timing -->
<div class="transition duration-100 bg-white dark:bg-gray-900">
  <p class="transition duration-500 text-black dark:text-white">
    Different speeds feel disjointed
  </p>
</div>
```

## Good Example

```html
<!-- Targeted transitions for theme changes -->
<div class="transition-colors duration-200 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  Smooth color transition
</div>

<!-- Global theme transition on body -->
<body class="transition-colors duration-300 bg-white dark:bg-gray-950">
  <!-- All children inherit the smooth background change -->
</body>

<!-- Component with multiple color transitions -->
<div class="
  transition-colors duration-200
  bg-white dark:bg-gray-800
  border border-gray-200 dark:border-gray-700
  shadow-sm dark:shadow-gray-900/20
">
  <h2 class="transition-colors duration-200 text-gray-900 dark:text-white">
    Title
  </h2>
  <p class="transition-colors duration-200 text-gray-600 dark:text-gray-400">
    Description
  </p>
</div>

<!-- Using CSS custom property for consistent timing -->
<style>
  :root {
    --theme-transition: 200ms;
  }
</style>
<div class="transition-colors duration-[--theme-transition] bg-white dark:bg-gray-900">
  Consistent timing via CSS variable
</div>
```

## Why

1. **Polished UX**: Smooth transitions feel more professional and intentional.

2. **Reduced visual jarring**: Users don't experience harsh color flashing.

3. **Consistent behavior**: All elements change at the same rate.

4. **Performance optimized**: Transitioning only colors is GPU-efficient.

5. **Accessibility**: Gradual changes are easier on the eyes.

## Best Practices

### 1. Use `transition-colors` Not `transition-all`

```html
<!-- Good: specific property -->
<div class="transition-colors duration-200">...</div>

<!-- Avoid: all properties -->
<div class="transition-all duration-200">...</div>
```

### 2. Consistent Duration

```html
<!-- Recommended: 150-300ms for theme transitions -->
<div class="transition-colors duration-200">...</div>
```

### 3. Apply at the Component Level

```html
<!-- Apply transition to the component root -->
<article class="transition-colors duration-200 bg-white dark:bg-gray-800 rounded-lg p-6">
  <h3 class="text-gray-900 dark:text-white">Title</h3>
  <p class="text-gray-600 dark:text-gray-300">Content</p>
</article>
```

## Global Theme Transition

```css
/* globals.css */
@layer base {
  * {
    @apply transition-colors duration-200;
  }
}
```

Or more selectively:

```css
@layer base {
  body,
  header,
  main,
  footer,
  nav,
  article,
  section,
  aside,
  .card,
  .btn {
    @apply transition-colors duration-200;
  }
}
```

## Disabling Transitions During Load

Prevent flash of animated content on page load:

```html
<script>
  // Add no-transition class before paint
  document.documentElement.classList.add('no-transition');

  // Remove after a tick
  window.addEventListener('load', () => {
    requestAnimationFrame(() => {
      document.documentElement.classList.remove('no-transition');
    });
  });
</script>
```

```css
.no-transition,
.no-transition * {
  transition: none !important;
}
```

## React Implementation

```jsx
// useThemeTransition.js
import { useEffect, useLayoutEffect } from 'react';

export function useThemeTransition() {
  useLayoutEffect(() => {
    // Disable transitions on initial load
    document.documentElement.classList.add('no-transition');

    // Re-enable after paint
    const timer = requestAnimationFrame(() => {
      document.documentElement.classList.remove('no-transition');
    });

    return () => cancelAnimationFrame(timer);
  }, []);
}
```

## Excluding Elements from Transition

```html
<!-- Some elements shouldn't transition (e.g., images) -->
<img class="transition-none" src="photo.jpg" alt="">

<!-- Code blocks shouldn't animate -->
<pre class="transition-none">
  <code>...</code>
</pre>
```

## Accessibility Considerations

Respect reduced motion preferences:

```css
@media (prefers-reduced-motion: reduce) {
  * {
    transition-duration: 0.01ms !important;
  }
}
```

Or in Tailwind:

```html
<div class="transition-colors duration-200 motion-reduce:transition-none">
  Respects reduced motion preference
</div>
```
