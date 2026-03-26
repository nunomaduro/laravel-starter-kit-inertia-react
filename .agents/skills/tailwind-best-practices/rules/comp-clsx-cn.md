---
id: comp-clsx-cn
title: Conditional Classes with clsx/cn
priority: HIGH
category: Component Patterns
---

# Conditional Classes with clsx/cn

## Why It Matters

Conditional class names in React get messy quickly. The `clsx` + `tailwind-merge` pattern (often called `cn`) provides a clean, type-safe way to handle conditional classes while properly merging Tailwind utilities.

## Incorrect

```tsx
// ❌ String concatenation - error prone
<div className={'px-4 ' + (isActive ? 'bg-blue-500' : 'bg-gray-500') + ' ' + className}>

// ❌ Template literals - still messy
<div className={`px-4 ${isActive ? 'bg-blue-500' : 'bg-gray-500'} ${className}`}>

// ❌ Array join - doesn't handle conflicts
<div className={['px-4', isActive && 'bg-blue-500', className].filter(Boolean).join(' ')}>

// ❌ Tailwind conflicts not resolved
// className = "px-8" passed in
<div className={`px-4 ${className}`}>  // Both px-4 and px-8 in output!
```

## Correct

### Setup

```bash
npm install clsx tailwind-merge
```

```ts
// lib/utils.ts
import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
```

### Basic Usage

```tsx
import { cn } from '@/lib/utils'

// Simple conditional
<div className={cn('px-4 py-2', isActive && 'bg-blue-500')}>

// Multiple conditions
<div className={cn(
  'rounded-lg border',
  isActive && 'border-blue-500',
  isDisabled && 'opacity-50 cursor-not-allowed',
  className  // Allow override
)}>

// Object syntax
<div className={cn(
  'px-4 py-2',
  {
    'bg-blue-500 text-white': variant === 'primary',
    'bg-gray-100 text-gray-900': variant === 'secondary',
    'opacity-50': isDisabled,
  }
)}>
```

### Button Component Example

```tsx
import { cn } from '@/lib/utils'
import { ButtonHTMLAttributes, forwardRef } from 'react'

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  isLoading?: boolean
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ variant = 'primary', size = 'md', isLoading, className, children, disabled, ...props }, ref) => {
    return (
      <button
        ref={ref}
        disabled={disabled || isLoading}
        className={cn(
          // Base styles
          'inline-flex items-center justify-center rounded-md font-medium',
          'transition-colors focus-visible:outline-none focus-visible:ring-2',
          'disabled:pointer-events-none disabled:opacity-50',

          // Variants
          {
            'bg-blue-600 text-white hover:bg-blue-700 focus-visible:ring-blue-500':
              variant === 'primary',
            'bg-gray-100 text-gray-900 hover:bg-gray-200 focus-visible:ring-gray-500':
              variant === 'secondary',
            'border border-gray-300 bg-transparent hover:bg-gray-100 focus-visible:ring-gray-500':
              variant === 'outline',
            'bg-transparent hover:bg-gray-100 focus-visible:ring-gray-500':
              variant === 'ghost',
            'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500':
              variant === 'danger',
          },

          // Sizes
          {
            'h-8 px-3 text-sm': size === 'sm',
            'h-10 px-4 text-sm': size === 'md',
            'h-12 px-6 text-base': size === 'lg',
          },

          // Allow className override
          className
        )}
        {...props}
      >
        {isLoading && (
          <svg className="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
            {/* spinner */}
          </svg>
        )}
        {children}
      </button>
    )
  }
)

Button.displayName = 'Button'
export { Button }
```

### Why tailwind-merge Matters

```tsx
// Without tailwind-merge - conflicting classes remain
clsx('px-4', 'px-8')  // "px-4 px-8" - conflict!

// With tailwind-merge - later class wins
twMerge('px-4', 'px-8')  // "px-8"

// cn combines both
cn('px-4 text-red-500', 'px-8')  // "px-8 text-red-500"
```

### Input Component Example

```tsx
import { cn } from '@/lib/utils'
import { InputHTMLAttributes, forwardRef } from 'react'

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  error?: string
}

const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ className, error, ...props }, ref) => {
    return (
      <div>
        <input
          ref={ref}
          className={cn(
            'flex h-10 w-full rounded-md border bg-white px-3 py-2 text-sm',
            'placeholder:text-gray-400',
            'focus:outline-none focus:ring-2 focus:ring-offset-2',
            'disabled:cursor-not-allowed disabled:opacity-50',
            error
              ? 'border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:ring-blue-500',
            className
          )}
          {...props}
        />
        {error && <p className="mt-1 text-sm text-red-500">{error}</p>}
      </div>
    )
  }
)

Input.displayName = 'Input'
export { Input }
```

## Benefits

- Clean, readable conditional classes
- Proper Tailwind conflict resolution
- Type-safe with TypeScript
- Allows component className overrides
- Handles undefined/null gracefully
