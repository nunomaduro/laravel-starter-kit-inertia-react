---
id: dark-class-strategy
title: Dark Mode Class Strategy
priority: CRITICAL
category: Dark Mode
---

# Dark Mode Class Strategy

Use the class-based dark mode strategy for manual control over theme switching in your application.

## Bad Example

```html
<!-- Duplicating components for each theme -->
<div class="light-theme-card bg-white text-black">
  Content
</div>
<div class="dark-theme-card bg-gray-900 text-white hidden">
  Content
</div>

<!-- Using JavaScript to swap all classes -->
<script>
  // Anti-pattern: manually toggling every element
  document.querySelectorAll('.card').forEach(card => {
    card.classList.toggle('bg-white');
    card.classList.toggle('bg-gray-900');
  });
</script>

<!-- Inline styles for theming -->
<div style="background: var(--theme-bg)">
  Content
</div>
```

## Good Example

```html
<!-- Configure dark mode in tailwind.config.js -->
<!-- darkMode: 'class' -->

<!-- Add dark class to html or body -->
<html class="dark">
  <body class="bg-white dark:bg-gray-900">

    <!-- Components with dark variants -->
    <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-lg p-6">
      <h2 class="text-gray-900 dark:text-white">Card Title</h2>
      <p class="text-gray-600 dark:text-gray-300">Card description</p>
      <button class="bg-blue-500 dark:bg-blue-600 hover:bg-blue-600 dark:hover:bg-blue-700">
        Action
      </button>
    </div>

  </body>
</html>

<!-- Theme toggle implementation -->
<script>
  function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme',
      document.documentElement.classList.contains('dark') ? 'dark' : 'light'
    );
  }

  // Initialize on page load
  if (localStorage.theme === 'dark' ||
      (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
</script>
```

## Why

1. **User control**: Users can override system preferences and choose their preferred theme.

2. **Persistent preference**: Theme choice can be saved to localStorage and restored on subsequent visits.

3. **Instant switching**: Toggling the `dark` class instantly updates all themed elements.

4. **No flash of wrong theme**: Properly initialized, the correct theme loads before paint.

5. **JavaScript integration**: Easy to connect with React state, Vue refs, or any framework.

## Configuration

```js
// tailwind.config.js
module.exports = {
  darkMode: 'class', // Enable class-based dark mode
  // ...
}
```

## React Implementation

```jsx
// ThemeProvider.jsx
import { createContext, useContext, useEffect, useState } from 'react';

const ThemeContext = createContext();

export function ThemeProvider({ children }) {
  const [theme, setTheme] = useState(() => {
    if (typeof window !== 'undefined') {
      return localStorage.theme ||
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }
    return 'light';
  });

  useEffect(() => {
    const root = document.documentElement;
    if (theme === 'dark') {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
  }, [theme]);

  return (
    <ThemeContext.Provider value={{ theme, setTheme }}>
      {children}
    </ThemeContext.Provider>
  );
}

export const useTheme = () => useContext(ThemeContext);
```

## Preventing Flash of Wrong Theme

```html
<!-- Add this script in <head> before CSS -->
<script>
  if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
</script>
```

## v4: No Config Needed

In Tailwind v4, class-based dark mode is the default. No `tailwind.config.js` required:

```css
@import "tailwindcss";
/* dark: variant works out of the box with .dark class */
```

To use a custom selector (e.g., `data-theme`):

```css
@custom-variant dark (&:where([data-theme="dark"], [data-theme="dark"] *));
```

```html
<html data-theme="dark">
  <!-- Works with data attributes in v4 -->
</html>
```

The toggle logic and React implementation remain the same in both v3 and v4.
