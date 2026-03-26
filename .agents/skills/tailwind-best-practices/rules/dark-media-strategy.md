---
id: dark-media-strategy
title: Dark Mode Media Strategy
priority: HIGH
category: Dark Mode
---

# Dark Mode Media Strategy

Use the media-based dark mode strategy to automatically follow the user's system preferences.

## Bad Example

```html
<!-- Manually checking prefers-color-scheme in JavaScript -->
<script>
  // Anti-pattern: reimplementing what Tailwind does automatically
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.querySelectorAll('.card').forEach(card => {
      card.classList.add('dark-card');
    });
  }
</script>

<!-- Using separate stylesheets -->
<link rel="stylesheet" href="light.css" media="(prefers-color-scheme: light)">
<link rel="stylesheet" href="dark.css" media="(prefers-color-scheme: dark)">

<!-- Not using dark: variants at all -->
<div class="bg-white text-black">
  No dark mode support
</div>
```

## Good Example

```html
<!-- Configure dark mode in tailwind.config.js -->
<!-- darkMode: 'media' (this is the default) -->

<!-- Components automatically respond to system preference -->
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
    Welcome
  </h1>
  <p class="text-gray-600 dark:text-gray-400">
    This content automatically adapts to your system theme preference.
  </p>
</div>

<!-- Full page with media-based dark mode -->
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">
  <header class="bg-white dark:bg-gray-900 shadow dark:shadow-gray-800">
    <nav class="container mx-auto px-4 py-4">
      <a href="/" class="text-gray-900 dark:text-white font-bold">Logo</a>
    </nav>
  </header>

  <main class="container mx-auto px-4 py-8">
    <article class="prose dark:prose-invert">
      <!-- Content styled with prose -->
    </article>
  </main>
</body>
```

## Why

1. **Zero JavaScript required**: Theme switching happens automatically via CSS media queries.

2. **Respects user preferences**: Honors the user's operating system theme setting.

3. **Automatic updates**: Theme changes instantly when user toggles system preference.

4. **Simpler implementation**: No theme state management or localStorage needed.

5. **Default behavior**: This is Tailwind's default dark mode strategy.

## Configuration

### v3

```js
// tailwind.config.js
module.exports = {
  darkMode: 'media', // Default - can be omitted
  // ...
}
```

### v4

In v4, dark mode defaults to class-based. To use media (system preference) strategy:

```css
@import "tailwindcss";

/* Override the default dark variant to use system preference */
@custom-variant dark (@media (prefers-color-scheme: dark));
```

## Generated CSS

When you use `dark:bg-gray-900`, Tailwind generates:

```css
@media (prefers-color-scheme: dark) {
  .dark\:bg-gray-900 {
    background-color: rgb(17 24 39);
  }
}
```

## When to Use Media Strategy

- **Content sites**: Blogs, documentation, marketing pages
- **Simple applications**: Where manual theme toggle isn't needed
- **Progressive enhancement**: Starting point before adding manual toggle
- **Accessibility focus**: Automatically respecting user preferences

## When to Use Class Strategy Instead

- **User preference override**: When users should choose regardless of system setting
- **Multiple themes**: Light, dark, and other color schemes
- **Theme persistence**: Saving preference across sessions
- **Admin/dashboard apps**: Where users expect control

## Combining Both Approaches

You can start with media strategy and add optional override:

```js
// tailwind.config.js
module.exports = {
  darkMode: 'class', // Use class for control
}
```

```javascript
// Initialize with system preference, allow override
function initTheme() {
  const stored = localStorage.getItem('theme');

  if (stored === 'dark') {
    document.documentElement.classList.add('dark');
  } else if (stored === 'light') {
    document.documentElement.classList.remove('dark');
  } else {
    // No stored preference, follow system
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark');
    }
  }
}
```

## Testing Dark Mode

```css
/* Force dark mode in browser DevTools */
@media (prefers-color-scheme: dark) {
  /* Styles here */
}
```

Or use browser DevTools:
1. Open DevTools
2. Press Cmd/Ctrl + Shift + P
3. Search "Emulate CSS prefers-color-scheme"
4. Select "dark" or "light"
