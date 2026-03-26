---
id: config-plugins
title: Tailwind Plugins Configuration
priority: HIGH
category: Custom Configuration
---

# Tailwind Plugins Configuration

Use and create plugins to extend Tailwind with custom utilities, components, and variants.

## Bad Example

```js
// tailwind.config.js
module.exports = {
  // Not using any plugins, missing useful functionality
  plugins: [],
}
```

```css
/* Manually writing repetitive CSS instead of using plugins */
.text-shadow-sm {
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.text-shadow-md {
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.text-shadow-lg {
  text-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
/* Repeated for each variant... */
```

## Good Example

```js
// tailwind.config.js
const plugin = require('tailwindcss/plugin')

module.exports = {
  plugins: [
    // Official Tailwind plugins
    require('@tailwindcss/typography'),
    require('@tailwindcss/forms'),
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/container-queries'),

    // Custom plugin for text shadows
    plugin(function({ addUtilities, theme, e }) {
      const textShadows = {
        '.text-shadow-sm': {
          textShadow: '0 1px 2px rgba(0, 0, 0, 0.1)',
        },
        '.text-shadow': {
          textShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
        },
        '.text-shadow-md': {
          textShadow: '0 4px 8px rgba(0, 0, 0, 0.1)',
        },
        '.text-shadow-lg': {
          textShadow: '0 8px 16px rgba(0, 0, 0, 0.1)',
        },
        '.text-shadow-none': {
          textShadow: 'none',
        },
      }
      addUtilities(textShadows)
    }),

    // Custom plugin for animations
    plugin(function({ addUtilities }) {
      addUtilities({
        '.animate-fade-in': {
          animation: 'fadeIn 0.5s ease-out forwards',
        },
        '.animate-slide-up': {
          animation: 'slideUp 0.3s ease-out forwards',
        },
        '@keyframes fadeIn': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        '@keyframes slideUp': {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      })
    }),
  ],
}
```

## Why

1. **Code reuse**: Plugins encapsulate common patterns.

2. **Consistency**: Shared plugins ensure uniform styles across projects.

3. **Official support**: Tailwind plugins are well-maintained and documented.

4. **Custom extensions**: Create project-specific utilities without leaving Tailwind.

5. **Composability**: Plugins work with Tailwind's modifiers (hover:, dark:, etc.).

## Official Tailwind Plugins

### Typography Plugin

```bash
npm install @tailwindcss/typography
```

```html
<article class="prose dark:prose-invert lg:prose-xl">
  <h1>Article Title</h1>
  <p>Beautiful typography defaults for long-form content...</p>
</article>
```

### Forms Plugin

```bash
npm install @tailwindcss/forms
```

```html
<input type="text" class="form-input rounded-lg">
<select class="form-select rounded-lg">
  <option>Option 1</option>
</select>
<textarea class="form-textarea rounded-lg"></textarea>
```

### Aspect Ratio Plugin

```bash
npm install @tailwindcss/aspect-ratio
```

```html
<div class="aspect-w-16 aspect-h-9">
  <iframe src="..." class="w-full h-full"></iframe>
</div>
```

### Container Queries Plugin

```bash
npm install @tailwindcss/container-queries
```

```html
<div class="@container">
  <div class="@lg:flex @lg:gap-8">
    Container-based responsive layout
  </div>
</div>
```

## Creating Custom Plugins

### Adding Utilities

```js
plugin(function({ addUtilities, theme }) {
  addUtilities({
    '.scrollbar-hide': {
      '-ms-overflow-style': 'none',
      'scrollbar-width': 'none',
      '&::-webkit-scrollbar': {
        display: 'none',
      },
    },
    '.scrollbar-thin': {
      'scrollbar-width': 'thin',
    },
  })
})
```

### Adding Components

```js
plugin(function({ addComponents, theme }) {
  addComponents({
    '.btn': {
      padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
      borderRadius: theme('borderRadius.lg'),
      fontWeight: theme('fontWeight.medium'),
      transition: 'all 150ms ease',
    },
    '.btn-primary': {
      backgroundColor: theme('colors.blue.600'),
      color: theme('colors.white'),
      '&:hover': {
        backgroundColor: theme('colors.blue.700'),
      },
    },
  })
})
```

### Adding Base Styles

```js
plugin(function({ addBase, theme }) {
  addBase({
    'h1': {
      fontSize: theme('fontSize.3xl'),
      fontWeight: theme('fontWeight.bold'),
    },
    'h2': {
      fontSize: theme('fontSize.2xl'),
      fontWeight: theme('fontWeight.semibold'),
    },
  })
})
```

### Adding Variants

```js
plugin(function({ addVariant }) {
  // Peer checked variant
  addVariant('peer-checked', ':merge(.peer):checked ~ &')

  // Group hover variant
  addVariant('group-hover', ':merge(.group):hover &')

  // Supports variant
  addVariant('supports-backdrop', '@supports (backdrop-filter: blur(0))')

  // Custom selector variant
  addVariant('hocus', ['&:hover', '&:focus'])
})
```

## Plugin with Options

```js
// plugins/buttons.js
const plugin = require('tailwindcss/plugin')

module.exports = plugin.withOptions(
  function(options = {}) {
    return function({ addComponents, theme }) {
      const baseRadius = options.radius || theme('borderRadius.lg')

      addComponents({
        '.btn': {
          borderRadius: baseRadius,
          padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
        },
      })
    }
  },
  function(options = {}) {
    return {
      theme: {
        extend: {
          // Plugin can extend theme
        },
      },
    }
  }
)
```

```js
// tailwind.config.js
module.exports = {
  plugins: [
    require('./plugins/buttons')({ radius: '0.5rem' }),
  ],
}
```

## v4: Plugins Move to CSS

In Tailwind v4, plugins use `@plugin` in CSS instead of `require()` in config:

```css
@import "tailwindcss";
@plugin "@tailwindcss/typography";
@plugin "@tailwindcss/forms";
```

Custom utilities and variants use CSS directives instead of the JS plugin API:

```css
/* Custom utility — replaces addUtilities() */
@utility scrollbar-hide {
  scrollbar-width: none;
}

/* Custom variant — replaces addVariant() */
@custom-variant hocus (&:hover, &:focus);
```

**Plugins no longer needed in v4** (features built into core):
- `@tailwindcss/aspect-ratio` — native `aspect-*` utilities
- `@tailwindcss/container-queries` — native `@container` with `@min-*`/`@max-*` range variants

## Popular Community Plugins

```js
// tailwind.config.js
module.exports = {
  plugins: [
    // Animations
    require('tailwindcss-animate'),

    // Scrollbar styling
    require('tailwind-scrollbar'),

    // Debug screens (shows current breakpoint)
    require('tailwindcss-debug-screens'),

    // Multi-theme support
    require('tailwindcss-themer'),

    // Fluid type
    require('tailwindcss-fluid-type'),
  ],
}
```

## Usage Example

```html
<!-- Using typography plugin -->
<article class="prose prose-lg dark:prose-invert">
  <h1>Welcome</h1>
  <p>This content is beautifully styled.</p>
</article>

<!-- Using custom text-shadow utility -->
<h1 class="text-4xl font-bold text-shadow-lg">
  Shadowed Heading
</h1>

<!-- Using custom animation -->
<div class="animate-fade-in">
  Fading in content
</div>
```
