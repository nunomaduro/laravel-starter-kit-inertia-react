# Tailwind CSS Best Practices - Agent Guide

This skill provides comprehensive Tailwind CSS patterns and best practices for AI coding agents.

## Skill Overview

**Name:** tailwind-best-practices
**Version:** 1.0.0
**Framework:** Tailwind CSS v3.4+ / v4.0+
**Rule Count:** 29 rules across 8 categories
**License:** MIT

## When to Use This Skill

Activate this skill when:
- Writing or refactoring Tailwind CSS classes
- Implementing responsive designs with breakpoints
- Adding dark mode support to applications
- Creating reusable component patterns
- Configuring Tailwind theme customization (v3 config or v4 @theme)
- Migrating from Tailwind v3 to v4
- Building forms, buttons, cards, tables, navigation
- Optimizing Tailwind for production
- Questions about Tailwind utilities and patterns

## Rule Categories

### 1. Responsive Design (CRITICAL - 6 rules)
Mobile-first responsive patterns are fundamental to every Tailwind project.

**Key Concepts:**
- Mobile-first: Base styles apply to all screens, add breakpoints upward
- Breakpoint order: `sm:` (640px) → `md:` (768px) → `lg:` (1024px) → `xl:` (1280px) → `2xl:` (1536px)
- Container queries for component-scoped responsiveness
- Fluid typography with `clamp()` for smooth scaling
- Responsive grid systems with `grid-cols-{n}`

**Common Patterns:**
```html
<!-- Mobile-first responsive layout -->
<div class="
  w-full           <!-- Mobile: full width -->
  sm:w-1/2         <!-- Tablet: half -->
  lg:w-1/3         <!-- Desktop: third -->
  px-4 md:px-8     <!-- Responsive spacing -->
">
  Content
</div>
```

**Rules:** `resp-mobile-first`, `responsive-breakpoint-order`, `responsive-container-queries`, `responsive-fluid-typography`, `responsive-aspect-ratio`, `responsive-grid-system`

### 2. Dark Mode (CRITICAL - 6 rules)
Modern applications require seamless light/dark theme support.

**Key Concepts:**
- Class strategy (`darkMode: 'class'`) for manual toggle
- Media strategy (`darkMode: 'media'`) for system preference
- Semantic color naming for maintainable themes
- Custom color palettes with full scales (50-950)
- Smooth transitions between themes

**Common Patterns:**
```html
<!-- Dark mode aware component -->
<div class="
  bg-white dark:bg-gray-900
  text-gray-900 dark:text-white
  border border-gray-200 dark:border-gray-700
">
  Content adapts to theme
</div>
```

**Rules:** `dark-setup`, `dark-class-strategy`, `dark-media-strategy`, `dark-color-scheme`, `dark-custom-colors`, `dark-transitions`

### 3. Component Patterns (HIGH - 7 rules)
Reusable component patterns for consistent UI.

**Key Concepts:**
- Use `clsx` + `tailwind-merge` (cn utility) for conditional classes
- Component variants with proper type safety
- Consistent button, card, form, table, modal patterns
- Proper accessibility attributes
- Responsive component behavior

**Common Patterns:**
```tsx
// Button with variants using cn utility
import { cn } from '@/lib/utils'

function Button({ variant = 'primary', size = 'md', className, children }) {
  return (
    <button className={cn(
      'inline-flex items-center justify-center rounded-lg font-medium transition-colors',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
      {
        'bg-blue-600 text-white hover:bg-blue-700': variant === 'primary',
        'bg-gray-100 text-gray-900 hover:bg-gray-200': variant === 'secondary',
      },
      {
        'px-3 py-1.5 text-sm': size === 'sm',
        'px-4 py-2 text-base': size === 'md',
        'px-6 py-3 text-lg': size === 'lg',
      },
      className
    )}>
      {children}
    </button>
  )
}
```

**Rules:** `comp-clsx-cn`, `component-btn-variants`, `component-card-patterns`, `component-form-elements`, `component-modals`, `component-navigation`, `component-tables`

### 4. Custom Configuration — v3 (HIGH - 6 rules)
Extending Tailwind's v3 theme via `tailwind.config.js`. For v4, use `@theme {}` instead.

**Key Concepts:**
- Always `extend` theme, don't override (preserves defaults)
- Use full color scales (50-950) for flexibility
- Font families with proper fallbacks
- Custom spacing for layout consistency
- Plugins for extended functionality
- Presets for shared configuration

