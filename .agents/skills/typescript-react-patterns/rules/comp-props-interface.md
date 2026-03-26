---
title: Component Props Interface
impact: CRITICAL
impactDescription: "ensures consistent and extensible component APIs"
tags: component, interface, props, typing
---

## Component Props Interface

**Impact: CRITICAL (ensures consistent and extensible component APIs)**

Consistent typing strategy makes code predictable and maintainable. Interfaces are preferred for props because they are extendable, provide better error messages, and align with React's composition model.

## Incorrect

```tsx
// ❌ Bad
// Using type when interface is better
type ButtonProps = {
  label: string
  onClick: () => void
}

// Inline types - not reusable
function Button({ label, onClick }: { label: string; onClick: () => void }) {
  return <button onClick={onClick}>{label}</button>
}

// No typing
function Card(props) {
  return <div>{props.title}</div>
}
```

**Problems:**
- Type aliases cannot be extended with `extends` for composition
- Inline types are not reusable across components
- Missing type annotations result in implicit `any` types

## Correct

```tsx
// ✅ Good
// Interface for component props
interface ButtonProps {
  label: string
  onClick: () => void
  disabled?: boolean
}

function Button({ label, onClick, disabled = false }: ButtonProps) {
  return (
    <button onClick={onClick} disabled={disabled}>
      {label}
    </button>
  )
}

// Extending interfaces
interface IconButtonProps extends ButtonProps {
  icon: React.ReactNode
  iconPosition?: 'left' | 'right'
}

function IconButton({
  label,
  onClick,
  disabled,
  icon,
  iconPosition = 'left',
}: IconButtonProps) {
  return (
    <button onClick={onClick} disabled={disabled}>
      {iconPosition === 'left' && icon}
      {label}
      {iconPosition === 'right' && icon}
    </button>
  )
}

// Extend native button props (separate component example)
interface NativeButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant: 'primary' | 'secondary'
  isLoading?: boolean
}

function NativeButton({
  variant,
  isLoading,
  children,
  disabled,
  ...props
}: NativeButtonProps) {
  return (
    <button
      className={`btn-${variant}`}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading ? 'Loading...' : children}
    </button>
  )
}

// Generic interface
interface SelectProps<T> {
  options: T[]
  value: T
  onChange: (value: T) => void
  getLabel: (option: T) => string
}

function Select<T>({ options, value, onChange, getLabel }: SelectProps<T>) {
  return (
    <select
      value={getLabel(value)}
      onChange={(e) => {
        const selected = options.find((o) => getLabel(o) === e.target.value)
        if (selected) onChange(selected)
      }}
    >
      {options.map((option) => (
        <option key={getLabel(option)} value={getLabel(option)}>
          {getLabel(option)}
        </option>
      ))}
    </select>
  )
}
```

**Benefits:**
- Interfaces are extendable with `extends` for composable component APIs
- Better TypeScript error messages compared to type aliases
- Extending HTML element attributes gives components native prop support
- Generic interfaces enable type-safe reusable components

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
