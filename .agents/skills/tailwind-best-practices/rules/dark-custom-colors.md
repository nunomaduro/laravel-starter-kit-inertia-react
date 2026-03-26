---
id: dark-custom-colors
title: Dark Mode Custom Colors
priority: HIGH
category: Dark Mode
---

# Dark Mode Custom Colors

Create custom color palettes that work harmoniously in both light and dark modes.

## Bad Example

```html
<!-- Using colors that clash in dark mode -->
<button class="bg-yellow-400 dark:bg-yellow-400 text-black dark:text-black">
  Yellow button (poor contrast in dark mode)
</button>

<!-- Inverted colors that look wrong -->
<div class="bg-blue-500 dark:bg-blue-500">
  <p class="text-white dark:text-white">Same colors don't adapt</p>
</div>

<!-- Random dark mode color choices -->
<div class="bg-indigo-600 dark:bg-pink-400">
  Unrelated color pairing
</div>
```

## Good Example

```html
<!-- Brand colors with dark mode variants -->
<button class="bg-brand-500 dark:bg-brand-400 text-white dark:text-gray-900">
  Brand button
</button>

<!-- Semantic color usage -->
<div class="bg-success-50 dark:bg-success-950 border border-success-200 dark:border-success-800">
  <p class="text-success-700 dark:text-success-300">Success message</p>
</div>

<!-- Accent colors that adapt -->
<a href="#" class="text-accent-600 dark:text-accent-400 hover:text-accent-700 dark:hover:text-accent-300">
  Accent link
</a>

<!-- Status colors with proper contrast -->
<span class="bg-error-100 dark:bg-error-900/50 text-error-700 dark:text-error-300 px-2 py-1 rounded">
  Error
</span>
<span class="bg-warning-100 dark:bg-warning-900/50 text-warning-700 dark:text-warning-300 px-2 py-1 rounded">
  Warning
</span>
<span class="bg-info-100 dark:bg-info-900/50 text-info-700 dark:text-info-300 px-2 py-1 rounded">
  Info
</span>
```

## Why

1. **Brand consistency**: Maintain brand identity while ensuring readability in dark mode.

2. **Accessibility**: Custom colors are designed with contrast ratios in mind.

3. **Visual harmony**: Light and dark variants feel intentional, not random.

4. **Semantic meaning**: Color names describe usage, making code readable.

5. **Scalability**: Easy to add new status colors or brand variations.

## Configuration

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Brand color with full scale
        brand: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
          950: '#082f49',
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

        // Semantic status colors
        success: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
          950: '#052e16',
        },

        warning: {
          50: '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#fbbf24',
          500: '#f59e0b',
          600: '#d97706',
          700: '#b45309',
          800: '#92400e',
          900: '#78350f',
          950: '#451a03',
        },

        error: {
          50: '#fef2f2',
          100: '#fee2e2',
          200: '#fecaca',
          300: '#fca5a5',
          400: '#f87171',
          500: '#ef4444',
          600: '#dc2626',
          700: '#b91c1c',
          800: '#991b1b',
          900: '#7f1d1d',
          950: '#450a0a',
        },

        info: {
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
      },
    },
  },
}
```

## Color Pairing Guidelines

### Light Mode
- Background: 50-100 shades
- Text: 700-900 shades
- Borders: 200-300 shades
- Primary actions: 500-600 shades

### Dark Mode
- Background: 900-950 shades (or with opacity)
- Text: 200-400 shades
- Borders: 700-800 shades
- Primary actions: 400-500 shades

## Component Example

```html
<!-- Alert component with custom colors -->
<div class="rounded-lg p-4 bg-info-50 dark:bg-info-950 border border-info-200 dark:border-info-800">
  <div class="flex items-center gap-3">
    <svg class="w-5 h-5 text-info-500 dark:text-info-400" fill="currentColor">...</svg>
    <div>
      <h4 class="font-medium text-info-800 dark:text-info-200">Information</h4>
      <p class="text-info-700 dark:text-info-300 text-sm">This is an informational message.</p>
    </div>
  </div>
</div>
```

## v4: Custom Colors with @theme

In v4, define color scales directly in CSS:

```css
@import "tailwindcss";

@theme {
  --color-brand-50: #eff6ff;
  --color-brand-500: #3b82f6;
  --color-brand-950: #172554;

  --color-success-500: #22c55e;
  --color-error-500: #ef4444;
}
```

The same pairing guidelines apply — use lighter shades (50-100) for dark backgrounds and darker shades (700-900) for dark text, with `dark:` variants.

## Generating Color Palettes

Use tools like:
- [UI Colors](https://uicolors.app)
- [Tailwind CSS Color Generator](https://tailwindcolors.com)
- [Palette Generator](https://palette.app)