**Common Patterns:**
```js
// tailwind.config.js - Proper theme extension
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#eff6ff',
          500: '#3b82f6',
          950: '#172554',
        },
      },
      fontFamily: {
        sans: ['Inter var', ...defaultTheme.fontFamily.sans],
      },
      spacing: {
        '18': '4.5rem',
        'header': '4rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
```

**Rules:** `config-extend-theme`, `config-custom-colors`, `config-custom-fonts`, `config-custom-spacing`, `config-plugins`, `config-presets`

### 5. V4 & Migration (HIGH - 4 rules)
Tailwind CSS v4 setup, configuration, and migration from v3.

**Key Concepts:**
- CSS-first architecture: `@import "tailwindcss"` replaces `@tailwind` directives
- `@theme {}` replaces `tailwind.config.js` for design tokens
- `@utility` and `@custom-variant` replace JS plugin API
- `@variant` for nesting variants in custom CSS
- `@reference` for importing without emitting CSS
- `@source` / `@source not` for scan control
- New features: `starting:`, `forced-colors:`, `color-mix()`, `transition-discrete`
- Container queries and aspect-ratio built into core (no plugins)

**Common Patterns:**
```css
@import "tailwindcss";

@theme {
  --color-brand-500: #3b82f6;
  --font-sans: "Inter", sans-serif;
}

@utility scrollbar-hide {
  scrollbar-width: none;
}

@custom-variant hocus (&:hover, &:focus);
```

**Rules:** `v4-installation`, `v4-theme-configuration`, `v4-custom-utilities`, `v4-migration`

### 6. Spacing & Typography (MEDIUM - 0 rules)
Consistent spacing and typography systems.

**Key Concepts:**
- Use Tailwind's spacing scale (0-96)
- Custom spacing only when needed
- Typography scale with line heights
- Responsive text sizing
- Vertical rhythm

**Future Rules:** To be added based on common patterns

### 7. Animation (MEDIUM - 0 rules)
Smooth transitions and animations.

**Key Concepts:**
- Transition utilities for state changes
- Custom keyframe animations
- Respect `prefers-reduced-motion`
- Performance considerations

**Future Rules:** To be added based on common patterns

### 8. Performance (LOW - 0 rules)
Build and runtime optimization.

**Key Concepts:**
- Content configuration for tree-shaking
- JIT mode benefits
- Arbitrary value usage
- Bundle size optimization

**Future Rules:** To be added based on common patterns

## Tailwind CSS v4 Quick Reference

When working with Tailwind v4, be aware of these key differences from v3:

### Setup
```css
/* Single import replaces @tailwind directives */
@import "tailwindcss";
```

### Configuration
```css
/* @theme replaces tailwind.config.js */
@theme {
  --color-brand-500: #3b82f6;
  --font-sans: "Inter", sans-serif;
  --breakpoint-3xl: 1920px;
}
```

### Custom Code
```css
/* @utility replaces addUtilities() and @layer components */
@utility scrollbar-hide { scrollbar-width: none; }

/* @custom-variant replaces addVariant() */
@custom-variant hocus (&:hover, &:focus);

/* @variant for nesting in custom CSS */
.card {
  background: white;
  @variant dark { background: #1e293b; }
}

/* @plugin replaces require() in config */
@plugin "@tailwindcss/forms";

/* @reference for @apply without emitting CSS */
@reference "tailwindcss";
```

### New Variants and Features
```html
<!-- starting: for @starting-style animations -->
<div popover class="transition-discrete starting:open:opacity-0">

<!-- forced-colors: for Windows High Contrast -->
<input class="appearance-none forced-colors:appearance-auto">

<!-- Container query ranges (built-in, no plugin) -->
<div class="@container">
  <div class="@min-sm:@max-lg:grid-cols-2">Range query</div>
</div>

<!-- Dynamic values — no config needed -->
<div class="grid-cols-15 px-17">
```

### Plugins No Longer Needed
- `@tailwindcss/aspect-ratio` — native `aspect-*` utilities
- `@tailwindcss/container-queries` — native `@container` with range variants

## Agent Workflow

### 1. Analyze Requirements
- Is this responsive? Use mobile-first approach
- Does it need dark mode? Apply dark: variants
- Is it a reusable component? Use cn utility
- Custom colors/fonts? Extend theme properly

### 2. Write Classes
- Start with base styles (display, sizing)
- Add responsive breakpoints (mobile-first)
- Include dark mode variants
- Add interactive states (hover, focus, active)
- Apply transitions for smooth UX

