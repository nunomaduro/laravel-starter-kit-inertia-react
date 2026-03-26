---
id: config-presets
title: Tailwind Presets Configuration
priority: MEDIUM
category: Custom Configuration
---

# Tailwind Presets Configuration

Use presets to share common Tailwind configurations across multiple projects or team members.

## Bad Example

```js
// Copying entire config between projects
// project-a/tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: { /* 50+ lines of color config */ },
      spacing: { /* spacing config */ },
      fontFamily: { /* font config */ },
    },
  },
  plugins: [ /* plugins list */ ],
}

// project-b/tailwind.config.js
// Same config copy-pasted, already out of sync...
```

## Good Example

```js
// packages/tailwind-preset/index.js
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  theme: {
    extend: {
      colors: {
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
      },
      fontFamily: {
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],
        display: ['Cal Sans', ...defaultTheme.fontFamily.sans],
      },
      borderRadius: {
        DEFAULT: '0.5rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/forms'),
  ],
}
```

```js
// project-a/tailwind.config.js
module.exports = {
  presets: [
    require('@mycompany/tailwind-preset'),
  ],
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
  theme: {
    extend: {
      // Project-specific overrides
      colors: {
        accent: '#ff5500',
      },
    },
  },
}
```

## Why

1. **Consistency**: Same design tokens across all company projects.

2. **Maintainability**: Update preset once, all projects benefit.

3. **Onboarding**: New projects start with correct configuration.

4. **Version control**: Preset changes are tracked and versioned.

5. **Team alignment**: Design system is encoded in code.

## Creating a Preset Package

### Package Structure

```
packages/tailwind-preset/
├── index.js
├── package.json
├── colors.js
├── typography.js
└── plugins/
    └── custom-plugin.js
```

### package.json

```json
{
  "name": "@mycompany/tailwind-preset",
  "version": "1.0.0",
  "main": "index.js",
  "peerDependencies": {
    "tailwindcss": "^3.0.0"
  },
  "dependencies": {
    "@tailwindcss/forms": "^0.5.0",
    "@tailwindcss/typography": "^0.5.0"
  }
}
```

### index.js

```js
const colors = require('./colors')
const typography = require('./typography')
const customPlugin = require('./plugins/custom-plugin')

module.exports = {
  theme: {
    extend: {
      colors,
      ...typography,
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('@tailwindcss/forms'),
    customPlugin,
  ],
}
```

### colors.js

```js
module.exports = {
  brand: {
    50: '#eff6ff',
    100: '#dbeafe',
    500: '#3b82f6',
    600: '#2563eb',
    900: '#1e3a8a',
  },
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
}
```

## Using the Preset

### Install

```bash
npm install @mycompany/tailwind-preset
```

### Configure

```js
// tailwind.config.js
module.exports = {
  presets: [
    require('@mycompany/tailwind-preset'),
  ],
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
  // Project-specific additions/overrides
  theme: {
    extend: {
      colors: {
        // Add project-specific colors
        accent: '#ff5500',
      },
    },
  },
}
```

## Multiple Presets

```js
// tailwind.config.js
module.exports = {
  presets: [
    require('@mycompany/tailwind-preset'),    // Base company preset
    require('@mycompany/tailwind-marketing'), // Marketing-specific additions
  ],
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
}
```

Presets are merged in order, later presets override earlier ones.

## Preset with Options

```js
// packages/tailwind-preset/index.js
module.exports = function(options = {}) {
  const { enableDarkMode = true, useSerifFont = false } = options

  return {
    darkMode: enableDarkMode ? 'class' : 'media',
    theme: {
      extend: {
        fontFamily: useSerifFont
          ? { sans: ['Merriweather', 'serif'] }
          : { sans: ['Inter', 'sans-serif'] },
      },
    },
  }
}
```

```js
// tailwind.config.js
module.exports = {
  presets: [
    require('@mycompany/tailwind-preset')({
      enableDarkMode: true,
      useSerifFont: false,
    }),
  ],
}
```

## Disabling Default Preset

By default, Tailwind uses its own preset. To start completely fresh:

```js
// tailwind.config.js
module.exports = {
  presets: [], // No presets, including Tailwind's default
  // You must define everything yourself
  theme: {
    colors: {
      // All colors must be defined
    },
    spacing: {
      // All spacing must be defined
    },
  },
}
```

## Preset Versioning Strategy

1. **Semantic versioning**: Use semver for breaking changes
2. **Changelog**: Document all changes
3. **Migration guides**: Provide upgrade paths
4. **Lock versions**: Pin preset versions in projects

```json
// package.json
{
  "dependencies": {
    "@mycompany/tailwind-preset": "^2.0.0"
  }
}
```

## Testing Presets

```js
// packages/tailwind-preset/tests/preset.test.js
const preset = require('../index')

describe('Tailwind Preset', () => {
  it('should include brand colors', () => {
    expect(preset.theme.extend.colors.brand).toBeDefined()
  })

  it('should include required plugins', () => {
    expect(preset.plugins.length).toBeGreaterThan(0)
  })
})
```
