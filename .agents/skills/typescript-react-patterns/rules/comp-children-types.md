---
title: Component Children Types
impact: CRITICAL
impactDescription: "prevents runtime errors from invalid children"
tags: component, children, ReactNode, typing
---

## Component Children Types

**Impact: CRITICAL (prevents runtime errors from invalid children)**

`children` is one of the most commonly used props. Using the wrong type causes type errors or allows invalid usage. Choose the right type based on what your component accepts.

## Incorrect

```tsx
// ❌ Bad
// Too restrictive - won't accept strings or numbers
interface CardProps {
  children: React.ReactElement
}

// This fails:
<Card>Hello</Card>  // Error: string is not ReactElement
<Card>{42}</Card>   // Error: number is not ReactElement

// No type - any is implied
interface CardProps {
  children: any
}

// JSX.Element - React Native incompatible
interface CardProps {
  children: JSX.Element
}
```

**Problems:**
- `React.ReactElement` rejects valid renderable values like strings, numbers, and fragments
- Using `any` removes all type checking and allows invalid children
- `JSX.Element` is not cross-platform compatible

## Correct

```tsx
// ✅ Good
// ReactNode (Most Common) - Accepts anything React can render
interface CardProps {
  title: string
  children: React.ReactNode
}

function Card({ title, children }: CardProps) {
  return (
    <div className="card">
      <h2>{title}</h2>
      {children}
    </div>
  )
}

// All valid:
<Card title="Welcome">Hello</Card>
<Card title="Count">{42}</Card>
<Card title="User"><UserProfile /></Card>
<Card title="List">{items.map(i => <Item key={i.id} />)}</Card>
<Card title="Maybe">{showContent && <Content />}</Card>
<Card title="Empty">{null}</Card>

// ReactElement (JSX Only) - When you need to access element props
interface TabsProps {
  children: React.ReactElement<TabProps> | React.ReactElement<TabProps>[]
}

interface TabProps {
  label: string
  children: React.ReactNode
}

function Tabs({ children }: TabsProps) {
  const tabs = React.Children.toArray(children) as React.ReactElement<TabProps>[]

  return (
    <div>
      <div className="tab-list">
        {tabs.map((tab, i) => (
          <button key={i}>{tab.props.label}</button>
        ))}
      </div>
      <div className="tab-panels">
        {children}
      </div>
    </div>
  )
}

// Render Props - Function as children
interface DataFetcherProps<T> {
  url: string
  children: (data: T, loading: boolean, error: Error | null) => React.ReactNode
}

function DataFetcher<T>({ url, children }: DataFetcherProps<T>) {
  const [data, setData] = useState<T | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<Error | null>(null)

  // fetch logic...

  return <>{children(data as T, loading, error)}</>
}

// PropsWithChildren Utility
import { PropsWithChildren } from 'react'

interface CardBaseProps {
  title: string
}

function Card({ title, children }: PropsWithChildren<CardBaseProps>) {
  return (
    <div>
      <h2>{title}</h2>
      {children}
    </div>
  )
}

// Required vs Optional Children (pick one per component)
// Option A: Required children
// interface ContainerProps { children: React.ReactNode }

// Option B: Truly required (must provide content)
// interface ContainerProps { children: NonNullable<React.ReactNode> }

// Option C: Optional children
// interface ContainerProps { children?: React.ReactNode }
```

**Benefits:**
- Correct types prevent runtime errors
- Better autocomplete and documentation
- Catches invalid children at compile time

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
