---
title: Rest Props Typing
impact: CRITICAL
impactDescription: "ensures type-safe prop spreading to HTML elements"
tags: component, rest-props, spread, Omit
---

## Rest Props Typing

**Impact: CRITICAL (ensures type-safe prop spreading to HTML elements)**

Typing rest/spread props for components that pass through HTML attributes. Proper Omit patterns prevent prop conflicts and maintain full type safety.

## Incorrect

```tsx
// ❌ Bad
// Using 'any' for rest props
interface ButtonProps {
  variant: 'primary' | 'secondary';
  [key: string]: any; // Loses all type safety
}

const Button = ({ variant, ...rest }: ButtonProps) => {
  return <button className={`btn-${variant}`} {...rest} />;
};

// Not properly omitting custom props from HTML props
interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string;
  error: string;
}

const Input = ({ label, error, ...rest }: InputProps) => {
  // 'label' and 'error' are valid HTML attributes, causing conflicts
  return (
    <div>
      <label>{label}</label>
      <input {...rest} />
      <span>{error}</span>
    </div>
  );
};
```

**Problems:**
- Index signatures like `[key: string]: any` remove all type checking on spread props
- Not omitting custom prop names that collide with HTML attributes causes conflicts
- Missing `ComponentPropsWithoutRef` can lead to ref handling issues

## Correct

```tsx
// ✅ Good
// Properly extending and omitting HTML attributes
interface ButtonProps extends Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, 'className'> {
  variant: 'primary' | 'secondary';
  size?: 'sm' | 'md' | 'lg';
}

function Button({
  variant,
  size = 'md',
  children,
  ...rest
}: ButtonProps): React.ReactElement {
  return (
    <button className={`btn-${variant} btn-${size}`} {...rest}>
      {children}
    </button>
  );
}

// Using ComponentPropsWithoutRef for better ref handling
import { ComponentPropsWithoutRef } from 'react';

interface CustomInputProps {
  label: string;
  errorMessage?: string;
  helperText?: string;
}

type InputProps = CustomInputProps & Omit<ComponentPropsWithoutRef<'input'>, keyof CustomInputProps>;

function CustomInput({
  label,
  errorMessage,
  helperText,
  id,
  ...rest
}: InputProps): React.ReactElement {
  const inputId = id ?? `input-${label.toLowerCase().replace(/\s/g, '-')}`;

  return (
    <div className="form-field">
      <label htmlFor={inputId}>{label}</label>
      <input id={inputId} aria-invalid={!!errorMessage} {...rest} />
      {errorMessage && <span className="error">{errorMessage}</span>}
      {helperText && !errorMessage && <span className="helper">{helperText}</span>}
    </div>
  );
}

// Creating reusable base prop types
type BaseButtonProps = ComponentPropsWithoutRef<'button'>;

interface IconButtonProps extends Omit<BaseButtonProps, 'children'> {
  icon: React.ReactNode;
  'aria-label': string; // Required for accessibility
}

function IconButton({ icon, ...rest }: IconButtonProps): React.ReactElement {
  return <button {...rest}>{icon}</button>;
}

// Generic rest props for polymorphic components
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

interface BoxOwnProps {
  padding?: 'sm' | 'md' | 'lg';
  margin?: 'sm' | 'md' | 'lg';
}

type BoxProps<C extends React.ElementType = 'div'> = PolymorphicComponentProps<C, BoxOwnProps>;

function Box<C extends React.ElementType = 'div'>({
  as,
  padding,
  margin,
  className,
  ...rest
}: BoxProps<C>): React.ReactElement {
  const Component = as ?? 'div';
  const classes = [
    className,
    padding && `p-${padding}`,
    margin && `m-${margin}`,
  ].filter(Boolean).join(' ');

  return <Component className={classes} {...rest} />;
}

// Usage with full type safety
<Box padding="md">Content in div</Box>
<Box as="section" padding="lg">Content in section</Box>
<Box as="a" href="/home" padding="sm">Link with padding</Box>

// Spreading with explicit rest type annotation
interface CardProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'title'> {
  title: React.ReactNode; // Override HTML title attribute
  subtitle?: string;
}

function Card({
  title,
  subtitle,
  children,
  ...divProps
}: CardProps): React.ReactElement {
  return (
    <div {...divProps}>
      <header>
        <h2>{title}</h2>
        {subtitle && <p>{subtitle}</p>}
      </header>
      <div>{children}</div>
    </div>
  );
}
```

**Benefits:**
- Proper typing catches invalid props at compile time
- IDE shows valid HTML attributes when using the component
- Omit removes props that conflict with custom props
- `ComponentPropsWithoutRef` properly excludes ref from spread
- Types clearly show which props are custom vs passed through

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
