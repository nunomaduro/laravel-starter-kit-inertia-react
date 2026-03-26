---
id: dark-setup
title: Dark Mode Setup
priority: CRITICAL
category: Dark Mode
---

# Dark Mode Setup

## Why It Matters

Dark mode is expected in modern applications. Tailwind provides first-class dark mode support with the `dark:` variant. Proper setup ensures consistent dark mode across your application.

## Configuration Options

| Mode | v3 | v4 | Use Case |
|------|----|----|----------|
| Class-based | `darkMode: 'class'` in config | Default, no config needed | Manual toggle, user preference |
| Media (system) | `darkMode: 'media'` in config | `@variant dark (&:where(.dark, .dark *));` override | Simple, automatic |
| Custom selector | `darkMode: ['selector', '...']` | `@custom-variant dark (...)` | Advanced custom logic |

## Configuration

### v3 — `tailwind.config.js`

```js
// tailwind.config.js
module.exports = {
  // Option 1: Class-based (recommended for most apps)
  darkMode: 'class',

  // Option 2: System preference
  darkMode: 'media',

  // Option 3: Custom selector (Tailwind 3.4.1+)
  darkMode: ['selector', '[data-theme="dark"]'],

  theme: {
    extend: {
      colors: {
        background: {
          light: '#ffffff',
          dark: '#0f172a',
        },
      },
    },
  },
}
```

### v4 — CSS only, no config file

```css
@import "tailwindcss";

/* Dark mode is class-based by default in v4 — no config needed */
/* The dark: variant works out of the box */

/* To use a custom selector instead of .dark class: */
@custom-variant dark (&:where([data-theme="dark"], [data-theme="dark"] *));

/* To use media (system preference) strategy: */
/* @custom-variant dark (@media (prefers-color-scheme: dark)); */
```

## Basic Usage

```tsx
// Apply dark mode classes
<div className="bg-white dark:bg-gray-900">
  <h1 className="text-gray-900 dark:text-white">
    Title
  </h1>
  <p className="text-gray-600 dark:text-gray-400">
    Description text
  </p>
  <button className="
    bg-blue-600 hover:bg-blue-700
    dark:bg-blue-500 dark:hover:bg-blue-600
    text-white
  ">
    Button
  </button>
</div>
```

## Dark Mode Toggle (Class Strategy)

```tsx
// hooks/useDarkMode.ts
import { useEffect, useState } from 'react'

export function useDarkMode() {
  const [isDark, setIsDark] = useState(() => {
    if (typeof window === 'undefined') return false

    // Check localStorage first
    const stored = localStorage.getItem('theme')
    if (stored) return stored === 'dark'

    // Fall back to system preference
    return window.matchMedia('(prefers-color-scheme: dark)').matches
  })

  useEffect(() => {
    const root = document.documentElement

    if (isDark) {
      root.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    } else {
      root.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    }
  }, [isDark])

  return { isDark, toggle: () => setIsDark(!isDark) }
}

// Component
function DarkModeToggle() {
  const { isDark, toggle } = useDarkMode()

  return (
    <button
      onClick={toggle}
      className="p-2 rounded-lg bg-gray-100 dark:bg-gray-800"
      aria-label="Toggle dark mode"
    >
      {isDark ? 'Light' : 'Dark'}
    </button>
  )
}
```

## Prevent Flash on Load

```html
<!-- Add to <head> before any stylesheets -->
<script>
  // Prevent flash of wrong theme
  (function() {
    const theme = localStorage.getItem('theme')
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches

    if (theme === 'dark' || (!theme && prefersDark)) {
      document.documentElement.classList.add('dark')
    }
  })()
</script>
```

## System Preference with Override

```tsx
type Theme = 'light' | 'dark' | 'system'

function useTheme() {
  const [theme, setTheme] = useState<Theme>(() => {
    return (localStorage.getItem('theme') as Theme) || 'system'
  })

  useEffect(() => {
    const root = document.documentElement
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)')

    const applyTheme = () => {
      const isDark =
        theme === 'dark' || (theme === 'system' && systemDark.matches)

      root.classList.toggle('dark', isDark)
    }

    applyTheme()

    // Listen for system changes when using 'system'
    if (theme === 'system') {
      systemDark.addEventListener('change', applyTheme)
      return () => systemDark.removeEventListener('change', applyTheme)
    }
  }, [theme])

  const setAndStore = (newTheme: Theme) => {
    setTheme(newTheme)
    localStorage.setItem('theme', newTheme)
  }

  return { theme, setTheme: setAndStore }
}
```

## Theme Selector UI

```tsx
function ThemeSelector() {
  const { theme, setTheme } = useTheme()

  return (
    <select
      value={theme}
      onChange={(e) => setTheme(e.target.value as Theme)}
      className="rounded border p-2 bg-white dark:bg-gray-800"
    >
      <option value="light">Light</option>
      <option value="dark">Dark</option>
      <option value="system">System</option>
    </select>
  )
}
```

## Common Dark Mode Patterns

```tsx
// Borders
<div className="border border-gray-200 dark:border-gray-700">

// Shadows (less prominent in dark mode)
<div className="shadow-md dark:shadow-gray-900/50">

// Hover states
<button className="
  hover:bg-gray-100 dark:hover:bg-gray-800
">

// Focus rings
<input className="
  focus:ring-blue-500 dark:focus:ring-blue-400
  focus:ring-offset-2 dark:focus:ring-offset-gray-900
">
```

## v4: Using @variant in Custom CSS

In v4, nest dark mode inside custom CSS with `@variant`:

```css
.card {
  background: white;
  color: #1e293b;

  @variant dark {
    background: #1e293b;
    color: #f1f5f9;
  }
}
```

## Impact

- Better user experience
- Reduces eye strain in dark environments
- Professional, modern look
- Accessibility improvement
