---
id: config-custom-colors
title: Custom Colors Configuration
priority: HIGH
category: Custom Configuration
---

# Custom Colors Configuration

Define a consistent color system with proper scales and semantic naming.

## Bad Example

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Single color without scale
        brand: '#3b82f6',

        // Arbitrary names
        myBlue: '#2563eb',
        lightBlue: '#dbeafe',
        darkBlue: '#1e40af',

        // Inconsistent naming
        'btn-primary': '#3b82f6',
        'btn-secondary': '#64748b',
        headerBg: '#f8fafc',
      },
    },
  },
}
```

## Good Example

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Full color scale for brand colors
        brand: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },

        // Accent color
        accent: {
          50: '#fdf4ff',
          100: '#fae8ff',
          200: '#f5d0fe',
          300: '#f0abfc',
          400: '#e879f9',
          500: '#d946ef',
          600: '#c026d3',
          700: '#a21caf',
          800: '#86198f',
          900: '#701a75',
          950: '#4a044e',
        },

        // Semantic colors for specific purposes
        success: {
          light: '#dcfce7',
          DEFAULT: '#22c55e',
          dark: '#15803d',
        },

        warning: {
          light: '#fef3c7',
          DEFAULT: '#f59e0b',
          dark: '#b45309',
        },

        error: {
          light: '#fee2e2',
          DEFAULT: '#ef4444',
          dark: '#b91c1c',
        },
      },
    },
  },
}
```

## Why

1. **Consistent scale**: 50-950 scale matches Tailwind's built-in colors.

2. **Design flexibility**: Multiple shades enable hover states, backgrounds, and text.

3. **Semantic naming**: Colors describe purpose, improving code readability.

4. **Dark mode ready**: Full scales make it easy to pick contrasting shades.

5. **Team alignment**: Clear naming conventions prevent color proliferation.

## Usage

```html
<!-- Using brand color scale -->
<button class="bg-brand-600 hover:bg-brand-700 text-white">
  Primary Action
</button>

<div class="bg-brand-50 border border-brand-200 text-brand-800">
  Brand-themed alert
</div>

<!-- Using semantic colors -->
<div class="bg-success-light text-success-dark">
  Success message
</div>

<span class="text-error">Error text</span>
```

## CSS Variables Approach

For dynamic theming:

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--color-background) / <alpha-value>)',
        foreground: 'hsl(var(--color-foreground) / <alpha-value>)',

        primary: {
          DEFAULT: 'hsl(var(--color-primary) / <alpha-value>)',
          foreground: 'hsl(var(--color-primary-foreground) / <alpha-value>)',
        },

        secondary: {
          DEFAULT: 'hsl(var(--color-secondary) / <alpha-value>)',
          foreground: 'hsl(var(--color-secondary-foreground) / <alpha-value>)',
        },

        muted: {
          DEFAULT: 'hsl(var(--color-muted) / <alpha-value>)',
          foreground: 'hsl(var(--color-muted-foreground) / <alpha-value>)',
        },

        card: {
          DEFAULT: 'hsl(var(--color-card) / <alpha-value>)',
          foreground: 'hsl(var(--color-card-foreground) / <alpha-value>)',
        },

        border: 'hsl(var(--color-border) / <alpha-value>)',
        ring: 'hsl(var(--color-ring) / <alpha-value>)',
      },
    },
  },
}
```

```css
/* globals.css */
@layer base {
  :root {
    --color-background: 0 0% 100%;
    --color-foreground: 222 47% 11%;
    --color-primary: 221 83% 53%;
    --color-primary-foreground: 210 40% 98%;
    --color-secondary: 210 40% 96%;
    --color-secondary-foreground: 222 47% 11%;
    --color-muted: 210 40% 96%;
    --color-muted-foreground: 215 16% 47%;
    --color-card: 0 0% 100%;
    --color-card-foreground: 222 47% 11%;
    --color-border: 214 32% 91%;
    --color-ring: 221 83% 53%;
  }

  .dark {
    --color-background: 222 47% 4%;
    --color-foreground: 210 40% 98%;
    --color-primary: 217 91% 60%;
    --color-primary-foreground: 222 47% 11%;
    --color-secondary: 217 33% 17%;
    --color-secondary-foreground: 210 40% 98%;
    --color-muted: 217 33% 17%;
    --color-muted-foreground: 215 20% 65%;
    --color-card: 222 47% 7%;
    --color-card-foreground: 210 40% 98%;
    --color-border: 217 33% 17%;
    --color-ring: 224 76% 48%;
  }
}
```

## Generating Color Scales

Use tools to generate consistent scales:

1. **UI Colors** - https://uicolors.app
2. **Tailwind Ink** - https://tailwind.ink
3. **Palette** - https://palette.app

Or use the `tailwindcss-palette-generator` package:

```bash
npm install tailwindcss-palette-generator
```

```js
const { generatePalette } = require('tailwindcss-palette-generator');

module.exports = {
  theme: {
    extend: {
      colors: {
        brand: generatePalette('#3b82f6'),
      },
    },
  },
}
```

## Color Naming Conventions

| Type | Naming | Example |
|------|--------|---------|
| Brand colors | `brand`, `accent` | `brand-500` |
| Semantic | `success`, `warning`, `error`, `info` | `error-light` |
| UI elements | `background`, `foreground`, `border` | `background` |
| Component | `card`, `popover`, `input` | `card-foreground` |

## Opacity Modifiers

```html
<!-- Using opacity with custom colors -->
<div class="bg-brand-500/50">50% opacity background</div>
<div class="text-brand-600/75">75% opacity text</div>
<div class="border-brand-200/30">30% opacity border</div>
```