### 3. Component Pattern
```tsx
// Standard component structure
<ComponentName
  className={cn(
    // Base
    'base styles',

    // Responsive
    'mobile lg:desktop',

    // Colors + Dark Mode
    'bg-white dark:bg-gray-900',
    'text-gray-900 dark:text-white',

    // Interactive
    'hover:bg-gray-50',
    'focus:ring-2',

    // Transitions
    'transition-colors duration-200',

    // Allow override
    className
  )}
/>
```

### 4. Verify Patterns
- ✅ Mobile-first breakpoint order
- ✅ Dark mode coverage
- ✅ Accessibility (ARIA, semantic HTML)
- ✅ Interactive states
- ✅ No conflicting utilities
- ✅ Proper transitions

## Essential Utilities Reference

### Layout
```html
<!-- Flexbox -->
<div class="flex items-center justify-between gap-4">

<!-- Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

<!-- Container -->
<div class="container mx-auto px-4 max-w-7xl">
```

### Spacing
```html
<!-- Padding: p-{size}, px-{size}, py-{size} -->
<div class="p-6 px-4 py-8">

<!-- Margin: m-{size}, mx-{size}, my-{size} -->
<div class="mt-4 mb-8 mx-auto">

<!-- Space between -->
<div class="space-y-4">
```

### Colors
```html
<!-- Background -->
<div class="bg-white dark:bg-gray-900">

<!-- Text -->
<p class="text-gray-900 dark:text-white">

<!-- Border -->
<div class="border border-gray-200 dark:border-gray-700">
```

### Typography
```html
<!-- Size -->
<h1 class="text-4xl md:text-5xl lg:text-6xl">

<!-- Weight -->
<p class="font-medium">

<!-- Line height -->
<p class="leading-relaxed">
```

### Interactive States
```html
<!-- Hover, focus, active -->
<button class="
  hover:bg-blue-700
  focus:outline-none focus:ring-2 focus:ring-blue-500
  active:bg-blue-800
">
```

### Transitions
```html
<!-- Smooth transitions -->
<div class="transition-colors duration-200">
<div class="transition-all duration-300 ease-in-out">
```

## Common Anti-Patterns to Avoid

### ❌ Don't: Desktop-first breakpoints
```html
<div class="w-1/4 lg:w-1/3 md:w-1/2 sm:w-full">
```

### ✅ Do: Mobile-first breakpoints
```html
<div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4">
```

### ❌ Don't: Conflicting utilities
```html
<div class="px-4 px-8">
```

### ✅ Do: Single utility or use cn
```html
<div class={cn('px-4', condition && 'px-8')}>
```

### ❌ Don't: Override theme without extend (v3)
```js
theme: {
  colors: { primary: '#blue' } // Lost all default colors!
}
```

### ✅ Do: Extend theme (v3) or use @theme (v4)
```js
// v3
theme: { extend: { colors: { brand: {...} } } }
```
```css
/* v4 — extends by default */
@theme { --color-brand-500: #3b82f6; }
```

### ❌ Don't: Arbitrary values for standard utilities
```html
<div class="w-[100%]">
```

### ✅ Do: Use built-in utilities
```html
<div class="w-full">
```

## Quick Decision Tree

**Question:** Am I building a new component?
→ YES: Use cn utility, create variants, ensure dark mode support
→ NO: Continue

**Question:** Does it need to be responsive?
→ YES: Mobile-first, add breakpoints upward (sm: md: lg:)
→ NO: Continue

**Question:** Does it have interactive states?
→ YES: Add hover:, focus:, active:, disabled: states
→ NO: Continue

**Question:** Should it transition smoothly?
→ YES: Add transition-* utilities
→ NO: Done

## Resources

- **Documentation:** https://tailwindcss.com/docs
- **Playground:** https://play.tailwindcss.com
- **Component Library:** https://tailwindui.com
- **Icons:** https://heroicons.com
- **Tools:** IntelliSense, Prettier Plugin, tailwind-merge

## Getting Help

1. Check rule files in `rules/` directory for specific patterns
2. Reference `_template.md` for rule structure
3. See `metadata.json` for official documentation links
4. Review `_sections.md` for category descriptions

## Success Metrics

When this skill is applied correctly, you should see:
- ✅ Consistent responsive behavior across devices
- ✅ Seamless dark mode transitions
- ✅ Reusable, maintainable components
- ✅ Accessible, keyboard-navigable interfaces
- ✅ Fast build times and small bundle sizes
- ✅ Clean, readable class names
- ✅ No conflicting utilities
- ✅ Type-safe component APIs

---

**Last Updated:** 2026-03-07
**Skill Version:** 1.0.0
**Maintainer:** Agent Skills Contributors
