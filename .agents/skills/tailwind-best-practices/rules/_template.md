---
id: rule-id-kebab-case
title: Rule Title
priority: CRITICAL | HIGH | MEDIUM | LOW
category: Responsive Design | Dark Mode | Component Patterns | Custom Configuration | Spacing & Typography | Animation | Performance
---

# Rule Title

Brief description of what this rule covers and why it's important.

## Bad Example

```html
<!-- Anti-pattern: What not to do -->
<div class="w-full p-4 bg-blue-500">
  Example showing incorrect usage
</div>
```

```jsx
// Anti-pattern in React/JSX
function BadComponent() {
  return (
    <div className="inline m-4">
      Incorrect pattern
    </div>
  )
}
```

## Good Example

```html
<!-- Best practice: Correct implementation -->
<div class="
  w-full max-w-screen-xl mx-auto
  p-6 md:p-8
  bg-primary-600 dark:bg-primary-500
  rounded-lg shadow-sm
  transition-colors duration-200
">
  Example showing correct usage with Tailwind v4 patterns
</div>
```

```jsx
// Best practice in React/JSX with TypeScript
interface CardProps {
  children: React.ReactNode
  variant?: 'primary' | 'secondary'
  className?: string
}

function GoodComponent({ children, variant = 'primary', className }: CardProps) {
  return (
    <div className={cn(
      'w-full rounded-lg p-6 transition-colors',
      {
        'bg-primary-600 text-white dark:bg-primary-500': variant === 'primary',
        'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white': variant === 'secondary',
      },
      className
    )}>
      {children}
    </div>
  )
}
```

## Why

1. **Reason 1**: Explanation of first benefit with concrete example.

2. **Reason 2**: How this improves code quality or performance.

3. **Reason 3**: Impact on maintainability or user experience.

4. **Reason 4**: Accessibility or responsiveness consideration.

5. **Reason 5**: Tailwind v4 specific improvements or patterns.

## Usage Examples

### Basic Usage
```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
  <div>Item 1</div>
  <div>Item 2</div>
  <div>Item 3</div>
</div>
```

### Advanced Pattern
```jsx
import { cn } from '@/lib/utils'

function AdvancedExample() {
  return (
    <div className={cn(
      // Base styles
      'flex items-center justify-between',
      'px-4 py-3 rounded-md',

      // Responsive
      'flex-col sm:flex-row',
      'gap-3 sm:gap-4',

      // Colors with dark mode
      'bg-white dark:bg-gray-900',
      'text-gray-900 dark:text-white',
      'border border-gray-200 dark:border-gray-700',

      // Interactive states
      'hover:bg-gray-50 dark:hover:bg-gray-800',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500',

      // Transitions
      'transition-colors duration-200'
    )}>
      Content
    </div>
  )
}
```

### With CSS Variables (Tailwind v4)
```html
<!-- Using @theme directive for custom properties -->
<div class="
  bg-[--color-background]
  text-[--color-foreground]
  border-[--color-border]
">
  Dynamic theme support
</div>
```

```css
@theme {
  --color-background: oklch(100% 0 0);
  --color-foreground: oklch(0% 0 0);
  --color-border: oklch(90% 0 0);

  @media (prefers-color-scheme: dark) {
    --color-background: oklch(20% 0 0);
    --color-foreground: oklch(100% 0 0);
    --color-border: oklch(30% 0 0);
  }
}
```

## Related Patterns

- Related utility classes: `flex`, `grid`, `space-y-*`
- Related rules: Link to other relevant rules
- Documentation: [Tailwind CSS Docs](https://tailwindcss.com/docs/)

## Common Mistakes

### Mistake 1
```html
<!-- Wrong -->
<div class="w-[100%]">Using arbitrary value for standard utility</div>

<!-- Correct -->
<div class="w-full">Using built-in utility</div>
```

### Mistake 2
```html
<!-- Wrong -->
<div class="px-4 px-8">Conflicting utilities</div>

<!-- Correct -->
<div class="px-8">Single utility wins</div>
```

## Tailwind v4 Features

Highlight any Tailwind v4 specific improvements:
- New `@theme` directive for CSS variables
- Improved color spaces (oklch)
- Container queries with `@container`
- New logical properties
- Simplified configuration

## Performance Tips

- Avoid unnecessary arbitrary values
- Use standard utilities when possible
- Leverage JIT mode for custom values
- Consider bundle size impact
