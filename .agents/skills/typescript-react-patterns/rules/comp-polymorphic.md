---
title: Polymorphic Component Typing
impact: CRITICAL
impactDescription: "enables type-safe rendering as different HTML elements"
tags: component, polymorphic, as-prop, generic
---

## Polymorphic Component Typing

**Impact: CRITICAL (enables type-safe rendering as different HTML elements)**

Creating type-safe polymorphic components that can render as different HTML elements using the `as` prop pattern.

## Incorrect

```tsx
// ❌ Bad
// Using 'any' loses all type safety
interface BoxProps {
  as?: any;
  children?: React.ReactNode;
}

const Box = ({ as: Component = 'div', ...props }: BoxProps) => {
  return <Component {...props} />;
};

// No validation of props for the rendered element
<Box as="a">This should have href</Box> // No type error, but missing required href

// String union doesn't provide prop inference
interface TextProps {
  as?: 'h1' | 'h2' | 'h3' | 'p' | 'span';
  children: React.ReactNode;
}

const Text = ({ as: Component = 'p', children }: TextProps) => {
  return <Component>{children}</Component>;
};
// Can't pass element-specific props like htmlFor to label
```

**Problems:**
- Using `any` for the `as` prop removes all type safety and autocomplete
- No validation that passed props match the rendered element type
- String unions cannot infer element-specific props

## Correct

```tsx
// ✅ Good
import React from 'react';

// Core polymorphic types
type AsProp<C extends React.ElementType> = {
  as?: C;
};

type PropsToOmit<C extends React.ElementType, P> = keyof (AsProp<C> & P);

type PolymorphicComponentProps<
  C extends React.ElementType,
  Props = object
> = Props &
  AsProp<C> &
  Omit<React.ComponentPropsWithoutRef<C>, PropsToOmit<C, Props>>;

type PolymorphicComponentPropsWithRef<
  C extends React.ElementType,
  Props = object
> = PolymorphicComponentProps<C, Props> & {
  ref?: PolymorphicRef<C>;
};

type PolymorphicRef<C extends React.ElementType> =
  React.ComponentPropsWithRef<C>['ref'];

// Simple polymorphic Box component
interface BoxOwnProps {
  padding?: 'none' | 'sm' | 'md' | 'lg';
  margin?: 'none' | 'sm' | 'md' | 'lg';
  display?: 'block' | 'flex' | 'grid' | 'inline';
}

type BoxProps<C extends React.ElementType = 'div'> = PolymorphicComponentProps<C, BoxOwnProps>;

function Box<C extends React.ElementType = 'div'>({
  as,
  padding = 'none',
  margin = 'none',
  display = 'block',
  className,
  style,
  ...rest
}: BoxProps<C>): React.ReactElement {
  const Component = as ?? 'div';

  const computedStyle: React.CSSProperties = {
    padding: padding !== 'none' ? `var(--spacing-${padding})` : undefined,
    margin: margin !== 'none' ? `var(--spacing-${margin})` : undefined,
    display,
    ...style,
  };

  return <Component className={className} style={computedStyle} {...rest} />;
}

// Usage with full type inference
<Box padding="md">Default div</Box>
<Box as="section" padding="lg">Section element</Box>
<Box as="a" href="/home">Link with href autocomplete</Box>
<Box as="button" onClick={() => {}}>Button with onClick</Box>

// Polymorphic with discriminated unions
type ButtonVariant = 'solid' | 'outline' | 'ghost';

interface ButtonBaseProps {
  variant?: ButtonVariant;
  size?: 'sm' | 'md' | 'lg';
  isLoading?: boolean;
}

type ButtonAsButton = ButtonBaseProps &
  Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, keyof ButtonBaseProps> & {
    as?: 'button';
  };

type ButtonAsLink = ButtonBaseProps &
  Omit<React.AnchorHTMLAttributes<HTMLAnchorElement>, keyof ButtonBaseProps> & {
    as: 'a';
  };

type ButtonProps = ButtonAsButton | ButtonAsLink;

function Button(props: ButtonProps): React.ReactElement {
  const {
    as = 'button',
    variant = 'solid',
    size = 'md',
    isLoading = false,
    className,
    children,
    ...rest
  } = props;

  const classes = `btn btn-${variant} btn-${size} ${className ?? ''}`;

  if (as === 'a') {
    return (
      <a className={classes} {...(rest as React.AnchorHTMLAttributes<HTMLAnchorElement>)}>
        {isLoading ? 'Loading...' : children}
      </a>
    );
  }

  return (
    <button
      className={classes}
      disabled={isLoading}
      {...(rest as React.ButtonHTMLAttributes<HTMLButtonElement>)}
    >
      {isLoading ? 'Loading...' : children}
    </button>
  );
}

// Usage with proper type narrowing
<Button onClick={() => console.log('clicked')}>Click me</Button>
<Button as="a" href="/home">Go home</Button>
```

**Benefits:**
- Full type safety: props are validated based on the rendered element
- IDE autocomplete suggests valid props for each element type
- Single component can render as any HTML element or custom component
- Properly typed refs match the rendered element
- Reduces need for wrapper components like LinkButton, SubmitButton, etc.

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
