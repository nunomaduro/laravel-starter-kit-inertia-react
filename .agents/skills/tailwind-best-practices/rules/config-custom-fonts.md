---
id: config-custom-fonts
title: Custom Fonts Configuration
priority: HIGH
category: Custom Configuration
---

# Custom Fonts Configuration

Configure custom font families with proper fallbacks and font feature settings.

## Bad Example

```js
// tailwind.config.js
module.exports = {
  theme: {
    // DANGER: Replaces ALL font families
    fontFamily: {
      'custom': ['CustomFont'],
    },
  },
}
```

```html
<!-- No fallback fonts -->
<p class="font-['CustomFont']">
  No fallbacks if custom font fails to load
</p>

<!-- Inconsistent font usage -->
<h1 style="font-family: 'Inter'">Heading</h1>
<p class="font-sans">Body with different font</p>
```

## Good Example

```js
// tailwind.config.js
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  theme: {
    extend: {
      fontFamily: {
        // Override sans with custom font + fallbacks
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],

        // Add display font for headings
        display: ['Cal Sans', 'Inter var', ...defaultTheme.fontFamily.sans],

        // Add mono font
        mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],

        // Add serif for specific use cases
        serif: ['Merriweather', ...defaultTheme.fontFamily.serif],
      },
    },
  },
}
```

## Why

1. **Fallback chain**: System fonts display while custom fonts load.

2. **Consistent typography**: Font families are used consistently across the app.

3. **Performance**: Fallbacks prevent layout shift during font loading.

4. **Flexibility**: Multiple font families for different purposes.

5. **Maintainability**: Change fonts in one place, applies everywhere.

## Usage

```html
<!-- Default sans font (Inter) -->
<p class="font-sans">Body text using Inter</p>

<!-- Display font for headings -->
<h1 class="font-display text-4xl font-bold">
  Heading with display font
</h1>

<!-- Mono font for code -->
<code class="font-mono">const x = 42;</code>

<!-- Serif for articles -->
<article class="font-serif prose">
  Long-form content in serif font
</article>
```

## Font Loading Strategies

### Using @font-face

```css
/* globals.css */
@font-face {
  font-family: 'Inter var';
  font-style: normal;
  font-weight: 100 900;
  font-display: swap;
  src: url('/fonts/Inter-roman.var.woff2') format('woff2');
}

@font-face {
  font-family: 'Inter var';
  font-style: italic;
  font-weight: 100 900;
  font-display: swap;
  src: url('/fonts/Inter-italic.var.woff2') format('woff2');
}
```

### Using Google Fonts (Next.js)

```jsx
// app/layout.tsx
import { Inter, Merriweather } from 'next/font/google'

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
})

const merriweather = Merriweather({
  weight: ['400', '700'],
  subsets: ['latin'],
  variable: '--font-merriweather',
  display: 'swap',
})

export default function RootLayout({ children }) {
  return (
    <html className={`${inter.variable} ${merriweather.variable}`}>
      <body>{children}</body>
    </html>
  )
}
```

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['var(--font-inter)', ...defaultTheme.fontFamily.sans],
        serif: ['var(--font-merriweather)', ...defaultTheme.fontFamily.serif],
      },
    },
  },
}
```

## Font Feature Settings

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],
      },
    },
  },
}
```

```css
/* Enable OpenType features */
.font-feature-settings {
  font-feature-settings:
    'cv01' 1,  /* Alternate a */
    'cv02' 1,  /* Alternate g */
    'cv03' 1,  /* Alternate i */
    'cv04' 1,  /* Alternate l */
    'ss01' 1,  /* Open digits */
    'ss02' 1,  /* Disambiguation */
    'case' 1,  /* Case-sensitive forms */
    'zero' 1;  /* Slashed zero */
}

/* Numeric features */
.tabular-nums {
  font-variant-numeric: tabular-nums;
}

.oldstyle-nums {
  font-variant-numeric: oldstyle-nums;
}
```

## Variable Fonts Configuration

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      fontWeight: {
        // Variable font allows any weight
        thin: '100',
        extralight: '200',
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
        extrabold: '800',
        black: '900',
      },
    },
  },
}
```

## Font Size with Line Height

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      fontSize: {
        // [fontSize, lineHeight]
        'xs': ['0.75rem', '1rem'],
        'sm': ['0.875rem', '1.25rem'],
        'base': ['1rem', '1.5rem'],
        'lg': ['1.125rem', '1.75rem'],
        'xl': ['1.25rem', '1.75rem'],
        '2xl': ['1.5rem', '2rem'],

        // [fontSize, { lineHeight, letterSpacing, fontWeight }]
        'display-lg': ['4.5rem', {
          lineHeight: '1.1',
          letterSpacing: '-0.02em',
          fontWeight: '700',
        }],
        'display-md': ['3.75rem', {
          lineHeight: '1.1',
          letterSpacing: '-0.02em',
          fontWeight: '700',
        }],
      },
    },
  },
}
```

## Complete Typography Setup

```js
// tailwind.config.js
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],
        display: ['Cal Sans', ...defaultTheme.fontFamily.sans],
        mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
      },
      fontSize: {
        'display-2xl': ['4.5rem', { lineHeight: '1', letterSpacing: '-0.02em' }],
        'display-xl': ['3.75rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
        'display-lg': ['3rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
        'display-md': ['2.25rem', { lineHeight: '1.2', letterSpacing: '-0.02em' }],
        'display-sm': ['1.875rem', { lineHeight: '1.2', letterSpacing: '-0.01em' }],
        'display-xs': ['1.5rem', { lineHeight: '1.2' }],
      },
      letterSpacing: {
        tightest: '-0.04em',
        tighter: '-0.02em',
        tight: '-0.01em',
      },
    },
  },
}
```

## Usage Example

```html
<header>
  <h1 class="font-display text-display-xl tracking-tighter">
    Welcome to Our Site
  </h1>
</header>

<main class="font-sans">
  <p class="text-lg leading-relaxed">
    Body content with comfortable reading line height.
  </p>

  <pre class="font-mono text-sm">
    <code>console.log('Hello');</code>
  </pre>
</main>
```
