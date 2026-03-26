---
id: config-extend-theme
title: Extend Theme Configuration
priority: HIGH
category: Custom Configuration
---

# Extend Theme Configuration

Use the `extend` key in your Tailwind config to add custom values while preserving defaults.

## Bad Example

```js
// tailwind.config.js
module.exports = {
  theme: {
    // DANGER: This replaces ALL colors, losing Tailwind defaults
    colors: {
      primary: '#3b82f6',
      secondary: '#64748b',
    },

    // DANGER: This replaces ALL spacing values
    spacing: {
      sm: '0.5rem',
      md: '1rem',
      lg: '2rem',
    },

    // DANGER: This replaces ALL font sizes
    fontSize: {
      heading: '2rem',
      body: '1rem',
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
      // ADD to existing colors
      colors: {
        primary: {
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
        secondary: {
          50: '#f8fafc',
          100: '#f1f5f9',
          // ... full scale
        },
      },

      // ADD custom spacing values
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },

      // ADD custom font sizes
      fontSize: {
        'xxs': '0.625rem',
        'display': ['4.5rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
      },

      // ADD custom breakpoints
      screens: {
        'xs': '475px',
        '3xl': '1920px',
      },
    },
  },
}
```

## Why

1. **Preserves defaults**: All built-in Tailwind utilities remain available.

2. **Safer upgrades**: Custom values won't conflict with future Tailwind updates.

3. **Smaller config**: Only specify what you're adding, not everything.

4. **Predictable behavior**: Team members can rely on standard Tailwind values.

5. **Better documentation**: Custom additions are clearly separated from defaults.

## When to Override (Not Extend)

Sometimes you intentionally want to replace defaults:

```js
// tailwind.config.js
module.exports = {
  theme: {
    // Override: Use custom font stack everywhere
    fontFamily: {
      sans: ['Inter var', 'system-ui', 'sans-serif'],
      serif: ['Merriweather', 'Georgia', 'serif'],
      mono: ['JetBrains Mono', 'monospace'],
    },

    // Override: Use custom breakpoints
    screens: {
      'sm': '640px',
      'md': '768px',
      'lg': '1024px',
      'xl': '1280px',
      // Intentionally removed 2xl
    },

    extend: {
      // Still extend other values
      colors: {
        brand: '#ff5500',
      },
    },
  },
}
```

## Extending with CSS Variables

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Reference CSS variables for dynamic theming
        background: 'hsl(var(--background) / <alpha-value>)',
        foreground: 'hsl(var(--foreground) / <alpha-value>)',
        primary: {
          DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
          foreground: 'hsl(var(--primary-foreground) / <alpha-value>)',
        },
        muted: {
          DEFAULT: 'hsl(var(--muted) / <alpha-value>)',
          foreground: 'hsl(var(--muted-foreground) / <alpha-value>)',
        },
      },
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },
    },
  },
}
```

## Extending Animations

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'spin-slow': 'spin 3s linear infinite',
        'bounce-slow': 'bounce 2s infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
      },
    },
  },
}
```

## Extending Typography

```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      typography: (theme) => ({
        DEFAULT: {
          css: {
            color: theme('colors.gray.700'),
            a: {
              color: theme('colors.primary.600'),
              '&:hover': {
                color: theme('colors.primary.800'),
              },
            },
            'code::before': {
              content: '""',
            },
            'code::after': {
              content: '""',
            },
          },
        },
        dark: {
          css: {
            color: theme('colors.gray.300'),
            a: {
              color: theme('colors.primary.400'),
            },
          },
        },
      }),
    },
  },
}
```

## Checking Available Defaults

View all default values in Tailwind's source:

```bash
npx tailwindcss init --full
```

Or reference the documentation for each utility's default values.

## v4: Use @theme Instead

In Tailwind v4, `tailwind.config.js` is replaced by `@theme {}` in CSS. The `@theme` block extends the default theme by default — no `extend` key needed:

```css
@import "tailwindcss";

@theme {
  /* Extends defaults automatically */
  --color-brand-500: #3b82f6;
  --font-sans: "Inter", sans-serif;
  --spacing-18: 4.5rem;

  /* To override ALL values in a namespace: */
  --color-*: initial;
  --color-white: #fff;
  --color-brand: #3b82f6;
}
```

See `v4-theme-configuration` for full details.
