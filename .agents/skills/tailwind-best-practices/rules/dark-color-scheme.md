---
id: dark-color-scheme
title: Dark Mode Color Scheme
priority: HIGH
category: Dark Mode
---

# Dark Mode Color Scheme

Use semantic color naming and CSS custom properties for maintainable dark mode color systems.

## Bad Example

```html
<!-- Hardcoded colors without semantic meaning -->
<div class="bg-gray-900 dark:bg-white text-white dark:text-gray-900">
  Inverted but confusing
</div>

<!-- Inconsistent color pairs across components -->
<div class="bg-slate-800 dark:bg-slate-100">Header</div>
<div class="bg-gray-900 dark:bg-white">Content</div>
<div class="bg-zinc-800 dark:bg-zinc-50">Footer</div>

<!-- Magic numbers without context -->
<p class="text-gray-700 dark:text-gray-300">
  Why these specific shades?
</p>
```

## Good Example

```html
<!-- Semantic color tokens via CSS variables -->
<div class="bg-[--color-surface] text-[--color-text]">
  Consistent theming
</div>

<!-- Or use Tailwind's built-in approach with custom colors -->
<div class="bg-surface text-foreground">
  <h1 class="text-foreground">Title</h1>
  <p class="text-muted">Description</p>
  <button class="bg-primary text-primary-foreground">Action</button>
</div>

<!-- Consistent gray scale strategy -->
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-50">
  <header class="bg-gray-50 dark:bg-gray-900">
    <nav class="border-b border-gray-200 dark:border-gray-800">
      Navigation
    </nav>
  </header>
  <main class="bg-white dark:bg-gray-950">
    <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4">
      Card content
    </div>
  </main>
</body>
```

## Why

1. **Semantic meaning**: Color names describe purpose, not appearance.

2. **Consistency**: All surfaces use the same color tokens.

3. **Easy theme changes**: Update colors in one place, apply everywhere.

4. **Accessibility**: Ensures proper contrast ratios are maintained.

5. **Design system alignment**: Matches common design system conventions.

## Color Pairing Strategy

| Light Mode | Dark Mode | Use Case |
|------------|-----------|----------|
| `white` | `gray-950` | Page background |
| `gray-50` | `gray-900` | Elevated surfaces |
| `gray-100` | `gray-800` | Cards, inputs |
| `gray-200` | `gray-700` | Borders, dividers |
| `gray-500` | `gray-400` | Placeholder text |
| `gray-600` | `gray-300` | Secondary text |
| `gray-900` | `gray-50` | Primary text |

## Configuration with Custom Colors

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Semantic tokens
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',

        surface: {
          DEFAULT: 'hsl(var(--surface))',
          elevated: 'hsl(var(--surface-elevated))',
        },

        muted: {
          DEFAULT: 'hsl(var(--muted))',
          foreground: 'hsl(var(--muted-foreground))',
        },

        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },

        border: 'hsl(var(--border))',
      },
    },
  },
}
```

## CSS Variables Setup

### v3

```css
/* globals.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222 47% 11%;
    --surface: 210 40% 98%;
    --surface-elevated: 0 0% 100%;
    --muted: 210 40% 96%;
    --muted-foreground: 215 16% 47%;
    --primary: 221 83% 53%;
    --primary-foreground: 210 40% 98%;
    --border: 214 32% 91%;
  }

  .dark {
    --background: 222 47% 4%;
    --foreground: 210 40% 98%;
    --surface: 222 47% 7%;
    --surface-elevated: 222 47% 11%;
    --muted: 217 33% 17%;
    --muted-foreground: 215 20% 65%;
    --primary: 217 91% 60%;
    --primary-foreground: 222 47% 11%;
    --border: 217 33% 17%;
  }
}
```

### v4

```css
@import "tailwindcss";

/* Define semantic colors as theme tokens */
@theme {
  --color-background: hsl(0 0% 100%);
  --color-foreground: hsl(222 47% 11%);
  --color-surface: hsl(210 40% 98%);
  --color-muted: hsl(210 40% 96%);
  --color-primary: hsl(221 83% 53%);
  --color-border: hsl(214 32% 91%);
}

/* Dark mode overrides with CSS custom properties */
.dark {
  --color-background: hsl(222 47% 4%);
  --color-foreground: hsl(210 40% 98%);
  --color-surface: hsl(222 47% 7%);
  --color-muted: hsl(217 33% 17%);
  --color-primary: hsl(217 91% 60%);
  --color-border: hsl(217 33% 17%);
}
```

The v4 approach uses `@theme` to register CSS variables as utility classes, so `bg-background`, `text-foreground`, etc. work automatically without configuring `tailwind.config.js`.

## Usage

```html
<div class="bg-background text-foreground min-h-screen">
  <header class="bg-surface border-b border-border">
    <h1 class="text-foreground">App Name</h1>
  </header>

  <main class="p-6">
    <div class="bg-surface-elevated rounded-lg p-4 border border-border">
      <h2 class="text-foreground">Card Title</h2>
      <p class="text-muted-foreground">Secondary content</p>
      <button class="bg-primary text-primary-foreground px-4 py-2 rounded">
        Primary Action
      </button>
    </div>
  </main>
</div>
```
