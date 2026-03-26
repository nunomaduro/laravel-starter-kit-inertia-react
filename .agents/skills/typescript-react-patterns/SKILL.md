---
name: typescript-react-patterns
description: TypeScript best practices for React development. Use when writing typed React components, hooks, events, refs, or generic components. Triggers on tasks involving TypeScript errors, type definitions, props typing, or type-safe React patterns.
license: MIT
metadata:
  author: agent-skills
  version: "2.0.0"
---

# TypeScript React Patterns

Type-safe React with TypeScript. Contains 33 rules across 7 categories covering component typing, hooks, event handling, refs, generics, context, and utility types.

## Metadata

- **Version:** 2.0.0
- **Rule Count:** 33 rules across 7 categories
- **License:** MIT

## When to Apply

Reference these guidelines when:
- Typing React component props
- Creating custom hooks with TypeScript
- Handling events with proper types
- Working with refs (DOM, mutable, imperative)
- Building generic, reusable components
- Setting up typed Context providers
- Fixing TypeScript errors in React code

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Component Typing | CRITICAL | `comp-` |
| 2 | Hook Typing | CRITICAL | `hook-` |
| 3 | Event Handling | HIGH | `event-` |
| 4 | Ref Typing | HIGH | `ref-` |
| 5 | Generic Components | MEDIUM | `generic-` |
| 6 | Context & State | MEDIUM | `ctx-` |
| 7 | Utility Types | LOW | `util-` |

## Quick Reference

### 1. Component Typing (CRITICAL)

- `comp-props-interface` - Use interface for props, type for unions
- `comp-children-types` - Correct children typing (ReactNode, ReactElement)
- `comp-default-props` - Default props with destructuring defaults
- `comp-forward-ref` - Typing forwardRef components
- `comp-polymorphic` - Polymorphic "as" prop typing
- `comp-fc-vs-function` - Function declaration vs React.FC
- `comp-display-name` - Display names for debugging
- `comp-rest-props` - Spreading rest props with proper types

### 2. Hook Typing (CRITICAL)

- `hook-usestate` - useState with proper generic types
- `hook-useref` - useRef for DOM elements and mutable values
- `hook-use-reducer` - useReducer with discriminated union actions
- `hook-use-callback` - useCallback with typed parameters
- `hook-use-memo` - useMemo with typed return values
- `hook-use-context` - useContext with null checking
- `hook-custom-hooks` - Custom hook return types
- `hook-generic-hooks` - Generic custom hooks

### 3. Event Handling (HIGH)

- `event-handler-types` - Event handler type patterns
- `event-click-handler` - Click event typing
- `event-form` - Form event handling (submit, change, select)
- `event-keyboard` - Keyboard event types

### 4. Ref Typing (HIGH)

- `ref-dom-elements` - useRef with specific HTML element types
- `ref-callback` - Callback ref pattern for DOM measurement
- `ref-imperative-handle` - useImperativeHandle typing

### 5. Generic Components (MEDIUM)

- `generic-list` - Generic list components
- `generic-select` - Generic select/dropdown
- `generic-table` - Generic table with typed columns
- `generic-constraints` - Generic constraints with extends

### 6. Context & State (MEDIUM)

- `ctx-create` - Creating typed context
- `ctx-provider` - Provider pattern with null check hook
- `ctx-reducer` - Context with useReducer

### 7. Utility Types (LOW)

- `util-component-props` - ComponentPropsWithoutRef for HTML props
- `util-pick-omit` - Pick, Omit, Partial for prop derivation
- `util-discriminated-unions` - Discriminated unions for state machines

## Essential Patterns

### Component Props

```tsx
interface ButtonProps {
  variant: 'primary' | 'secondary' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  children: React.ReactNode
  onClick?: () => void
}

function Button({ variant, size = 'md', children, onClick }: ButtonProps) {
  return (
    <button className={`btn-${variant} btn-${size}`} onClick={onClick}>
      {children}
    </button>
  )
}
```

### Typed Context with Null Check

```tsx
interface AuthContextType {
  user: User | null
  login: (credentials: Credentials) => Promise<void>
  logout: () => void
}

const AuthContext = createContext<AuthContextType | null>(null)

function useAuth() {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth must be used within AuthProvider')
  return context
}
```

### Generic Component

```tsx
interface ListProps<T> {
  items: T[]
  renderItem: (item: T) => React.ReactNode
  keyExtractor: (item: T) => string
}

function List<T>({ items, renderItem, keyExtractor }: ListProps<T>) {
  return <ul>{items.map(item => <li key={keyExtractor(item)}>{renderItem(item)}</li>)}</ul>
}
```

## How to Use

Read individual rule files for detailed explanations:

```
rules/comp-props-interface.md
rules/hook-usestate.md
rules/event-form.md
rules/ref-dom-elements.md
rules/util-discriminated-unions.md
```

## References

- [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
- [React + TypeScript Guide](https://react.dev/learn/typescript)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/handbook/)

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
