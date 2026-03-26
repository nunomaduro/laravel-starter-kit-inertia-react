# TypeScript React Patterns - Complete Reference

**Version:** 2.0.0
**Date:** March 2026
**License:** MIT

## Abstract

Type-safe React with TypeScript. Contains 33 rules across 7 categories covering component typing, hooks, event handling, refs, generics, context, and utility types. Based on patterns from the TypeScript React Cheatsheet.

## References

- [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)
- [React + TypeScript Guide](https://react.dev/learn/typescript)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/handbook/)

---

# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Component Typing (comp)

**Impact:** CRITICAL
**Description:** Foundational patterns for typing React component props. Interface vs type for props, children typing with ReactNode, default props with destructuring, forwardRef, polymorphic "as" prop, FC vs function declaration, and rest props spreading.

## 2. Hook Typing (hook)

**Impact:** CRITICAL
**Description:** Type-safe React hooks. useState with generic types, useRef for DOM elements and mutable values, useReducer with discriminated union actions, useCallback/useMemo with typed parameters, useContext with null checking, and custom hooks with proper return types.

## 3. Event Handling (event)

**Impact:** HIGH
**Description:** Typing React event handlers correctly. FormEvent, ChangeEvent, MouseEvent, KeyboardEvent with proper HTML element generics, and event handler prop types.

## 4. Ref Typing (ref)

**Impact:** HIGH
**Description:** TypeScript patterns for React refs. useRef with specific HTMLElement types, callback refs for DOM measurement, and useImperativeHandle for exposing component methods to parents.

## 5. Generic Components (generic)

**Impact:** MEDIUM
**Description:** Building reusable, type-safe generic components. Generic list, select, and table components with type inference, and generic constraints using extends and keyof.

## 6. Context & State (ctx)

**Impact:** MEDIUM
**Description:** Typed React Context patterns. Creating context with null default and custom hook that throws on missing provider, and combining Context with useReducer using discriminated union actions.

## 7. Utility Types (util)

**Impact:** LOW
**Description:** TypeScript utility types for React. ComponentPropsWithoutRef for inheriting HTML attributes, Pick/Omit/Partial for deriving prop types, and discriminated unions for modeling state machines with exhaustive checking.


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


---

## Default Props Typing

**Impact: CRITICAL (avoids deprecated patterns and ensures type-aware defaults)**

Modern approaches to typing default props in React with TypeScript use destructuring defaults instead of the deprecated `defaultProps` static property.

## Incorrect

```tsx
// ❌ Bad
// Using deprecated defaultProps static property
interface ButtonProps {
  variant?: 'primary' | 'secondary';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
}

const Button: React.FC<ButtonProps> = ({ variant, size, disabled, children }) => {
  return (
    <button className={`btn-${variant} btn-${size}`} disabled={disabled}>
      {children}
    </button>
  );
};

// This pattern is deprecated and will be removed
Button.defaultProps = {
  variant: 'primary',
  size: 'md',
  disabled: false,
};

// Default values not reflected in type system
interface CardProps {
  elevation: number;
  rounded: boolean;
}

function Card({ elevation, rounded }: CardProps) {
  return <div style={{ boxShadow: `0 ${elevation}px ${elevation * 2}px rgba(0,0,0,0.1)` }} />;
}

Card.defaultProps = {
  elevation: 2,
  rounded: true,
};
```

**Problems:**
- `defaultProps` is deprecated for function components and will be removed
- TypeScript does not reflect `defaultProps` values in the type system
- Bundlers cannot tree-shake `defaultProps` effectively

## Correct

```tsx
// ✅ Good
// Using default parameters in destructuring (preferred modern approach)
interface ButtonProps {
  variant?: 'primary' | 'secondary';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  children: React.ReactNode;
}

function Button({
  variant = 'primary',
  size = 'md',
  disabled = false,
  children,
}: ButtonProps): React.ReactElement {
  return (
    <button className={`btn-${variant} btn-${size}`} disabled={disabled}>
      {children}
    </button>
  );
}

// Extracting defaults for reuse and testing
interface CardProps {
  elevation?: number;
  rounded?: boolean;
  children: React.ReactNode;
}

const cardDefaults: Required<Pick<CardProps, 'elevation' | 'rounded'>> = {
  elevation: 2,
  rounded: true,
};

function Card({
  elevation = cardDefaults.elevation,
  rounded = cardDefaults.rounded,
  children,
}: CardProps): React.ReactElement {
  return (
    <div
      className={rounded ? 'rounded' : ''}
      style={{ boxShadow: `0 ${elevation}px ${elevation * 2}px rgba(0,0,0,0.1)` }}
    >
      {children}
    </div>
  );
}

// Complex default objects with proper typing
interface FormFieldProps {
  name: string;
  label?: string;
  validation?: {
    required?: boolean;
    minLength?: number;
    maxLength?: number;
    pattern?: RegExp;
  };
  styles?: {
    container?: React.CSSProperties;
    label?: React.CSSProperties;
    input?: React.CSSProperties;
  };
}

const defaultValidation: Required<NonNullable<FormFieldProps['validation']>> = {
  required: false,
  minLength: 0,
  maxLength: Infinity,
  pattern: /.*/,
};

const defaultStyles: Required<NonNullable<FormFieldProps['styles']>> = {
  container: {},
  label: {},
  input: {},
};

function FormField({
  name,
  label = name,
  validation = {},
  styles = {},
}: FormFieldProps): React.ReactElement {
  const mergedValidation = { ...defaultValidation, ...validation };
  const mergedStyles = { ...defaultStyles, ...styles };

  return (
    <div style={mergedStyles.container}>
      <label style={mergedStyles.label}>{label}</label>
      <input
        name={name}
        required={mergedValidation.required}
        minLength={mergedValidation.minLength}
        maxLength={mergedValidation.maxLength}
        pattern={mergedValidation.pattern.source}
        style={mergedStyles.input}
      />
    </div>
  );
}

// Using satisfies for type-safe defaults
interface ThemeProps {
  colors?: {
    primary?: string;
    secondary?: string;
    background?: string;
  };
  spacing?: {
    small?: number;
    medium?: number;
    large?: number;
  };
}

const themeDefaults = {
  colors: {
    primary: '#007bff',
    secondary: '#6c757d',
    background: '#ffffff',
  },
  spacing: {
    small: 8,
    medium: 16,
    large: 24,
  },
} satisfies Required<{
  colors: Required<NonNullable<ThemeProps['colors']>>;
  spacing: Required<NonNullable<ThemeProps['spacing']>>;
}>;

type Theme = {
  colors: Required<NonNullable<ThemeProps['colors']>>;
  spacing: Required<NonNullable<ThemeProps['spacing']>>;
};

// import { createContext } from 'react'
const defaultTheme: Theme = themeDefaults;
const ThemeContext = createContext<Theme>(defaultTheme);

function ThemeProvider({
  colors = themeDefaults.colors,
  spacing = themeDefaults.spacing,
  children,
}: ThemeProps & { children: React.ReactNode }): React.ReactElement {
  const theme = {
    colors: { ...themeDefaults.colors, ...colors },
    spacing: { ...themeDefaults.spacing, ...spacing },
  };

  return (
    <ThemeContext.Provider value={theme}>
      {children}
    </ThemeContext.Provider>
  );
}
```

**Benefits:**
- Default parameters are type-aware and TypeScript understands the values inside the function
- No deprecation concerns since `defaultProps` is deprecated for function components
- Better tree-shaking by bundlers
- Explicit defaults make component behavior clear when reading the code
- Extracted default objects can be imported in tests

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## ForwardRef Typing

**Impact: CRITICAL (ensures ref types match actual DOM elements)**

Properly typing components that forward refs to child elements. Mismatched ref types cause runtime errors or require unsafe type assertions.

## Incorrect

```tsx
// ❌ Bad
// Missing ref type annotation
const Input = React.forwardRef((props, ref) => {
  return <input ref={ref} {...props} />;
});

// Incorrect ref type
interface ButtonProps {
  variant: 'primary' | 'secondary';
}

const Button = React.forwardRef<HTMLDivElement, ButtonProps>((props, ref) => {
  // Ref type doesn't match the actual element
  return <button ref={ref as any}>{props.variant}</button>;
});

// Not exposing ref at all for imperative handle
const ComplexInput = React.forwardRef((props: InputProps, ref) => {
  const inputRef = React.useRef<HTMLInputElement>(null);

  // ref is ignored, parent can't access input
  return <input ref={inputRef} {...props} />;
});
```

**Problems:**
- Missing type annotations result in `any` types for ref and props
- Mismatched ref element types require unsafe `as any` casts
- Ignoring the forwarded ref makes the component unusable by parent components

## Correct

```tsx
// ✅ Good
import React, { forwardRef, useRef, useImperativeHandle } from 'react';

// Basic forwardRef with proper typing
interface InputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'size'> {
  label: string;
  error?: string;
  size?: 'sm' | 'md' | 'lg';
}

const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, size = 'md', className, ...rest }, ref) => {
    return (
      <div className={`input-wrapper input-${size}`}>
        <label>{label}</label>
        <input
          ref={ref}
          className={`input ${error ? 'input-error' : ''} ${className ?? ''}`}
          aria-invalid={!!error}
          {...rest}
        />
        {error && <span className="error-message">{error}</span>}
      </div>
    );
  }
);

Input.displayName = 'Input';

// ForwardRef with useImperativeHandle for custom methods
interface FormInputHandle {
  focus: () => void;
  clear: () => void;
  getValue: () => string;
  validate: () => boolean;
}

interface FormInputProps {
  name: string;
  label: string;
  required?: boolean;
  pattern?: RegExp;
}

const FormInput = forwardRef<FormInputHandle, FormInputProps>(
  ({ name, label, required = false, pattern }, ref) => {
    const inputRef = useRef<HTMLInputElement>(null);
    const [error, setError] = React.useState<string | null>(null);

    useImperativeHandle(ref, () => ({
      focus: () => {
        inputRef.current?.focus();
      },
      clear: () => {
        if (inputRef.current) {
          inputRef.current.value = '';
          setError(null);
        }
      },
      getValue: () => {
        return inputRef.current?.value ?? '';
      },
      validate: () => {
        const value = inputRef.current?.value ?? '';

        if (required && !value) {
          setError('This field is required');
          return false;
        }

        if (pattern && !pattern.test(value)) {
          setError('Invalid format');
          return false;
        }

        setError(null);
        return true;
      },
    }));

    return (
      <div className="form-input">
        <label htmlFor={name}>{label}</label>
        <input ref={inputRef} id={name} name={name} aria-invalid={!!error} />
        {error && <span className="error">{error}</span>}
      </div>
    );
  }
);

FormInput.displayName = 'FormInput';

// Generic forwardRef component
interface SelectOption<T> {
  value: T;
  label: string;
}

interface SelectProps<T> {
  options: SelectOption<T>[];
  value?: T;
  onChange: (value: T) => void;
  placeholder?: string;
}

type GenericForwardRefComponent = <T>(
  props: SelectProps<T> & { ref?: React.ForwardedRef<HTMLSelectElement> }
) => React.ReactElement;

const Select: GenericForwardRefComponent = forwardRef(
  <T,>(
    { options, value, onChange, placeholder }: SelectProps<T>,
    ref: React.ForwardedRef<HTMLSelectElement>
  ) => {
    return (
      <select
        ref={ref}
        value={String(value)}
        onChange={(e) => {
          const selected = options.find((opt) => String(opt.value) === e.target.value);
          if (selected) onChange(selected.value);
        }}
      >
        {placeholder && <option value="">{placeholder}</option>}
        {options.map((option) => (
          <option key={String(option.value)} value={String(option.value)}>
            {option.label}
          </option>
        ))}
      </select>
    );
  }
) as GenericForwardRefComponent;
```

**Benefits:**
- Proper typing ensures ref type matches the actual DOM element
- `useImperativeHandle` enables custom methods with full type safety
- Setting displayName improves React DevTools debugging
- Special patterns enable generic forwardRef components
- Parent components can imperatively control children safely

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


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


---

## FC vs Function Declaration

**Impact: CRITICAL (ensures consistent and future-proof component typing)**

Choosing between `React.FC` and regular function declarations for component typing. Regular function declarations are preferred over `React.FC` for clarity and generic support.

## Incorrect

```tsx
// ❌ Bad
import React, { FC } from 'react';

interface ButtonProps {
  label: string;
  onClick: () => void;
}

// FC used to include children implicitly (React 17 and earlier)
// In React 18+, FC no longer includes children automatically
const Button: FC<ButtonProps> = ({ label, onClick }) => {
  return <button onClick={onClick}>{label}</button>;
};

// FC makes it harder to use generics
const List: FC<{ items: string[] }> = ({ items }) => (
  <ul>
    {items.map((item) => (
      <li key={item}>{item}</li>
    ))}
  </ul>
);
```

**Problems:**
- `React.FC` had implicit children in React 17, causing confusion across versions
- `React.FC` makes generic components awkward to type
- `React.FC` had issues with `defaultProps` typing
- Inconsistent community conventions between React 17 and 18

## Correct

```tsx
// ✅ Good
// Using regular function declarations with explicit return types
interface ButtonProps {
  label: string;
  onClick: () => void;
}

function Button({ label, onClick }: ButtonProps): React.ReactElement {
  return <button onClick={onClick}>{label}</button>;
}

// Arrow function with explicit typing
const Card = ({ title, children }: {
  title: string;
  children: React.ReactNode;
}): React.ReactElement => {
  return (
    <div className="card">
      <h2>{title}</h2>
      {children}
    </div>
  );
};

// Generic components are cleaner without FC
interface ListProps<T> {
  items: T[];
  renderItem: (item: T) => React.ReactNode;
}

function List<T>({ items, renderItem }: ListProps<T>): React.ReactElement {
  return <ul>{items.map(renderItem)}</ul>;
}

// Named function export for better debugging
interface User { id: string; name: string; email: string }

export function UserProfile({ user }: { user: User }): React.ReactElement {
  return <div>{user.name}</div>;
}
```

**Benefits:**
- Explicit over implicit: regular functions require explicit children prop declaration
- Generic support: regular functions work better with TypeScript generics
- Consistency: avoids confusion about React 17 vs 18 FC behavior
- Better debugging: named function declarations appear with their names in DevTools
- Industry trend: the React and TypeScript communities have moved away from FC

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Display Name Pattern

**Impact: CRITICAL (improves debugging and DevTools component identification)**

Setting displayName for better debugging and DevTools integration. Components without displayName show as "Anonymous" in React DevTools, making debugging difficult.

## Incorrect

```tsx
// ❌ Bad
// Anonymous arrow function - shows as "Anonymous" in DevTools
export const Button = ({ children }: { children: React.ReactNode }) => {
  return <button>{children}</button>;
};

// HOC without displayName - hard to identify wrapped component
function withLogger<P extends object>(Component: React.ComponentType<P>) {
  return (props: P) => {
    console.log('Rendering:', props);
    return <Component {...props} />;
  };
}

// Memo without displayName
const ExpensiveList = React.memo(({ items }: { items: string[] }) => {
  return (
    <ul>
      {items.map((item) => (
        <li key={item}>{item}</li>
      ))}
    </ul>
  );
});

// ForwardRef without displayName
const Input = React.forwardRef<HTMLInputElement, React.InputHTMLAttributes<HTMLInputElement>>(
  (props, ref) => <input ref={ref} {...props} />
);

// Context without displayName
const ThemeContext = React.createContext<'light' | 'dark'>('light');
```

**Problems:**
- Anonymous components appear as "Anonymous" in React DevTools
- HOCs without displayName hide the wrapped component identity
- Error stack traces show meaningless component names
- React Profiler cannot label components for performance analysis

## Correct

```tsx
// ✅ Good
import React, { forwardRef, memo, createContext } from 'react';

// Named function export - automatically has displayName
export function Button({ children }: { children: React.ReactNode }): React.ReactElement {
  return <button>{children}</button>;
}

// Arrow function with explicit displayName
export const IconButton = ({ icon, label }: { icon: React.ReactNode; label: string }): React.ReactElement => {
  return (
    <button aria-label={label}>
      {icon}
    </button>
  );
};
IconButton.displayName = 'IconButton';

// HOC with proper displayName
function withLogger<P extends object>(
  WrappedComponent: React.ComponentType<P>
) {
  const displayName = WrappedComponent.displayName ?? WrappedComponent.name ?? 'Component';

  function WithLogger(props: P) {
    console.log(`Rendering ${displayName}:`, props);
    return <WrappedComponent {...props} />;
  }

  WithLogger.displayName = `withLogger(${displayName})`;

  return WithLogger;
}

const LoggedButton = withLogger(Button);

// Memo with displayName
interface ListProps {
  items: string[];
  onItemClick?: (item: string) => void;
}

const ExpensiveList = memo<ListProps>(({ items, onItemClick }) => {
  return (
    <ul>
      {items.map((item) => (
        <li key={item} onClick={() => onItemClick?.(item)}>
          {item}
        </li>
      ))}
    </ul>
  );
});
ExpensiveList.displayName = 'ExpensiveList';

// ForwardRef with displayName
interface InputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'size'> {
  size?: 'sm' | 'md' | 'lg';
  error?: string;
}

const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ size = 'md', error, className, ...rest }, ref) => {
    return (
      <div className="input-wrapper">
        <input
          ref={ref}
          className={`input input-${size} ${error ? 'input-error' : ''} ${className ?? ''}`}
          aria-invalid={!!error}
          {...rest}
        />
        {error && <span className="error">{error}</span>}
      </div>
    );
  }
);
Input.displayName = 'Input';

// Context with displayName
interface ThemeContextValue {
  theme: 'light' | 'dark';
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);
ThemeContext.displayName = 'ThemeContext';

// Helper function for creating displayName
function getDisplayName<P>(Component: React.ComponentType<P>): string {
  return Component.displayName ?? Component.name ?? 'Component';
}
```

**Benefits:**
- Components appear with readable names in React DevTools
- Stack traces show meaningful component names
- Wrapped components show their hierarchy (e.g., "withLogger(Button)")
- React Profiler shows component names for performance analysis
- Component names appear in test output and snapshots

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


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


---

## useState Hook Typing

**Impact: CRITICAL (prevents type errors when initial values do not represent all possible states)**

useState infers types from initial values. When the initial value does not represent all possible states (like `null` for async data), explicit typing prevents runtime errors.

## Incorrect

```tsx
// ❌ Bad
// Inferred as null, can't set to User
const [user, setUser] = useState(null)
setUser({ id: 1, name: 'John' })  // Error!

// Inferred as never[] - can't add typed items
const [items, setItems] = useState([])
setItems([{ id: 1 }])  // Error!

// Inferred as string, can't set undefined
const [search, setSearch] = useState('')
setSearch(undefined)  // Error if you need undefined state
```

**Problems:**
- `useState(null)` infers type as `null` only, rejecting object values
- `useState([])` infers type as `never[]`, rejecting any array items
- Simple type inference cannot represent union states like `string | undefined`

## Correct

```tsx
// ✅ Good
// Nullable State
interface User {
  id: number
  name: string
  email: string
}

// Explicit union type for nullable state
const [user, setUser] = useState<User | null>(null)

// Now both work:
setUser({ id: 1, name: 'John', email: 'john@example.com' })
setUser(null)

// Access with null check
if (user) {
  console.log(user.name)  // TypeScript knows user is User here
}

// Array State
interface Todo {
  id: number
  text: string
  done: boolean
}

const [todos, setTodos] = useState<Todo[]>([])

// Add item
setTodos(prev => [...prev, { id: Date.now(), text: 'New', done: false }])

// Update item
setTodos(prev =>
  prev.map(todo =>
    todo.id === id ? { ...todo, done: !todo.done } : todo
  )
)

// Remove item
setTodos(prev => prev.filter(todo => todo.id !== id))

// Object State
interface FormData {
  name: string
  email: string
  age: number
}

const [form, setForm] = useState<FormData>({
  name: '',
  email: '',
  age: 0,
})

// Update single field
setForm(prev => ({ ...prev, name: 'John' }))

// Partial updates helper
const updateForm = <K extends keyof FormData>(
  field: K,
  value: FormData[K]
) => {
  setForm(prev => ({ ...prev, [field]: value }))
}

updateForm('name', 'John')
updateForm('age', 25)

// Union State (Discriminated union for state machines)
type RequestState<T> =
  | { status: 'idle' }
  | { status: 'loading' }
  | { status: 'success'; data: T }
  | { status: 'error'; error: Error }

const [state, setState] = useState<RequestState<User>>({ status: 'idle' })

// Usage
switch (state.status) {
  case 'idle':
    return <button onClick={fetch}>Load</button>
  case 'loading':
    return <Spinner />
  case 'success':
    return <UserCard user={state.data} />  // data is typed!
  case 'error':
    return <Error message={state.error.message} />
}

// Lazy Initialization
const [lazyState, setLazyState] = useState<ComplexState>(() => {
  return computeInitialState()
})

const [theme, setTheme] = useState<'light' | 'dark'>(() => {
  const saved = localStorage.getItem('theme')
  return (saved as 'light' | 'dark') || 'light'
})

// Undefined vs Null
// Use undefined for "not yet set"
const [selectedId, setSelectedId] = useState<number | undefined>(undefined)

// Use null for "explicitly empty"
// (same pattern as the nullable state example above)
const [userData, setUserData] = useState<User | null>(null)
```

**Benefits:**
- Explicit type annotations allow state to hold values beyond the initial type
- Discriminated unions enable exhaustive state machine patterns
- Lazy initialization runs expensive computations only once
- Clear conventions for `null` vs `undefined` improve code readability
- Generic type parameters work seamlessly with complex state shapes

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## useRef Hook Typing

**Impact: CRITICAL (prevents null reference errors and unnecessary null checks)**

useRef has two distinct use cases with different typing: DOM element refs (nullable) and mutable value storage (non-nullable). Using the wrong pattern causes type errors or requires unnecessary null checks.

## Incorrect

```tsx
// ❌ Bad
// Missing element type
const inputRef = useRef(null)
inputRef.current.focus()  // Error: possibly null

// Wrong initial value for DOM ref
const inputRef = useRef<HTMLInputElement>()  // undefined, not null
<input ref={inputRef} />  // Type error

// Treating mutable ref as nullable
const countRef = useRef<number>(0)
if (countRef.current !== null) {  // Unnecessary check
  countRef.current++
}
```

**Problems:**
- Missing element type generic means `current` has no useful properties
- Using `undefined` instead of `null` for DOM refs causes type incompatibility
- Unnecessary null checks on mutable refs add noise and reduce readability

## Correct

```tsx
// ✅ Good
// DOM Element Refs - pass null, type the element
// const inputRef = useRef<HTMLInputElement>(null)
// const buttonRef = useRef<HTMLButtonElement>(null)
// const divRef = useRef<HTMLDivElement>(null)

function Form() {
  const inputRef = useRef<HTMLInputElement>(null)

  const focusInput = () => {
    // Optional chaining because ref might not be attached yet
    inputRef.current?.focus()
  }

  useEffect(() => {
    inputRef.current?.focus()
  }, [])

  return <input ref={inputRef} />
}

// Common DOM Element Types (standalone declarations for reference)
// const inputRef = useRef<HTMLInputElement>(null)
// const textareaRef = useRef<HTMLTextAreaElement>(null)
// const selectRef = useRef<HTMLSelectElement>(null)
// const formRef = useRef<HTMLFormElement>(null)
// const divRef = useRef<HTMLDivElement>(null)
// const videoRef = useRef<HTMLVideoElement>(null)
// const canvasRef = useRef<HTMLCanvasElement>(null)
// const svgRef = useRef<SVGSVGElement>(null)

// Mutable Value Refs - pass actual initial value
function Timer() {
  const intervalRef = useRef<number | undefined>(undefined)
  const countRef = useRef(0)  // Inferred as MutableRefObject<number>

  useEffect(() => {
    intervalRef.current = window.setInterval(() => {
      countRef.current++  // No null check needed
    }, 1000)

    return () => {
      clearInterval(intervalRef.current)
    }
  }, [])
}

// Storing Previous Value
function usePrevious<T>(value: T): T | undefined {
  const ref = useRef<T | undefined>(undefined)

  useEffect(() => {
    ref.current = value
  }, [value])

  return ref.current
}

function Counter({ count }: { count: number }) {
  const prevCount = usePrevious(count)

  return (
    <p>
      Current: {count}, Previous: {prevCount ?? 'none'}
    </p>
  )
}

// Storing Callbacks
function useEventCallback<T extends (...args: never[]) => unknown>(fn: T): T {
  const ref = useRef<T>(fn)

  useEffect(() => {
    ref.current = fn
  }, [fn])

  return useCallback(
    ((...args) => ref.current(...args)) as T,
    []
  )
}

// Multiple Refs (Callback Refs)
interface Item { id: string; name: string }

function List({ items }: { items: Item[] }) {
  const itemRefs = useRef<Map<string, HTMLLIElement>>(new Map())

  const setRef = (id: string) => (el: HTMLLIElement | null) => {
    if (el) {
      itemRefs.current.set(id, el)
    } else {
      itemRefs.current.delete(id)
    }
  }

  const scrollToItem = (id: string) => {
    itemRefs.current.get(id)?.scrollIntoView()
  }

  return (
    <ul>
      {items.map(item => (
        <li key={item.id} ref={setRef(item.id)}>
          {item.name}
        </li>
      ))}
    </ul>
  )
}
```

**Benefits:**
- DOM refs with `null` initial value create `RefObject<T>` with nullable current
- Mutable refs with actual initial value create `MutableRefObject<T>` with non-nullable current
- Optional chaining on DOM refs handles the "not yet attached" state cleanly
- Callback refs enable dynamic ref management for lists of elements
- Ref-stored callbacks avoid stale closure issues without triggering re-renders

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## useReducer Typing

**Impact: CRITICAL (enables exhaustive action handling and type-safe state transitions)**

Properly typing the useReducer hook with actions, state, and discriminated unions. Discriminated union action types enable exhaustive switch statements and catch missing cases at compile time.

## Incorrect

```tsx
// ❌ Bad
// Untyped reducer - no type safety
const reducer = (state, action) => {
  switch (action.type) {
    case 'increment':
      return { count: state.count + 1 };
    default:
      return state;
  }
};

// Action type as string - no autocomplete or validation
interface Action {
  type: string;
  payload?: any;
}

// Missing exhaustiveness checking
function badReducer(state: State, action: Action): State {
  switch (action.type) {
    case 'add':
      return { ...state, items: [...state.items, action.payload] };
    // Forgot to handle 'remove' action - no error!
  }
  return state;
}
```

**Problems:**
- Untyped reducers have implicit `any` for state and action
- String-typed action types provide no autocomplete or validation
- Without exhaustiveness checking, missing action cases are silently ignored
- `any` payload types allow invalid data to pass through

## Correct

```tsx
// ✅ Good
import { useReducer, Reducer } from 'react';

// Define state interface
interface CounterState {
  count: number;
  step: number;
}

// Define action types using discriminated union
type CounterAction =
  | { type: 'increment' }
  | { type: 'decrement' }
  | { type: 'reset' }
  | { type: 'setStep'; payload: number }
  | { type: 'incrementBy'; payload: number };

const initialState: CounterState = {
  count: 0,
  step: 1,
};

// Typed reducer with exhaustiveness checking
function counterReducer(state: CounterState, action: CounterAction): CounterState {
  switch (action.type) {
    case 'increment':
      return { ...state, count: state.count + state.step };
    case 'decrement':
      return { ...state, count: state.count - state.step };
    case 'reset':
      return initialState;
    case 'setStep':
      return { ...state, step: action.payload };
    case 'incrementBy':
      return { ...state, count: state.count + action.payload };
    default:
      // Exhaustiveness check - will error if a case is missing
      const _exhaustive: never = action;
      return state;
  }
}

// Usage in component
function Counter() {
  const [state, dispatch] = useReducer(counterReducer, initialState);

  return (
    <div>
      <p>Count: {state.count}</p>
      <button onClick={() => dispatch({ type: 'increment' })}>+</button>
      <button onClick={() => dispatch({ type: 'decrement' })}>-</button>
      <button onClick={() => dispatch({ type: 'reset' })}>Reset</button>
      <button onClick={() => dispatch({ type: 'incrementBy', payload: 10 })}>+10</button>
    </div>
  );
}

// Complex state with nested objects
interface TodoState {
  todos: Todo[];
  filter: 'all' | 'active' | 'completed';
  isLoading: boolean;
  error: string | null;
}

interface Todo {
  id: string;
  text: string;
  completed: boolean;
}

type TodoAction =
  | { type: 'ADD_TODO'; payload: { text: string } }
  | { type: 'TOGGLE_TODO'; payload: { id: string } }
  | { type: 'DELETE_TODO'; payload: { id: string } }
  | { type: 'SET_FILTER'; payload: TodoState['filter'] }
  | { type: 'FETCH_START' }
  | { type: 'FETCH_SUCCESS'; payload: Todo[] }
  | { type: 'FETCH_ERROR'; payload: string };

function todoReducer(state: TodoState, action: TodoAction): TodoState {
  switch (action.type) {
    case 'ADD_TODO':
      return {
        ...state,
        todos: [
          ...state.todos,
          {
            id: crypto.randomUUID(),
            text: action.payload.text,
            completed: false,
          },
        ],
      };

    case 'TOGGLE_TODO':
      return {
        ...state,
        todos: state.todos.map((todo) =>
          todo.id === action.payload.id
            ? { ...todo, completed: !todo.completed }
            : todo
        ),
      };

    case 'DELETE_TODO':
      return {
        ...state,
        todos: state.todos.filter((todo) => todo.id !== action.payload.id),
      };

    case 'SET_FILTER':
      return { ...state, filter: action.payload };

    case 'FETCH_START':
      return { ...state, isLoading: true, error: null };

    case 'FETCH_SUCCESS':
      return { ...state, isLoading: false, todos: action.payload };

    case 'FETCH_ERROR':
      return { ...state, isLoading: false, error: action.payload };

    default:
      const _exhaustive: never = action;
      return state;
  }
}

// Lazy initialization with typed init function
interface FormState {
  values: Record<string, string>;
  touched: Record<string, boolean>;
  errors: Record<string, string>;
}

type FormAction =
  | { type: 'SET_VALUE'; field: string; value: string }
  | { type: 'SET_TOUCHED'; field: string }
  | { type: 'SET_ERROR'; field: string; error: string }
  | { type: 'RESET' };

function createInitialState(fields: string[]): FormState {
  return {
    values: Object.fromEntries(fields.map((f) => [f, ''])),
    touched: Object.fromEntries(fields.map((f) => [f, false])),
    errors: {},
  };
}

function formReducer(state: FormState, action: FormAction): FormState {
  switch (action.type) {
    case 'SET_VALUE':
      return {
        ...state,
        values: { ...state.values, [action.field]: action.value },
      };
    case 'SET_TOUCHED':
      return {
        ...state,
        touched: { ...state.touched, [action.field]: true },
      };
    case 'SET_ERROR':
      return {
        ...state,
        errors: { ...state.errors, [action.field]: action.error },
      };
    case 'RESET':
      return createInitialState(Object.keys(state.values));
    default:
      const _exhaustive: never = action;
      return state;
  }
}

// Usage with lazy init
function Form({ fields }: { fields: string[] }) {
  const [state, dispatch] = useReducer(formReducer, fields, createInitialState);

  // dispatch is fully typed
  dispatch({ type: 'SET_VALUE', field: 'email', value: 'test@example.com' });

  return <form>{/* form fields */}</form>;
}

// Action creators for better DX
const todoActions = {
  addTodo: (text: string): TodoAction => ({ type: 'ADD_TODO', payload: { text } }),
  toggleTodo: (id: string): TodoAction => ({ type: 'TOGGLE_TODO', payload: { id } }),
  deleteTodo: (id: string): TodoAction => ({ type: 'DELETE_TODO', payload: { id } }),
  setFilter: (filter: TodoState['filter']): TodoAction => ({ type: 'SET_FILTER', payload: filter }),
};

// Usage with action creators
dispatch(todoActions.addTodo('Buy groceries'));
dispatch(todoActions.toggleTodo('123'));
```

**Benefits:**
- Discriminated unions enable exhaustive switch statements
- Compile-time errors when action cases are missing
- Each action has its own strongly-typed payload
- Type system helps ensure proper immutable state updates
- Lazy initialization third argument properly types the init function
- Action creator factory functions provide better developer experience

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## useCallback Typing

**Impact: CRITICAL (ensures memoized callbacks have correct parameter and return types)**

Properly typing useCallback for memoized function references. Explicit parameter types enable autocomplete and catch errors, while proper dependencies prevent stale closures.

## Incorrect

```tsx
// ❌ Bad
// Missing dependency array type inference
const handleClick = useCallback((id) => {
  console.log(id); // id is implicitly 'any'
}, []);

// Incorrect return type inference
const fetchData = useCallback(async () => {
  const data = await api.getData();
  return data; // Return type not enforced
});

// Dependencies not aligned with closure usage
const [count, setCount] = useState(0);
const increment = useCallback(() => {
  setCount(count + 1); // Stale closure - count not in deps
}, []); // Missing count dependency

// Using 'any' for event parameter
const handleChange = useCallback((e: any) => {
  setValue(e.target.value);
}, []);
```

**Problems:**
- Missing parameter types result in implicit `any` with no autocomplete
- Missing or incorrect dependencies cause stale closure bugs
- Using `any` for event parameters removes all type checking
- Return types are not enforced without explicit annotation

## Correct

```tsx
// ✅ Good
import { useCallback, useState } from 'react';

// Basic callback with explicit parameter types
const handleClick = useCallback((id: string) => {
  console.log('Clicked:', id);
}, []);

// Callback with event typing
const handleInputChange = useCallback(
  (event: React.ChangeEvent<HTMLInputElement>) => {
    const value = event.target.value;
    console.log('Input changed:', value);
  },
  []
);

// Callback with return type
interface CartItem { id: string; name: string; price: number; quantity: number }

const calculateTotal = useCallback(
  (items: CartItem[]): number => {
    return items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  },
  []
);

// Async callback with proper typing
interface User {
  id: string;
  name: string;
  email: string;
}

const fetchUser = useCallback(
  async (userId: string): Promise<User> => {
    const response = await fetch(`/api/users/${userId}`);
    if (!response.ok) {
      throw new Error('Failed to fetch user');
    }
    return response.json();
  },
  []
);

// Callback using state with proper dependencies
function Counter() {
  const [count, setCount] = useState(0);
  const [step, setStep] = useState(1);

  // Use functional update to avoid stale closure
  const increment = useCallback(() => {
    setCount((prevCount) => prevCount + step);
  }, [step]); // Only depends on step, count uses functional update

  // When you need the current value in the callback
  const logAndIncrement = useCallback(() => {
    console.log('Current count:', count);
    setCount((prevCount) => prevCount + 1);
  }, [count]); // count needed for console.log

  return (
    <div>
      <p>Count: {count}</p>
      <button onClick={increment}>+{step}</button>
    </div>
  );
}

// Callback passed to child with proper typing
interface Item { id: string; name: string }

interface ItemListProps {
  items: Item[];
  onItemSelect: (item: Item) => void;
  onItemDelete: (id: string) => Promise<void>;
}

function ItemContainer() {
  const [items, setItems] = useState<Item[]>([]);

  const handleSelect = useCallback((item: Item) => {
    console.log('Selected:', item.name);
  }, []);

  const handleDelete = useCallback(async (id: string): Promise<void> => {
    // assume api is injected or imported
    await api.deleteItem(id);
    setItems((prev) => prev.filter((item) => item.id !== id));
  }, []);

  return (
    <ItemList
      items={items}
      onItemSelect={handleSelect}
      onItemDelete={handleDelete}
    />
  );
}

// Callback with multiple parameters
interface FormData {
  name: string;
  email: string;
  message: string;
}

type FormField = keyof FormData;

function FormComponent() {
  const [formData, setFormData] = useState<Record<string, string>>({});

  const handleFieldChange = useCallback(
    (field: FormField, value: string) => {
      setFormData((prev) => ({ ...prev, [field]: value }));
    },
    []
  );
}

// Memoized callback for optimized child renders
const MemoizedChild = React.memo<{ onClick: () => void }>(({ onClick }) => {
  console.log('Child rendered');
  return <button onClick={onClick}>Click me</button>;
});

function Parent() {
  const [count, setCount] = useState(0);

  // Without useCallback, MemoizedChild would re-render on every Parent render
  const handleClick = useCallback(() => {
    setCount((c) => c + 1);
  }, []);

  return (
    <div>
      <p>Count: {count}</p>
      <MemoizedChild onClick={handleClick} />
    </div>
  );
}
```

**Benefits:**
- Explicit parameter types enable autocomplete and catch errors
- Declared return types ensure callbacks return expected values
- Functional updates avoid stale closures without adding state to deps
- Proper React event types for form and DOM events
- Stable references prevent unnecessary child re-renders with React.memo

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## useMemo Typing

**Impact: CRITICAL (ensures memoized values have correct types and proper dependencies)**

Properly typing useMemo for memoized computed values. Explicit type annotations make code more readable, catch errors, and ensure correct dependency arrays.

## Incorrect

```tsx
// ❌ Bad
// Missing type annotation - relies entirely on inference
const expensiveResult = useMemo(() => {
  return someExpensiveCalculation(data);
}, [data]);

// Using 'any' loses type safety
const config = useMemo<any>(() => ({
  theme: 'dark',
  features: ['a', 'b'],
}), []);

// Unnecessary useMemo for simple values
const double = useMemo(() => count * 2, [count]); // Simple math doesn't need memoization

// Missing dependencies causes stale values
const filteredItems = useMemo(() => {
  return items.filter(item => item.category === selectedCategory);
}, []); // Missing items and selectedCategory
```

**Problems:**
- Using `any` removes type safety on the memoized value
- Missing dependencies cause stale cached values
- Simple calculations do not benefit from memoization overhead
- Unclear return types when conditionally returning different shapes

## Correct

```tsx
// ✅ Good
import { useMemo, useState, createContext } from 'react';

// Basic useMemo with explicit type
interface ProcessedData {
  total: number;
  average: number;
  max: number;
  min: number;
}

const statistics = useMemo<ProcessedData>(() => {
  const total = numbers.reduce((sum, n) => sum + n, 0);
  return {
    total,
    average: total / numbers.length,
    max: Math.max(...numbers),
    min: Math.min(...numbers),
  };
}, [numbers]);

// Filtering and sorting with proper typing
interface Product {
  id: string;
  name: string;
  price: number;
  category: string;
  inStock: boolean;
}

type SortField = 'name' | 'price';
type SortDirection = 'asc' | 'desc';

function ProductList({ products }: { products: Product[] }) {
  const [searchTerm, setSearchTerm] = useState('');
  const [category, setCategory] = useState<string | null>(null);
  const [sortField, setSortField] = useState<SortField>('name');
  const [sortDirection, setSortDirection] = useState<SortDirection>('asc');

  const filteredAndSortedProducts = useMemo<Product[]>(() => {
    let result = products;

    if (searchTerm) {
      const term = searchTerm.toLowerCase();
      result = result.filter((p) =>
        p.name.toLowerCase().includes(term)
      );
    }

    if (category) {
      result = result.filter((p) => p.category === category);
    }

    result = [...result].sort((a, b) => {
      const aVal = a[sortField];
      const bVal = b[sortField];
      const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
      return sortDirection === 'asc' ? comparison : -comparison;
    });

    return result;
  }, [products, searchTerm, category, sortField, sortDirection]);

  return (
    <ul>
      {filteredAndSortedProducts.map((product) => (
        <li key={product.id}>{product.name} - ${product.price}</li>
      ))}
    </ul>
  );
}

// Memoizing derived state for context
interface ThemeColors {
  primary: string;
  secondary: string;
  background: string;
  text: string;
  border: string;
}

interface ThemeContextValue {
  theme: 'light' | 'dark';
  colors: ThemeColors;
}

const ThemeContext = createContext<ThemeContextValue>(null!);

function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<'light' | 'dark'>('light');

  const colors = useMemo<ThemeColors>(() => {
    if (theme === 'light') {
      return {
        primary: '#007bff',
        secondary: '#6c757d',
        background: '#ffffff',
        text: '#212529',
        border: '#dee2e6',
      };
    }
    return {
      primary: '#6ea8fe',
      secondary: '#adb5bd',
      background: '#212529',
      text: '#ffffff',
      border: '#495057',
    };
  }, [theme]);

  const contextValue = useMemo<ThemeContextValue>(
    () => ({ theme, colors }),
    [theme, colors]
  );

  return (
    <ThemeContext.Provider value={contextValue}>
      {children}
    </ThemeContext.Provider>
  );
}

// Memoizing expensive transformations
interface RawDataPoint {
  timestamp: string;
  value: number;
  metadata: Record<string, unknown>;
}

interface ChartDataPoint {
  x: Date;
  y: number;
  label: string;
}

function DataVisualization({ rawData }: { rawData: RawDataPoint[] }) {
  const chartData = useMemo<ChartDataPoint[]>(() => {
    return rawData.map((point) => ({
      x: new Date(point.timestamp),
      y: point.value,
      label: `Value: ${point.value}`,
    }));
  }, [rawData]);

  const aggregatedData = useMemo(() => {
    const byMonth = new Map<string, number[]>();

    chartData.forEach((point) => {
      const monthKey = `${point.x.getFullYear()}-${point.x.getMonth()}`;
      const existing = byMonth.get(monthKey) ?? [];
      byMonth.set(monthKey, [...existing, point.y]);
    });

    return Array.from(byMonth.entries()).map(([month, values]) => ({
      month,
      average: values.reduce((a, b) => a + b, 0) / values.length,
      count: values.length,
    }));
  }, [chartData]);

  return <Chart data={chartData} aggregated={aggregatedData} />;
}

// Generic memoized selector pattern
interface Order { id: string; total: number }
function useMemoizedSelector<TState, TSelected>(
  state: TState,
  selector: (state: TState) => TSelected
): TSelected {
  return useMemo(() => selector(state), [state, selector]);
}

// Usage
interface User {
  id: string;
  name: string;
  email: string;
  isActive: boolean;
}

interface AppState {
  users: User[];
  products: Product[];
  orders: Order[];
}

function UserList({ state }: { state: AppState }) {
  const activeUsers = useMemoizedSelector(
    state,
    (s) => s.users.filter((u) => u.isActive)
  );

  return <ul>{activeUsers.map((u) => <li key={u.id}>{u.name}</li>)}</ul>;
}
```

**Benefits:**
- Explicit types make code more readable and catch type mismatches
- Correct dependencies ensure memoized values stay up to date
- Only expensive calculations benefit from memoization
- Reference stability prevents unnecessary child re-renders
- Derived state avoids storing redundant data
- Memoized context values prevent provider-triggered re-renders

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## useContext Typing

**Impact: CRITICAL (prevents silent failures from undefined context access)**

Properly typing Context and useContext for type-safe global state. Using null defaults with custom guard hooks prevents silent failures when context is used outside its provider.

## Incorrect

```tsx
// ❌ Bad
// Untyped context - no type safety
const AppContext = React.createContext(undefined);

// Using 'any' for context value
const ThemeContext = React.createContext<any>({ theme: 'light' });

// Default value that doesn't match actual usage
interface User {
  id: string;
  name: string;
}

const UserContext = React.createContext<User>({
  id: '',
  name: '',
}); // Fake default encourages using context outside provider

// Not handling missing provider
const AuthContext = React.createContext<AuthContextValue | undefined>(undefined);

function useAuth() {
  const context = React.useContext(AuthContext);
  return context; // Could be undefined, but callers don't know
}
```

**Problems:**
- Untyped context defaults to `any` with no type checking
- Using `any` removes autocomplete and error detection
- Fake default values mask missing providers and create misleading behavior
- Returning potentially undefined context without a guard leaves callers unprotected

## Correct

```tsx
// ✅ Good
import React, { createContext, useContext, useState, useCallback, useMemo } from 'react';

// Pattern 1: Context with null default and custom hook
interface User {
  id: string;
  name: string;
  email: string;
}

interface AuthContextValue {
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextValue | null>(null);
AuthContext.displayName = 'AuthContext';

function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);

  if (context === null) {
    throw new Error('useAuth must be used within an AuthProvider');
  }

  return context;
}

// assume: const api = createApiClient()
function AuthProvider({ children }: { children: React.ReactNode }): React.ReactElement {
  const [user, setUser] = useState<User | null>(null);

  const login = useCallback(async (email: string, password: string) => {
    const response = await api.login(email, password);
    setUser(response.user);
  }, []);

  const logout = useCallback(() => {
    setUser(null);
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isAuthenticated: user !== null,
      login,
      logout,
    }),
    [user, login, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

// Usage in component
function UserProfile(): React.ReactElement {
  const { user, logout } = useAuth(); // Guaranteed to be non-null

  if (!user) {
    return <div>Please log in</div>;
  }

  return (
    <div>
      <h1>{user.name}</h1>
      <p>{user.email}</p>
      <button onClick={logout}>Logout</button>
    </div>
  );
}

// Pattern 2: Multiple contexts for separation of concerns
interface ThemeContextValue {
  theme: 'light' | 'dark';
  colors: {
    primary: string;
    background: string;
    text: string;
  };
}

interface ThemeActionsContextValue {
  setTheme: (theme: 'light' | 'dark') => void;
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);
const ThemeActionsContext = createContext<ThemeActionsContextValue | null>(null);

function useTheme(): ThemeContextValue {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within ThemeProvider');
  }
  return context;
}

function useThemeActions(): ThemeActionsContextValue {
  const context = useContext(ThemeActionsContext);
  if (!context) {
    throw new Error('useThemeActions must be used within ThemeProvider');
  }
  return context;
}

// Pattern 3: Generic context factory
function createSafeContext<T>(displayName: string) {
  const Context = createContext<T | null>(null);
  Context.displayName = displayName;

  function useContextSafe(): T {
    const context = useContext(Context);
    if (context === null) {
      throw new Error(`use${displayName} must be used within ${displayName}Provider`);
    }
    return context;
  }

  return [Context.Provider, useContextSafe] as const;
}

// Usage of factory
interface AppNotification { id: string; message: string; type: 'info' | 'error' | 'success' }

interface NotificationContextValue {
  notifications: AppNotification[];
  addNotification: (notification: Omit<AppNotification, 'id'>) => void;
  removeNotification: (id: string) => void;
}

const [NotificationProvider, useNotifications] = createSafeContext<NotificationContextValue>('Notification');

// Pattern 4: Context with reducer
interface CartItem {
  id: string;
  name: string;
  price: number;
  quantity: number;
}

type CartAction =
  | { type: 'ADD_ITEM'; payload: Omit<CartItem, 'quantity'> }
  | { type: 'REMOVE_ITEM'; payload: string }
  | { type: 'UPDATE_QUANTITY'; payload: { id: string; quantity: number } }
  | { type: 'CLEAR' };

interface CartState {
  items: CartItem[];
  total: number;
}

interface CartContextValue {
  state: CartState;
  dispatch: React.Dispatch<CartAction>;
  itemCount: number;
  addItem: (item: Omit<CartItem, 'quantity'>) => void;
  removeItem: (id: string) => void;
}

const CartContext = createContext<CartContextValue | null>(null);

function useCart(): CartContextValue {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within CartProvider');
  }
  return context;
}
```

**Benefits:**
- Null default with error boundary prevents silent failures
- Custom hooks encapsulate context access and error handling
- Separated contexts split state and actions to prevent unnecessary re-renders
- Factory pattern reduces boilerplate for creating typed contexts
- Memoized provider values prevent unnecessary re-renders
- DisplayName improves debugging in React DevTools

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Custom Hooks Typing

**Impact: CRITICAL (ensures clear hook contracts with proper return types and parameters)**

Creating properly typed custom hooks with clear return types and parameters. Explicit typing makes hook contracts clear to consumers and catches errors early.

## Incorrect

```tsx
// ❌ Bad
// No return type - callers don't know what to expect
function useUser(id) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLoading(true);
    fetchUser(id).then(setUser).finally(() => setLoading(false));
  }, [id]);

  return { user, loading };
}

// Using 'any' for state
function useLocalStorage(key: string, initialValue: any) {
  const [value, setValue] = useState(initialValue);
  return [value, setValue];
}

// Inconsistent return type (object vs tuple)
function useToggle(initial: boolean) {
  const [value, setValue] = useState(initial);
  const toggle = () => setValue(v => !v);
  return { value, toggle, setValue };
}

// Missing cleanup and error handling
function useEventListener(event: string, handler: any) {
  useEffect(() => {
    window.addEventListener(event, handler);
  }, [event, handler]);
}
```

**Problems:**
- Missing parameter types result in implicit `any`
- No return type annotation makes the hook contract unclear
- Using `any` for state removes all type safety
- Missing effect cleanup causes memory leaks
- Missing error handling in async hooks leads to uncaught errors

## Correct

```tsx
// ✅ Good
import { useState, useEffect, useCallback, useRef, useMemo } from 'react';

// Hook with clear parameter and return types
interface User {
  id: string;
  name: string;
  email: string;
}

interface UseUserResult {
  user: User | null;
  loading: boolean;
  error: Error | null;
  refetch: () => Promise<void>;
}

function useUser(userId: string | null): UseUserResult {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Error | null>(null);

  const fetchUserData = useCallback(async () => {
    if (!userId) {
      setUser(null);
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch(`/api/users/${userId}`);
      if (!response.ok) {
        throw new Error('Failed to fetch user');
      }
      const data: User = await response.json();
      setUser(data);
    } catch (err) {
      setError(err instanceof Error ? err : new Error('Unknown error'));
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    fetchUserData();
  }, [fetchUserData]);

  return { user, loading, error, refetch: fetchUserData };
}

// Generic hook with type parameter
function useLocalStorage<T>(
  key: string,
  initialValue: T
): [T, (value: T | ((prev: T) => T)) => void, () => void] {
  const [storedValue, setStoredValue] = useState<T>(() => {
    try {
      const item = localStorage.getItem(key);
      return item ? (JSON.parse(item) as T) : initialValue;
    } catch {
      return initialValue;
    }
  });

  const setValue = useCallback(
    (value: T | ((prev: T) => T)) => {
      setStoredValue((prev) => {
        const valueToStore = value instanceof Function ? value(prev) : value;
        localStorage.setItem(key, JSON.stringify(valueToStore));
        return valueToStore;
      });
    },
    [key]
  );

  const removeValue = useCallback(() => {
    localStorage.removeItem(key);
    setStoredValue(initialValue);
  }, [key, initialValue]);

  return [storedValue, setValue, removeValue];
}

// Usage with explicit type
const [theme, setTheme] = useLocalStorage<'light' | 'dark'>('theme', 'light');

// Tuple return type for toggle hook
function useToggle(
  initialValue: boolean = false
): [boolean, () => void, (value: boolean) => void] {
  const [value, setValue] = useState(initialValue);

  const toggle = useCallback(() => {
    setValue((v) => !v);
  }, []);

  const setValueDirectly = useCallback((newValue: boolean) => {
    setValue(newValue);
  }, []);

  return [value, toggle, setValueDirectly];
}

// Event listener hook with proper typing
function useEventListener<K extends keyof WindowEventMap>(
  eventName: K,
  handler: (event: WindowEventMap[K]) => void,
  element: Window | null = window,
  options?: AddEventListenerOptions
): void {
  const savedHandler = useRef(handler);

  useEffect(() => {
    savedHandler.current = handler;
  }, [handler]);

  useEffect(() => {
    if (!element) return;

    const eventListener = (event: WindowEventMap[K]) => {
      savedHandler.current(event);
    };

    element.addEventListener(eventName, eventListener, options);

    return () => {
      element.removeEventListener(eventName, eventListener, options);
    };
  }, [eventName, element, options]);
}

// Async hook with proper state machine
type AsyncState<T> =
  | { status: 'idle'; data: null; error: null }
  | { status: 'loading'; data: null; error: null }
  | { status: 'success'; data: T; error: null }
  | { status: 'error'; data: null; error: Error };

interface UseAsyncResult<T> {
  state: AsyncState<T>;
  execute: () => Promise<T | null>;
  reset: () => void;
}

function useAsync<T>(
  asyncFunction: () => Promise<T>,
  immediate: boolean = true
): UseAsyncResult<T> {
  const [state, setState] = useState<AsyncState<T>>({
    status: 'idle',
    data: null,
    error: null,
  });

  const execute = useCallback(async (): Promise<T | null> => {
    setState({ status: 'loading', data: null, error: null });

    try {
      const data = await asyncFunction();
      setState({ status: 'success', data, error: null });
      return data;
    } catch (error) {
      const err = error instanceof Error ? error : new Error('Unknown error');
      setState({ status: 'error', data: null, error: err });
      return null;
    }
  }, [asyncFunction]);

  const reset = useCallback(() => {
    setState({ status: 'idle', data: null, error: null });
  }, []);

  useEffect(() => {
    if (immediate) {
      execute();
    }
  }, [execute, immediate]);

  return { state, execute, reset };
}

// Hook returning readonly tuple (const assertion)
function useCounter(initialValue: number = 0) {
  const [count, setCount] = useState(initialValue);

  const increment = useCallback(() => setCount((c) => c + 1), []);
  const decrement = useCallback(() => setCount((c) => c - 1), []);
  const reset = useCallback(() => setCount(initialValue), [initialValue]);

  return [count, { increment, decrement, reset }] as const;
}

// Usage - tuple is readonly [number, { increment, decrement, reset }]
const [count, { increment, decrement, reset }] = useCounter(0);
```

**Benefits:**
- Explicit return types make hook contracts clear to consumers
- Generic parameters enable type-safe reuse across different data types
- Tuple returns for simple hooks, objects for complex ones
- Proper cleanup in effects prevents memory leaks
- Error states in async hooks prevent uncaught runtime errors
- Ref patterns store values that should not trigger re-renders

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Generic Hooks Typing

**Impact: CRITICAL (enables flexible, reusable hooks without losing type safety)**

Creating flexible, reusable hooks with TypeScript generics. Well-designed generics preserve type information throughout the hook without requiring explicit type arguments.

## Incorrect

```tsx
// ❌ Bad
// Using 'any' instead of generics
function useFetch(url: string): { data: any; loading: boolean } {
  const [data, setData] = useState<any>(null);
  // ...
  return { data, loading };
}

// Not constraining generic types when needed
function useList<T>(initial: T[]) {
  const [items, setItems] = useState(initial);

  const add = (item: T) => setItems([...items, item]);
  const remove = (item: T) => setItems(items.filter(i => i === item)); // Needs ID

  return { items, add, remove };
}

// Overly complex generics that are hard to understand
function useStore<
  T extends Record<string, unknown>,
  K extends keyof T,
  V extends T[K],
  A extends { type: string; payload: V }
>(initial: T): [T, (action: A) => void] {
  // Too many type parameters
}
```

**Problems:**
- Using `any` removes all type information from hook return values
- Unconstrained generics allow operations that require specific shapes (like `id`)
- Too many type parameters make hooks difficult to use and understand

## Correct

```tsx
// ✅ Good
import { useState, useCallback, useEffect, useRef, useMemo } from 'react';

// Basic generic hook for data fetching
interface FetchState<T> {
  data: T | null;
  loading: boolean;
  error: Error | null;
}

interface UseFetchOptions<T> {
  initialData?: T;
  onSuccess?: (data: T) => void;
  onError?: (error: Error) => void;
  transform?: (raw: unknown) => T;
}

function useFetch<T>(
  url: string | null,
  options: UseFetchOptions<T> = {}
): FetchState<T> & { refetch: () => Promise<void> } {
  const { initialData = null, onSuccess, onError, transform } = options;

  const [state, setState] = useState<FetchState<T>>({
    data: initialData,
    loading: !!url,
    error: null,
  });

  const fetchData = useCallback(async () => {
    if (!url) {
      setState({ data: initialData, loading: false, error: null });
      return;
    }

    setState((prev) => ({ ...prev, loading: true, error: null }));

    try {
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const raw = await response.json();
      const data = transform ? transform(raw) : (raw as T);

      setState({ data, loading: false, error: null });
      onSuccess?.(data);
    } catch (err) {
      const error = err instanceof Error ? err : new Error('Fetch failed');
      setState({ data: null, loading: false, error });
      onError?.(error);
    }
  }, [url, initialData, transform, onSuccess, onError]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  return { ...state, refetch: fetchData };
}

// Usage with type inference
interface User {
  id: string;
  name: string;
  email: string;
}

const { data: user, loading, error } = useFetch<User>('/api/user/1');
// user is User | null

// Generic list management hook with proper constraints
interface Identifiable {
  id: string | number;
}

interface UseListActions<T> {
  add: (item: T) => void;
  remove: (id: T extends Identifiable ? T['id'] : number) => void;
  update: (id: T extends Identifiable ? T['id'] : number, updates: Partial<T>) => void;
  clear: () => void;
  set: (items: T[]) => void;
}

function useList<T extends Identifiable>(
  initialItems: T[] = []
): [T[], UseListActions<T>] {
  const [items, setItems] = useState<T[]>(initialItems);

  const actions: UseListActions<T> = useMemo(
    () => ({
      add: (item: T) => {
        setItems((prev) => [...prev, item]);
      },
      remove: (id) => {
        setItems((prev) => prev.filter((item) => item.id !== id));
      },
      update: (id, updates) => {
        setItems((prev) =>
          prev.map((item) => (item.id === id ? { ...item, ...updates } : item))
        );
      },
      clear: () => {
        setItems([]);
      },
      set: (newItems) => {
        setItems(newItems);
      },
    }),
    []
  );

  return [items, actions];
}

// Usage
interface Todo {
  id: string;
  text: string;
  completed: boolean;
}

const [todos, { add, remove, update }] = useList<Todo>([]);
add({ id: '1', text: 'Learn TypeScript', completed: false });
update('1', { completed: true });
remove('1');

// Generic form hook
type ValidationRule<T> = (value: T) => string | null;

interface UseFormOptions<T extends Record<string, unknown>> {
  initialValues: T;
  validationRules?: Partial<{ [K in keyof T]: ValidationRule<T[K]> }>;
  onSubmit: (values: T) => void | Promise<void>;
}

interface UseFormResult<T extends Record<string, unknown>> {
  values: T;
  errors: Partial<Record<keyof T, string>>;
  touched: Partial<Record<keyof T, boolean>>;
  isSubmitting: boolean;
  isValid: boolean;
  handleChange: <K extends keyof T>(field: K, value: T[K]) => void;
  handleBlur: <K extends keyof T>(field: K) => void;
  handleSubmit: (e?: React.FormEvent) => Promise<void>;
  reset: () => void;
  setFieldValue: <K extends keyof T>(field: K, value: T[K]) => void;
  setFieldError: <K extends keyof T>(field: K, error: string) => void;
}

function useForm<T extends Record<string, unknown>>({
  initialValues,
  validationRules = {},
  onSubmit,
}: UseFormOptions<T>): UseFormResult<T> {
  const [values, setValues] = useState<T>(initialValues);
  const [errors, setErrors] = useState<Partial<Record<keyof T, string>>>({});
  const [touched, setTouched] = useState<Partial<Record<keyof T, boolean>>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  const validateField = useCallback(
    <K extends keyof T>(field: K, value: T[K]): string | null => {
      const rule = validationRules[field];
      return rule ? rule(value) : null;
    },
    [validationRules]
  );

  const handleChange = useCallback(
    <K extends keyof T>(field: K, value: T[K]) => {
      setValues((prev) => ({ ...prev, [field]: value }));

      if (touched[field]) {
        const error = validateField(field, value);
        setErrors((prev) => ({ ...prev, [field]: error ?? undefined }));
      }
    },
    [touched, validateField]
  );

  const handleBlur = useCallback(
    <K extends keyof T>(field: K) => {
      setTouched((prev) => ({ ...prev, [field]: true }));

      const error = validateField(field, values[field]);
      setErrors((prev) => ({ ...prev, [field]: error ?? undefined }));
    },
    [values, validateField]
  );

  const validateAll = useCallback((): boolean => {
    const newErrors: Partial<Record<keyof T, string>> = {};
    let isValid = true;

    for (const key of Object.keys(values) as Array<keyof T>) {
      const error = validateField(key, values[key]);
      if (error) {
        newErrors[key] = error;
        isValid = false;
      }
    }

    setErrors(newErrors);
    return isValid;
  }, [values, validateField]);

  const handleSubmit = useCallback(
    async (e?: React.FormEvent) => {
      e?.preventDefault();

      if (!validateAll()) return;

      setIsSubmitting(true);
      try {
        await onSubmit(values);
      } finally {
        setIsSubmitting(false);
      }
    },
    [values, validateAll, onSubmit]
  );

  const reset = useCallback(() => {
    setValues(initialValues);
    setErrors({});
    setTouched({});
  }, [initialValues]);

  const setFieldValue = useCallback(<K extends keyof T>(field: K, value: T[K]) => {
    setValues((prev) => ({ ...prev, [field]: value }));
  }, []);

  const setFieldError = useCallback(<K extends keyof T>(field: K, error: string) => {
    setErrors((prev) => ({ ...prev, [field]: error }));
  }, []);

  const isValid = Object.keys(errors).length === 0;

  return {
    values,
    errors,
    touched,
    isSubmitting,
    isValid,
    handleChange,
    handleBlur,
    handleSubmit,
    reset,
    setFieldValue,
    setFieldError,
  };
}

// Generic selection hook
function useSelection<T extends Identifiable>() {
  const [selectedIds, setSelectedIds] = useState<Set<T['id']>>(new Set());

  const select = useCallback((id: T['id']) => {
    setSelectedIds((prev) => new Set(prev).add(id));
  }, []);

  const deselect = useCallback((id: T['id']) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      next.delete(id);
      return next;
    });
  }, []);

  const toggle = useCallback((id: T['id']) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  }, []);

  const isSelected = useCallback(
    (id: T['id']) => selectedIds.has(id),
    [selectedIds]
  );

  return {
    selectedIds: Array.from(selectedIds),
    select,
    deselect,
    toggle,
    isSelected,
    selectedCount: selectedIds.size,
  };
}
```

**Benefits:**
- Type safety without `any`: generics preserve type information throughout the hook
- Constraints using `extends` limit generic types to specific shapes
- Well-designed generics often do not need explicit type arguments
- Generic hooks work with any compatible type for maximum reusability
- Clear contracts: generic return types communicate what hooks provide

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Event Handler Types

**Impact: HIGH (provides autocomplete for event properties and catches errors at compile time)**

Event handlers in React use synthetic events with specific types. Using the correct types provides autocomplete for event properties and catches errors at compile time.

## Incorrect

```tsx
// ❌ Bad
// Using native Event instead of React event
const handleClick = (e: Event) => {
  // Missing React-specific properties
}

// Missing element type
const handleChange = (e: React.ChangeEvent) => {
  e.target.value  // Error: Property 'value' does not exist
}

// Wrong event type
const handleSubmit = (e: React.MouseEvent) => {  // Should be FormEvent
  e.preventDefault()
}
```

**Problems:**
- Native `Event` type lacks React synthetic event properties
- Missing element type generic means `target` properties are unknown
- Wrong event type does not match the actual DOM event being handled

## Correct

```tsx
// ✅ Good
// Mouse Events
const handleClick = (e: React.MouseEvent<HTMLButtonElement>) => {
  console.log(e.clientX, e.clientY)
  console.log(e.currentTarget.disabled)  // Button properties
}

const handleDivClick = (e: React.MouseEvent<HTMLDivElement>) => {
  console.log(e.currentTarget.className)
}

<button onClick={handleClick}>Click</button>
<div onClick={handleDivClick}>Click</div>

// Form Events
const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
  e.preventDefault()
  const formData = new FormData(e.currentTarget)
}

const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
  console.log(e.target.value)
  console.log(e.target.type)
  console.log(e.target.checked)  // For checkboxes
}

const handleSelectChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
  console.log(e.target.value)
  console.log(e.target.selectedOptions)
}

const handleTextareaChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
  console.log(e.target.value)
}

// Keyboard Events
const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
  if (e.key === 'Enter') {
    e.preventDefault()
  }
  if (e.key === 'Escape') {
    // close
  }
  console.log(e.ctrlKey, e.shiftKey, e.altKey)
}

<input onKeyDown={handleKeyDown} />

// Focus Events
const handleFocus = (e: React.FocusEvent<HTMLInputElement>) => {
  console.log('Focused:', e.target.name)
}

const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
  console.log('Blurred:', e.target.value)
}

<input onFocus={handleFocus} onBlur={handleBlur} />

// Drag Events
const handleDragStart = (e: React.DragEvent<HTMLDivElement>) => {
  e.dataTransfer.setData('text/plain', 'data')
}

const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
  e.preventDefault()
  const data = e.dataTransfer.getData('text/plain')
}

// Inline vs Defined Handlers
// Inline - types inferred automatically
<button onClick={(e) => {
  // e is React.MouseEvent<HTMLButtonElement>
  console.log(e.clientX)
}}>
  Click
</button>

// Defined separately - must type explicitly
const handleButtonClick = (e: React.MouseEvent<HTMLButtonElement>) => {
  console.log(e.clientX)
}
<button onClick={handleButtonClick}>Click</button>

// Typing event handler props
interface ButtonProps {
  onClick?: React.MouseEventHandler<HTMLButtonElement>
  onFocus?: React.FocusEventHandler<HTMLButtonElement>
}
```

**Benefits:**
- Full autocomplete for event-specific properties like `clientX`, `key`, `target.value`
- Compile-time errors when accessing properties that do not exist on the event type
- Proper element type matching ensures `currentTarget` has the correct properties
- `React.MouseEventHandler<T>` and similar aliases simplify prop typing

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Click Event Handler Typing

**Impact: HIGH (catches wrong element types and event property access at compile time)**

Properly typing click event handlers for various React elements. Using the correct event and element types provides autocomplete for event properties and catches errors.

## Incorrect

```tsx
// ❌ Bad
// Using 'any' for event parameter
const handleClick = (e: any) => {
  console.log(e.target.value);
};

// Missing event type entirely
const onClick = (e) => {
  e.preventDefault();
};

// Using wrong event type
const handleButtonClick = (e: React.MouseEvent<HTMLDivElement>) => {
  // Should be HTMLButtonElement for button clicks
};

// Not handling synthetic events properly
const handleLinkClick = (e: MouseEvent) => {
  // Should be React.MouseEvent, not DOM MouseEvent
  e.preventDefault();
};
```

**Problems:**
- Using `any` removes all type checking on event properties
- Missing type annotations result in implicit `any`
- Wrong element type generic means `currentTarget` has wrong properties
- Native `MouseEvent` lacks React synthetic event methods

## Correct

```tsx
// ✅ Good
import React, { useCallback } from 'react';

// Button click with proper typing
const handleButtonClick = (event: React.MouseEvent<HTMLButtonElement>) => {
  console.log('Button clicked');
  console.log('Button text:', event.currentTarget.textContent);
};

// Div/container click
const handleContainerClick = (event: React.MouseEvent<HTMLDivElement>) => {
  event.stopPropagation();
  console.log('Container clicked at:', event.clientX, event.clientY);
};

// Link click with preventDefault
const handleLinkClick = (event: React.MouseEvent<HTMLAnchorElement>) => {
  event.preventDefault();
  const href = event.currentTarget.href;
  console.log('Navigating to:', href);
};

// Generic click handler for reusable components
type ClickHandler<T extends HTMLElement = HTMLElement> = (
  event: React.MouseEvent<T>
) => void;

// Click with data attribute
interface ItemProps {
  id: string;
  name: string;
  onClick: (id: string) => void;
}

function Item({ id, name, onClick }: ItemProps): React.ReactElement {
  const handleClick = useCallback(
    (event: React.MouseEvent<HTMLLIElement>) => {
      event.stopPropagation();
      onClick(id);
    },
    [id, onClick]
  );

  return <li onClick={handleClick}>{name}</li>;
}

// Click handler factory for list items
function createClickHandler<T extends { id: string }>(
  item: T,
  callback: (item: T) => void
): React.MouseEventHandler<HTMLElement> {
  return (event) => {
    event.stopPropagation();
    callback(item);
  };
}

// Component with optional click handler
interface CardProps {
  title: string;
  children: React.ReactNode;
  onClick?: React.MouseEventHandler<HTMLDivElement>;
  isClickable?: boolean;
}

function Card({
  title,
  children,
  onClick,
  isClickable = !!onClick,
}: CardProps): React.ReactElement {
  return (
    <div
      className={`card ${isClickable ? 'clickable' : ''}`}
      onClick={onClick}
      role={isClickable ? 'button' : undefined}
      tabIndex={isClickable ? 0 : undefined}
    >
      <h2>{title}</h2>
      {children}
    </div>
  );
}

// Accessible click handler (keyboard support)
function AccessibleButton({
  onClick,
  children,
}: {
  onClick: () => void;
  children: React.ReactNode;
}): React.ReactElement {
  const handleClick = (event: React.MouseEvent<HTMLDivElement>) => {
    onClick();
  };

  const handleKeyDown = (event: React.KeyboardEvent<HTMLDivElement>) => {
    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      onClick();
    }
  };

  return (
    <div
      role="button"
      tabIndex={0}
      onClick={handleClick}
      onKeyDown={handleKeyDown}
    >
      {children}
    </div>
  );
}

// Using MouseEventHandler type alias
type ButtonClickHandler = React.MouseEventHandler<HTMLButtonElement>;

const myButtonHandler: ButtonClickHandler = (event) => {
  console.log('Button:', event.currentTarget.name);
};
```

**Benefits:**
- Proper event types catch errors like accessing wrong properties
- Event type should match the actual HTML element
- `React.MouseEvent` provides synthetic event methods unlike native `MouseEvent`
- `currentTarget` is typed while `target` needs assertion
- `React.MouseEventHandler<T>` simplifies function signatures

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Typing Form Events

**Impact: HIGH (prevents runtime errors from untyped form data access)**

Form events require specific types for each input element. Using `any` or omitting types loses all safety around `e.target.value` and form data access.

## Incorrect

```tsx
// ❌ Bad
function LoginForm() {
  const [email, setEmail] = useState("");
  const [role, setRole] = useState("");

  // Untyped event — no safety on e.target
  const handleChange = (e) => {
    setEmail(e.target.value);
  };

  // Using 'any' — defeats TypeScript
  const handleSubmit = (e: any) => {
    e.preventDefault();
    console.log(e.target.email.value);
  };

  // Wrong event type for select
  const handleSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    setRole(e.target.value); // Should be HTMLSelectElement
  };

  return (
    <form onSubmit={handleSubmit}>
      <input onChange={handleChange} />
      <select onChange={handleSelect}>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Login</button>
    </form>
  );
}
```

**Problems:**
- `any` and untyped handlers provide no autocomplete or type checking
- Wrong element types (e.g., `HTMLInputElement` for a `<select>`) cause silent mismatches
- `e.target` is typed as `EventTarget`, not the specific element, without proper generics

## Correct

```tsx
// ✅ Good
function LoginForm() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState("");

  const handleEmailChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setEmail(e.target.value); // e.target is HTMLInputElement
  };

  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setPassword(e.target.value);
  };

  const handleRoleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setRole(e.target.value); // e.target is HTMLSelectElement
  };

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const data = {
      email: formData.get("email") as string,
      password: formData.get("password") as string,
      role: formData.get("role") as string,
    };
    console.log("Submitting:", data);
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        name="email"
        type="email"
        value={email}
        onChange={handleEmailChange}
      />
      <input
        name="password"
        type="password"
        value={password}
        onChange={handlePasswordChange}
      />
      <select name="role" value={role} onChange={handleRoleChange}>
        <option value="">Select role</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
      <textarea
        name="notes"
        onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => {
          console.log(e.target.value); // e.target is HTMLTextAreaElement
        }}
      />
      <button type="submit">Login</button>
    </form>
  );
}
```

**Benefits:**
- Each input element gets its correct event type (`HTMLInputElement`, `HTMLSelectElement`, `HTMLTextAreaElement`)
- `e.target` properties are fully typed with autocomplete
- `React.FormEvent<HTMLFormElement>` gives typed access to `e.currentTarget` for `FormData`
- Compile-time errors if you access properties that don't exist on the element

Reference: [React TypeScript Cheatsheet — Forms and Events](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/forms_and_events)


---

## Typing Keyboard Events

**Impact: HIGH (catches invalid key checks and missing modifier handling at compile time)**

Keyboard events need specific element types and proper use of `e.key`, `e.code`, and modifier properties. Using `any` loses all safety.

## Incorrect

```tsx
// ❌ Bad
function SearchInput() {
  // Using 'any' — no type safety on key properties
  const handleKeyDown = (e: any) => {
    if (e.key === "Enter") {
      e.target.blur();
    }
  };

  // Using native DOM KeyboardEvent instead of React's
  const handleKeyUp = (e: KeyboardEvent) => {
    console.log(e.key); // Wrong type — should be React.KeyboardEvent
  };

  // Untyped handler — e is implicitly 'any'
  const handleKeyPress = (e) => {
    if (e.keyCode === 13) {
      // keyCode is deprecated
      console.log("Enter pressed");
    }
  };

  return <input onKeyDown={handleKeyDown} onKeyUp={handleKeyUp} />;
}
```

**Problems:**
- `any` prevents autocomplete for `key`, `code`, `metaKey`, `ctrlKey`, etc.
- Native `KeyboardEvent` is not the same as `React.KeyboardEvent` and causes type errors
- `keyCode` is deprecated; `key` and `code` are the modern standard
- No element type means `e.currentTarget` is untyped

## Correct

```tsx
// ✅ Good
function SearchInput({ onSearch }: { onSearch: (query: string) => void }) {
  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    // e.key is the logical key value ("Enter", "Escape", "a", etc.)
    if (e.key === "Enter") {
      e.preventDefault();
      onSearch(e.currentTarget.value); // currentTarget is HTMLInputElement
    }

    if (e.key === "Escape") {
      e.currentTarget.blur();
    }
  };

  return <input type="search" onKeyDown={handleKeyDown} placeholder="Search..." />;
}

// Keyboard shortcuts with modifier keys
function Editor({ onSave, onUndo }: { onSave: () => void; onUndo: () => void }) {
  const handleKeyDown = (e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    // e.metaKey = Cmd on Mac, e.ctrlKey = Ctrl on Windows/Linux
    const isModifier = e.metaKey || e.ctrlKey;

    if (isModifier && e.key === "s") {
      e.preventDefault(); // Prevent browser save dialog
      onSave();
    }

    if (isModifier && e.key === "z") {
      e.preventDefault();
      onUndo();
    }

    // e.code gives the physical key ("KeyS", "KeyZ", "Space")
    // Useful for keyboard shortcuts that should work regardless of layout
    if (e.code === "Space" && e.shiftKey) {
      e.preventDefault();
      console.log("Shift+Space pressed");
    }
  };

  return <textarea onKeyDown={handleKeyDown} />;
}

// Typed handler for non-input elements
function Modal({ onClose, children }: { onClose: () => void; children: React.ReactNode }) {
  const handleKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
    if (e.key === "Escape") {
      onClose();
    }
  };

  return (
    <div role="dialog" tabIndex={-1} onKeyDown={handleKeyDown}>
      {children}
    </div>
  );
}
```

**Benefits:**
- `React.KeyboardEvent<HTMLInputElement>` provides typed access to `key`, `code`, `metaKey`, `ctrlKey`, `shiftKey`, `altKey`
- `e.currentTarget` is properly typed as the specific HTML element
- Modern `e.key` and `e.code` instead of deprecated `keyCode`
- Modifier key checks enable proper cross-platform keyboard shortcuts

Reference: [React TypeScript Cheatsheet — Forms and Events](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/forms_and_events)


---

## useRef for DOM Elements

**Impact: HIGH (prevents null access crashes and enables precise DOM typing)**

When using `useRef` for DOM elements, pass `null` as the initial value and use the most specific HTML element type. This returns a `RefObject` with a read-only `.current` that is `null` until React attaches the element.

## Incorrect

```tsx
// ❌ Bad
function BadForm() {
  // Missing type parameter — .current is undefined, not null
  const inputRef = useRef();

  // Too generic — loses element-specific properties like .value, .focus()
  const anotherRef = useRef<HTMLElement>(null);

  // Missing null initial value — returns MutableRefObject, not RefObject
  const divRef = useRef<HTMLDivElement>();

  const handleClick = () => {
    // No null check — crashes if element isn't mounted
    console.log(inputRef.current.value);
    inputRef.current.focus();
  };

  return (
    <div ref={divRef}>
      <input ref={inputRef} />
      <button onClick={handleClick}>Focus</button>
    </div>
  );
}
```

**Problems:**
- `useRef()` without a type defaults to `undefined`, not `null` — won't match React's ref assignment
- `HTMLElement` is too generic and lacks element-specific properties like `value` on inputs
- Missing `null` initial value creates a `MutableRefObject` instead of `RefObject`
- No null check before accessing `.current` causes runtime crashes

## Correct

```tsx
// ✅ Good
function SearchForm() {
  // Specific element types — pass null to get RefObject
  const inputRef = useRef<HTMLInputElement>(null);
  const formRef = useRef<HTMLFormElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);

  useEffect(() => {
    // Always check for null before accessing .current
    if (inputRef.current) {
      inputRef.current.focus(); // .focus() is available on HTMLInputElement
    }
  }, []);

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (inputRef.current) {
      console.log(inputRef.current.value); // .value is typed
      inputRef.current.select(); // .select() is typed
    }
  };

  const drawCanvas = () => {
    if (canvasRef.current) {
      // .getContext() is available because we used HTMLCanvasElement
      const ctx = canvasRef.current.getContext("2d");
      if (ctx) {
        ctx.fillRect(0, 0, 100, 100);
      }
    }
  };

  return (
    <form ref={formRef} onSubmit={handleSubmit}>
      <input ref={inputRef} type="search" />
      <canvas ref={canvasRef} />
      <button type="submit">Search</button>
    </form>
  );
}

// Using a ref for a video element
function VideoPlayer({ src }: { src: string }) {
  const videoRef = useRef<HTMLVideoElement>(null);

  const play = () => videoRef.current?.play();
  const pause = () => videoRef.current?.pause();

  return (
    <div>
      <video ref={videoRef} src={src} />
      <button onClick={play}>Play</button>
      <button onClick={pause}>Pause</button>
    </div>
  );
}
```

**Benefits:**
- `useRef<HTMLInputElement>(null)` returns `RefObject<HTMLInputElement>` with read-only `.current`
- Element-specific methods and properties (`value`, `focus`, `select`, `getContext`) are fully typed
- Null checks prevent runtime crashes when the element isn't mounted
- Optional chaining (`ref.current?.method()`) provides concise null-safe access

Reference: [React TypeScript Cheatsheet — useRef](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/hooks#useref)


---

## Callback Ref Pattern

**Impact: HIGH (eliminates extra render cycles for DOM measurement)**

Callback refs fire the moment a DOM node is attached or detached. This avoids the extra render cycle caused by `useRef` + `useEffect` when you need to measure or interact with a DOM element immediately.

## Incorrect

```tsx
// ❌ Bad
function MeasuredBox() {
  const boxRef = useRef<HTMLDivElement>(null);
  const [height, setHeight] = useState(0);

  // Extra render cycle: component renders → ref attaches → effect runs → state updates → re-render
  useEffect(() => {
    if (boxRef.current) {
      setHeight(boxRef.current.getBoundingClientRect().height);
    }
  }, []);

  return (
    <div>
      <div ref={boxRef}>Content that needs measuring</div>
      <p>Height: {height}px</p>
    </div>
  );
}
```

**Problems:**
- `useEffect` runs after paint, causing a visible flash when height updates
- Requires an extra render cycle: mount, then measure, then re-render with measurement
- Does not react to the element being conditionally shown/hidden
- If the element mounts later (e.g., after data loads), the effect won't re-run

## Correct

```tsx
// ✅ Good
function MeasuredBox() {
  const [height, setHeight] = useState(0);

  // Callback ref — fires immediately when the DOM node attaches
  const measuredRef = useCallback((node: HTMLDivElement | null) => {
    if (node !== null) {
      setHeight(node.getBoundingClientRect().height);
    }
  }, []);

  return (
    <div>
      <div ref={measuredRef}>Content that needs measuring</div>
      <p>Height: {height}px</p>
    </div>
  );
}

// Callback ref for conditional elements
function Tooltip({ show, text }: { show: boolean; text: string }) {
  const [position, setPosition] = useState({ top: 0, left: 0 });

  const tooltipRef = useCallback((node: HTMLDivElement | null) => {
    if (node) {
      const rect = node.getBoundingClientRect();
      setPosition({
        top: rect.top - rect.height - 8,
        left: rect.left + rect.width / 2,
      });
    }
  }, []);

  if (!show) return null;

  // Callback ref fires each time this element mounts
  return (
    <div ref={tooltipRef} style={{ position: "fixed", ...position }}>
      {text}
    </div>
  );
}

// Callback ref with cleanup (React 19+)
function ResizeObserverBox() {
  const [width, setWidth] = useState(0);

  const observerRef = useCallback((node: HTMLDivElement | null) => {
    if (node) {
      const observer = new ResizeObserver(([entry]) => {
        setWidth(entry.contentRect.width);
      });
      observer.observe(node);

      // Return cleanup function (React 19+)
      return () => observer.disconnect();
    }
  }, []);

  return (
    <div ref={observerRef}>
      <p>Width: {width}px</p>
    </div>
  );
}
```

**Benefits:**
- No extra render cycle — measurement happens the instant the node attaches
- Automatically fires when conditionally rendered elements mount or unmount
- Works with elements that appear after async data loads
- Cleanup function support (React 19+) enables attaching observers cleanly

Reference: [React TypeScript Cheatsheet — useRef](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/hooks#useref)


---

## useImperativeHandle Typing

**Impact: HIGH (enables type-safe parent-to-child method calls via refs)**

`useImperativeHandle` lets a child component expose specific methods to a parent via a ref. Define a handle interface for the exposed methods and use `forwardRef` with the handle type to ensure full type safety.

## Incorrect

```tsx
// ❌ Bad
// No handle type — parent has no idea what methods are available
const Timer = forwardRef((props, ref) => {
  const [count, setCount] = useState(0);

  useImperativeHandle(ref, () => ({
    start() { /* ... */ },
    stop() { /* ... */ },
    reset() { /* ... */ },
  }));

  return <div>{count}</div>;
});

// Parent — ref.current is typed as unknown, no autocomplete
function Parent() {
  const timerRef = useRef(null);

  const handleClick = () => {
    // TypeScript doesn't know .start() exists
    timerRef.current?.start();
  };

  return <Timer ref={timerRef} />;
}
```

**Problems:**
- `ref` is untyped — parent gets no autocomplete or type checking on exposed methods
- Typos like `ref.current.stat()` won't be caught at compile time
- No contract between parent and child about what the ref exposes

## Correct

```tsx
// ✅ Good
// 1. Define the handle type — this is the ref's public API
interface TimerHandle {
  start: () => void;
  stop: () => void;
  reset: () => void;
  getCount: () => number;
}

interface TimerProps {
  interval?: number;
}

// 2. Use forwardRef<HandleType, PropsType>
const Timer = forwardRef<TimerHandle, TimerProps>(({ interval = 1000 }, ref) => {
  const [count, setCount] = useState(0);
  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  // 3. useImperativeHandle exposes only these methods
  useImperativeHandle(ref, () => ({
    start() {
      if (intervalRef.current) return;
      intervalRef.current = setInterval(() => {
        setCount((c) => c + 1);
      }, interval);
    },
    stop() {
      if (intervalRef.current) {
        clearInterval(intervalRef.current);
        intervalRef.current = null;
      }
    },
    reset() {
      setCount(0);
    },
    getCount() {
      return count;
    },
  }), [interval, count]);

  return <div>Count: {count}</div>;
});

Timer.displayName = "Timer";

// 4. Parent uses the handle type for full type safety
function Dashboard() {
  const timerRef = useRef<TimerHandle>(null);

  const handleStart = () => {
    timerRef.current?.start(); // Autocomplete shows start, stop, reset, getCount
  };

  const handleStop = () => {
    timerRef.current?.stop();
  };

  const handleReset = () => {
    timerRef.current?.reset();
  };

  const showCount = () => {
    const count = timerRef.current?.getCount();
    console.log("Current count:", count); // count is number | undefined
  };

  return (
    <div>
      <Timer ref={timerRef} interval={500} />
      <button onClick={handleStart}>Start</button>
      <button onClick={handleStop}>Stop</button>
      <button onClick={handleReset}>Reset</button>
      <button onClick={showCount}>Log Count</button>
    </div>
  );
}
```

**Benefits:**
- `TimerHandle` interface creates a clear contract for the ref's public API
- `forwardRef<TimerHandle, TimerProps>` ensures the ref type flows to the parent
- Parent gets full autocomplete and type checking on `timerRef.current`
- Only explicitly exposed methods are accessible — internal state stays encapsulated

Reference: [React TypeScript Cheatsheet — forwardRef/createRef](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/forward_and_create_ref)


---

## Generic List Component

**Impact: MEDIUM (enables reusable, type-safe list rendering for any data type)**

Generic components allow building reusable, type-safe components that work with any data type. The type flows through from the data to the render function, providing full type safety.

## Incorrect

```tsx
// ❌ Bad
// Using any - no type safety
interface ListProps {
  items: any[]
  renderItem: (item: any) => React.ReactNode
}

function List({ items, renderItem }: ListProps) {
  return <ul>{items.map(renderItem)}</ul>
}

// No autocomplete, no type checking
<List
  items={users}
  renderItem={(item) => <li>{item.nmae}</li>}  // Typo not caught!
/>
```

**Problems:**
- Using `any` removes all type checking on list items
- Typos in property names are not caught at compile time
- No autocomplete for item properties in the render callback

## Correct

```tsx
// ✅ Good
// Basic Generic List
interface ListProps<T> {
  items: T[]
  renderItem: (item: T, index: number) => React.ReactNode
  keyExtractor: (item: T) => string | number
}

function List<T>({ items, renderItem, keyExtractor }: ListProps<T>) {
  return (
    <ul>
      {items.map((item, index) => (
        <li key={keyExtractor(item)}>
          {renderItem(item, index)}
        </li>
      ))}
    </ul>
  )
}

// Usage - T is inferred from items
interface User {
  id: number
  name: string
  email: string
}

const users: User[] = [
  { id: 1, name: 'John', email: 'john@example.com' }
]

<List
  items={users}
  renderItem={(user) => (
    // user is typed as User
    <span>{user.name} - {user.email}</span>
  )}
  keyExtractor={(user) => user.id}
/>

// With Constraints
interface HasId {
  id: string | number
}

interface ConstrainedListProps<T extends HasId> {
  items: T[]
  renderItem: (item: T) => React.ReactNode
}

function ConstrainedList<T extends HasId>({ items, renderItem }: ConstrainedListProps<T>) {
  return (
    <ul>
      {items.map((item) => (
        <li key={item.id}>
          {renderItem(item)}
        </li>
      ))}
    </ul>
  )
}

// Generic Grid Component
interface GridProps<T> {
  items: T[]
  columns: number
  renderItem: (item: T) => React.ReactNode
  keyExtractor: (item: T) => string | number
  emptyState?: React.ReactNode
}

function Grid<T>({
  items,
  columns,
  renderItem,
  keyExtractor,
  emptyState = <p>No items</p>,
}: GridProps<T>) {
  if (items.length === 0) {
    return <>{emptyState}</>
  }

  return (
    <div
      className="grid gap-4"
      style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
    >
      {items.map((item) => (
        <div key={keyExtractor(item)}>
          {renderItem(item)}
        </div>
      ))}
    </div>
  )
}

// Generic Table Component
interface Column<T> {
  key: keyof T | string
  header: string
  render?: (item: T) => React.ReactNode
}

interface TableProps<T> {
  data: T[]
  columns: Column<T>[]
  keyExtractor: (item: T) => string | number
}

function Table<T>({ data, columns, keyExtractor }: TableProps<T>) {
  return (
    <table>
      <thead>
        <tr>
          {columns.map((col) => (
            <th key={String(col.key)}>{col.header}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((item) => (
          <tr key={keyExtractor(item)}>
            {columns.map((col) => (
              <td key={String(col.key)}>
                {col.render
                  ? col.render(item)
                  : String(item[col.key as keyof T] ?? '')}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  )
}

// Alternative syntax: arrow function generic
// const ListArrow = <T,>({ items, renderItem }: ListProps<T>) => (
//   // Note the comma after T - needed in TSX files
//   <ul>{items.map(renderItem)}</ul>
// )

// Alternative syntax: with constraint
// const ListConstrained = <T extends HasId>({ items }: { items: T[] }) => (
//   <ul>{items.map(item => <li key={item.id} />)}</ul>
// )
```

**Benefits:**
- Full type inference from items to render callbacks
- Typos in property names are caught at compile time
- IDE autocomplete works for item properties
- Generic constraints can enforce minimum type requirements
- Reusable across any data type without losing type safety

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Generic Select/Dropdown Component

**Impact: MEDIUM (keeps value and onChange type-safe across any data type)**

A generic select component lets the value type be inferred from the items array, so `onChange` always receives the correct type instead of forcing everything through `string`.

## Incorrect

```tsx
// ❌ Bad
interface SelectProps {
  items: { label: string; value: string }[];
  value: string;
  onChange: (value: string) => void;
}

function Select({ items, value, onChange }: SelectProps) {
  return (
    <select value={value} onChange={(e) => onChange(e.target.value)}>
      {items.map((item) => (
        <option key={item.value} value={item.value}>
          {item.label}
        </option>
      ))}
    </select>
  );
}

// Everything forced to string — loses type information
const statusOptions = [
  { label: "Active", value: "active" },
  { label: "Inactive", value: "inactive" },
];

// onChange receives string, but we wanted "active" | "inactive"
<Select items={statusOptions} value={status} onChange={(val) => setStatus(val)} />
```

**Problems:**
- `value: string` forces all select values to be strings, even when the domain type is narrower
- `onChange` returns `string`, requiring manual casting at every call site
- No connection between the items' value type and the onChange callback type
- Cannot use numeric or object values without converting to/from strings

## Correct

```tsx
// ✅ Good
interface SelectOption<T> {
  label: string;
  value: T;
}

interface SelectProps<T> {
  items: SelectOption<T>[];
  value: T;
  onChange: (value: T) => void;
  placeholder?: string;
}

function Select<T extends string | number>({
  items,
  value,
  onChange,
  placeholder,
}: SelectProps<T>) {
  return (
    <select
      value={String(value)}
      onChange={(e) => {
        const selected = items.find((item) => String(item.value) === e.target.value);
        if (selected) {
          onChange(selected.value); // Returns T, not string
        }
      }}
    >
      {placeholder && (
        <option value="" disabled>
          {placeholder}
        </option>
      )}
      {items.map((item) => (
        <option key={String(item.value)} value={String(item.value)}>
          {item.label}
        </option>
      ))}
    </select>
  );
}

// Usage — T is inferred from items

// String literal union
type Status = "active" | "inactive" | "pending";

const statusOptions: SelectOption<Status>[] = [
  { label: "Active", value: "active" },
  { label: "Inactive", value: "inactive" },
  { label: "Pending", value: "pending" },
];

function StatusFilter() {
  const [status, setStatus] = useState<Status>("active");

  // onChange receives Status, not string
  return <Select items={statusOptions} value={status} onChange={setStatus} />;
}

// Numeric values
const priorityOptions: SelectOption<number>[] = [
  { label: "Low", value: 1 },
  { label: "Medium", value: 2 },
  { label: "High", value: 3 },
];

function PriorityPicker() {
  const [priority, setPriority] = useState(1);

  // onChange receives number, not string
  return <Select items={priorityOptions} value={priority} onChange={setPriority} />;
}
```

**Benefits:**
- `T` is inferred from the items array, so `onChange` automatically returns the correct type
- String literal unions (`"active" | "inactive"`) are preserved through the component
- Numeric values work without string conversion at call sites
- Type mismatch between `items`, `value`, and `onChange` is caught at compile time

Reference: [React TypeScript Cheatsheet — Generic Components](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#generic-components)


---

## Generic Table Component

**Impact: MEDIUM (type-safe column accessors prevent runtime data access errors)**

A generic table component uses `keyof T` for column accessors, ensuring that column definitions can only reference properties that actually exist on the row data type.

## Incorrect

```tsx
// ❌ Bad
interface Column {
  key: string;
  header: string;
  render?: (value: any, row: any) => React.ReactNode;
}

interface TableProps {
  data: any[];
  columns: Column[];
}

function Table({ data, columns }: TableProps) {
  return (
    <table>
      <thead>
        <tr>
          {columns.map((col) => (
            <th key={col.key}>{col.header}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((row, i) => (
          <tr key={i}>
            {columns.map((col) => (
              <td key={col.key}>
                {col.render ? col.render(row[col.key], row) : row[col.key]}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Typo in key — not caught until runtime
<Table
  data={users}
  columns={[
    { key: "nmae", header: "Name" },  // Typo: should be "name"
    { key: "email", header: "Email" },
  ]}
/>
```

**Problems:**
- `key: string` accepts any string, including typos and non-existent properties
- `any[]` data means row properties have no autocomplete
- `render` callback receives `any`, so mistakes in the render function go uncaught
- No relationship between column keys and the actual data shape

## Correct

```tsx
// ✅ Good
interface Column<T> {
  key: keyof T;
  header: string;
  render?: (value: T[keyof T], row: T) => React.ReactNode;
}

interface TableProps<T> {
  data: T[];
  columns: Column<T>[];
  keyExtractor: (row: T) => string | number;
}

function Table<T>({ data, columns, keyExtractor }: TableProps<T>) {
  return (
    <table>
      <thead>
        <tr>
          {columns.map((col) => (
            <th key={String(col.key)}>{col.header}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((row) => (
          <tr key={keyExtractor(row)}>
            {columns.map((col) => (
              <td key={String(col.key)}>
                {col.render
                  ? col.render(row[col.key], row)
                  : String(row[col.key] ?? "")}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Usage
interface User {
  id: number;
  name: string;
  email: string;
  role: "admin" | "user";
}

const userColumns: Column<User>[] = [
  { key: "name", header: "Name" },
  { key: "email", header: "Email" },
  {
    key: "role",
    header: "Role",
    render: (value, row) => (
      // row is typed as User, value is User[keyof User]
      <span className={row.role === "admin" ? "font-bold" : ""}>
        {String(value)}
      </span>
    ),
  },
];

function UserTable({ users }: { users: User[] }) {
  return (
    <Table
      data={users}
      columns={userColumns}
      keyExtractor={(user) => user.id}
    />
  );
}

// Compile error — "nmae" is not keyof User
// const badColumn: Column<User> = { key: "nmae", header: "Name" };
```

**Benefits:**
- `key: keyof T` only accepts property names that exist on the data type
- Typos in column keys are caught at compile time, not runtime
- `render` callback receives the typed row, enabling safe property access
- Adding or removing fields from the data type immediately flags broken columns

Reference: [React TypeScript Cheatsheet — Generic Components](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#generic-components)


---

## Generic Constraints with extends

**Impact: MEDIUM (prevents invalid types from being passed to generic components and functions)**

Unconstrained generics accept anything, including types that lack the properties your component needs. Use `extends` to constrain what `T` can be, and `keyof` to constrain keys to valid property names.

## Incorrect

```tsx
// ❌ Bad
// Unconstrained — accepts string, number, null, anything
interface ListProps<T> {
  items: T[];
  renderItem: (item: T) => React.ReactNode;
}

function List<T>({ items, renderItem }: ListProps<T>) {
  return (
    <ul>
      {items.map((item, i) => (
        // Cannot use item.id as key — T might not have 'id'
        <li key={i}>{renderItem(item)}</li>
      ))}
    </ul>
  );
}

// Unconstrained key lookup — no guarantee key exists on obj
function getProperty<T>(obj: T, key: string): unknown {
  return (obj as any)[key]; // Forced to cast to any
}
```

**Problems:**
- `T` could be a primitive, so accessing `item.id` would crash
- `key: string` accepts any string, not just valid property names of `T`
- Forced to use `any` casts to access properties, defeating TypeScript
- No compile-time feedback when passing invalid types

## Correct

```tsx
// ✅ Good

// Constrain T to objects with an id
interface HasId {
  id: string | number;
}

function List<T extends HasId>({
  items,
  renderItem,
}: {
  items: T[];
  renderItem: (item: T) => React.ReactNode;
}) {
  return (
    <ul>
      {items.map((item) => (
        // Safe — T is guaranteed to have .id
        <li key={item.id}>{renderItem(item)}</li>
      ))}
    </ul>
  );
}

// Constrain T to record-like objects
function ObjectTable<T extends Record<string, unknown>>({
  data,
  keys,
}: {
  data: T[];
  keys: (keyof T)[];
}) {
  return (
    <table>
      <thead>
        <tr>
          {keys.map((key) => (
            <th key={String(key)}>{String(key)}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {data.map((row, i) => (
          <tr key={i}>
            {keys.map((key) => (
              <td key={String(key)}>{String(row[key] ?? "")}</td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Constrain key to keyof T — type-safe property access
function getProperty<T, K extends keyof T>(obj: T, key: K): T[K] {
  return obj[key]; // No cast needed — return type is T[K]
}

interface User {
  id: number;
  name: string;
  email: string;
}

const user: User = { id: 1, name: "Alice", email: "alice@example.com" };
const name = getProperty(user, "name"); // type is string
const id = getProperty(user, "id"); // type is number
// getProperty(user, "age"); // Compile error — "age" is not keyof User

// Multiple constraints
function merge<T extends HasId, U extends Partial<T>>(base: T, updates: U): T {
  return { ...base, ...updates };
}

// Constrained component props
interface SortableListProps<T extends HasId & { sortOrder: number }> {
  items: T[];
  onReorder: (items: T[]) => void;
}

function SortableList<T extends HasId & { sortOrder: number }>({
  items,
  onReorder,
}: SortableListProps<T>) {
  const sorted = [...items].sort((a, b) => a.sortOrder - b.sortOrder);

  return (
    <ul>
      {sorted.map((item) => (
        <li key={item.id}>Item {item.id} (order: {item.sortOrder})</li>
      ))}
    </ul>
  );
}
```

**Benefits:**
- `T extends HasId` guarantees `.id` exists, enabling safe key extraction
- `K extends keyof T` restricts keys to valid property names with typed return values
- `T extends Record<string, unknown>` ensures T is an object, not a primitive
- Multiple constraints (`T extends A & B`) combine requirements cleanly

Reference: [React TypeScript Cheatsheet — Generic Components](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#generic-components)


---

## Create Typed Context

**Impact: MEDIUM (prevents undefined context access and runtime crashes)**

Context requires proper typing for both the value and the default. A common pattern is using `null` as default with a custom hook that throws if used outside the provider.

## Incorrect

```tsx
// ❌ Bad
// No typing - any is inferred
const AuthContext = createContext(undefined)

// Unsafe access - might be undefined
const AuthContext = createContext<AuthContextType | undefined>(undefined)
const auth = useContext(AuthContext)
auth.user  // Error: possibly undefined

// Fake default value - misleading
const AuthContext = createContext<AuthContextType>({
  user: null,
  login: () => {},  // Fake implementation
  logout: () => {},
})
```

**Problems:**
- Untyped context loses all type safety and defaults to `any`
- Using `undefined` as default without a guard hook leads to unchecked access
- Fake default values mask provider absence and create misleading behavior

## Correct

```tsx
// ✅ Good
// Null Default with Custom Hook
interface User {
  id: number
  name: string
  email: string
}

interface AuthContextType {
  user: User | null
  login: (email: string, password: string) => Promise<void>
  logout: () => void
  isLoading: boolean
}

// Create context with null - indicates "not yet provided"
const AuthContext = createContext<AuthContextType | null>(null)

// Custom hook with runtime check
export function useAuth(): AuthContextType {
  const context = useContext(AuthContext)

  if (context === null) {
    throw new Error('useAuth must be used within an AuthProvider')
  }

  return context  // TypeScript knows this is AuthContextType, not null
}

// Provider component
export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  // assume: const authApi = createAuthApi()
  const login = async (email: string, password: string) => {
    setIsLoading(true)
    try {
      const user = await authApi.login(email, password)
      setUser(user)
    } finally {
      setIsLoading(false)
    }
  }

  const logout = () => {
    setUser(null)
  }

  return (
    <AuthContext.Provider value={{ user, login, logout, isLoading }}>
      {children}
    </AuthContext.Provider>
  )
}

// Safe usage in components
// import { Navigate } from 'react-router-dom'
function UserProfile() {
  const { user, logout } = useAuth()  // Typed, never null

  if (!user) {
    return <Navigate to="/login" />
  }

  return (
    <div>
      <h1>{user.name}</h1>
      <button onClick={logout}>Logout</button>
    </div>
  )
}

// Multiple Contexts Pattern
interface ThemeContextType {
  theme: 'light' | 'dark'
  toggleTheme: () => void
}

const ThemeContext = createContext<ThemeContextType | null>(null)

export function useTheme() {
  const context = useContext(ThemeContext)
  if (!context) {
    throw new Error('useTheme must be used within ThemeProvider')
  }
  return context
}

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const [theme, setTheme] = useState<'light' | 'dark'>('light')

  const toggleTheme = () => {
    setTheme(prev => prev === 'light' ? 'dark' : 'light')
  }

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme }}>
      {children}
    </ThemeContext.Provider>
  )
}

// Context with Reducer
interface State {
  count: number
  step: number
}

type Action =
  | { type: 'INCREMENT' }
  | { type: 'DECREMENT' }
  | { type: 'SET_STEP'; payload: number }

interface CounterContextType {
  state: State
  dispatch: React.Dispatch<Action>
}

const CounterContext = createContext<CounterContextType | null>(null)

function counterReducer(state: State, action: Action): State {
  switch (action.type) {
    case 'INCREMENT':
      return { ...state, count: state.count + state.step }
    case 'DECREMENT':
      return { ...state, count: state.count - state.step }
    case 'SET_STEP':
      return { ...state, step: action.payload }
    default:
      return state
  }
}

export function useCounter() {
  const context = useContext(CounterContext)
  if (!context) {
    throw new Error('useCounter must be used within CounterProvider')
  }
  return context
}

export function CounterProvider({ children }: { children: React.ReactNode }) {
  const [state, dispatch] = useReducer(counterReducer, {
    count: 0,
    step: 1,
  })

  return (
    <CounterContext.Provider value={{ state, dispatch }}>
      {children}
    </CounterContext.Provider>
  )
}
```

**Benefits:**
- Type safety: context value is properly typed throughout the application
- Runtime safety: custom hook throws a clear error if used outside the provider
- No fake defaults: null indicates "not provided" clearly
- Clean API: custom hook provides a nice consumer interface

Reference: [React TypeScript Cheatsheet](https://react-typescript-cheatsheet.netlify.app)


---

## Typed Context Provider Pattern

**Impact: MEDIUM (eliminates null checks at every useContext call site)**

The recommended pattern is `createContext<Type | null>(null)` paired with a custom hook that throws if the context is used outside the provider. This keeps the default value honest (no dummy objects) while giving consumers a non-null type.

## Incorrect

```tsx
// ❌ Bad
// Fake default value — misleading and breaks if used outside provider
const AuthContext = createContext<AuthState>({
  user: null,
  token: "",
  login: () => {},      // Dummy no-op — silently does nothing
  logout: () => {},     // Dummy no-op
  isAuthenticated: false,
});

// Every consumer must handle potential undefined anyway
function Profile() {
  const { user } = useContext(AuthContext);
  // user could still be null, but TypeScript thinks the context always exists
  return <div>{user?.name}</div>;
}
```

**Problems:**
- Fake default values silently do nothing when the provider is missing
- No-op functions like `login: () => {}` mask bugs — the app appears to work but does nothing
- No error when a component accidentally renders outside the provider tree
- Consumers get a false sense of type safety from the dummy default

## Correct

```tsx
// ✅ Good

// 1. Define the context type
interface AuthContextType {
  user: User | null;
  token: string;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

interface User {
  id: string;
  name: string;
  email: string;
}

// 2. Create context with null default — honest about having no value
const AuthContext = createContext<AuthContextType | null>(null);

// 3. Custom hook with null check and descriptive error
function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (context === null) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context; // Return type is AuthContextType, not AuthContextType | null
}

// 4. Provider component encapsulates state logic
function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState("");

  const login = async (email: string, password: string) => {
    const response = await fetch("/api/login", {
      method: "POST",
      body: JSON.stringify({ email, password }),
    });
    const data = await response.json();
    setUser(data.user);
    setToken(data.token);
  };

  const logout = () => {
    setUser(null);
    setToken("");
  };

  const value: AuthContextType = {
    user,
    token,
    isAuthenticated: user !== null,
    login,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

// 5. Consumers use the custom hook — no null checks needed
function Profile() {
  const { user, isAuthenticated, logout } = useAuth();
  // user is User | null (from the interface), but the context itself is guaranteed

  if (!isAuthenticated || !user) {
    return <p>Please log in.</p>;
  }

  return (
    <div>
      <h1>{user.name}</h1>
      <p>{user.email}</p>
      <button onClick={logout}>Logout</button>
    </div>
  );
}

// 6. App wraps the tree with the provider
function App() {
  return (
    <AuthProvider>
      <Profile />
    </AuthProvider>
  );
}
```

**Benefits:**
- `createContext<T | null>(null)` avoids fake defaults that mask missing providers
- Custom `useAuth` hook throws a clear error if used outside `AuthProvider`
- Consumers get `AuthContextType` (not `AuthContextType | null`), removing redundant null checks
- Provider encapsulates all state logic in one place

Reference: [React TypeScript Cheatsheet — Context](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/context)


---

## Context with useReducer

**Impact: MEDIUM (discriminated union actions eliminate invalid state transitions)**

When combining Context with `useReducer`, use discriminated union actions so each action type has its own payload shape. This prevents passing the wrong payload to the wrong action.

## Incorrect

```tsx
// ❌ Bad
interface State {
  count: number;
  name: string;
  items: string[];
}

// Loose action type — any action can have any payload
interface Action {
  type: string;
  payload: any;
}

function reducer(state: State, action: Action): State {
  switch (action.type) {
    case "INCREMENT":
      return { ...state, count: state.count + action.payload }; // payload could be anything
    case "SET_NAME":
      return { ...state, name: action.payload }; // no guarantee it's a string
    case "ADD_ITEM":
      return { ...state, items: [...state.items, action.payload] };
    default:
      return state; // Unknown actions silently pass through
  }
}

// Dispatching wrong payload type — not caught
// dispatch({ type: "INCREMENT", payload: "hello" })
// dispatch({ type: "SET_NAME", payload: 42 })
```

**Problems:**
- `type: string` accepts any string, including typos like `"INCRMENT"`
- `payload: any` means wrong payload types are never caught
- `default: return state` silently ignores unknown actions instead of failing
- No connection between action type and its expected payload

## Correct

```tsx
// ✅ Good

// State type
interface CounterState {
  count: number;
  name: string;
  items: string[];
  isLoading: boolean;
}

// Discriminated union — each action defines its own shape
type CounterAction =
  | { type: "INCREMENT" }
  | { type: "DECREMENT" }
  | { type: "INCREMENT_BY"; payload: number }
  | { type: "SET_NAME"; payload: string }
  | { type: "ADD_ITEM"; payload: string }
  | { type: "REMOVE_ITEM"; payload: number }
  | { type: "SET_LOADING"; payload: boolean }
  | { type: "RESET" };

const initialState: CounterState = {
  count: 0,
  name: "",
  items: [],
  isLoading: false,
};

function counterReducer(state: CounterState, action: CounterAction): CounterState {
  switch (action.type) {
    case "INCREMENT":
      return { ...state, count: state.count + 1 };
    case "DECREMENT":
      return { ...state, count: state.count - 1 };
    case "INCREMENT_BY":
      return { ...state, count: state.count + action.payload }; // payload is number
    case "SET_NAME":
      return { ...state, name: action.payload }; // payload is string
    case "ADD_ITEM":
      return { ...state, items: [...state.items, action.payload] }; // payload is string
    case "REMOVE_ITEM":
      return {
        ...state,
        items: state.items.filter((_, i) => i !== action.payload), // payload is number
      };
    case "SET_LOADING":
      return { ...state, isLoading: action.payload }; // payload is boolean
    case "RESET":
      return initialState; // No payload needed
  }
}

// Context setup
interface CounterContextType {
  state: CounterState;
  dispatch: React.Dispatch<CounterAction>;
}

const CounterContext = createContext<CounterContextType | null>(null);

function useCounter(): CounterContextType {
  const context = useContext(CounterContext);
  if (context === null) {
    throw new Error("useCounter must be used within a CounterProvider");
  }
  return context;
}

function CounterProvider({ children }: { children: React.ReactNode }) {
  const [state, dispatch] = useReducer(counterReducer, initialState);

  return (
    <CounterContext.Provider value={{ state, dispatch }}>
      {children}
    </CounterContext.Provider>
  );
}

// Consumer — dispatch is fully typed
function Counter() {
  const { state, dispatch } = useCounter();

  return (
    <div>
      <p>Count: {state.count}</p>
      <button onClick={() => dispatch({ type: "INCREMENT" })}>+1</button>
      <button onClick={() => dispatch({ type: "INCREMENT_BY", payload: 5 })}>+5</button>
      <button onClick={() => dispatch({ type: "RESET" })}>Reset</button>

      {/* Compile error: payload must be number for INCREMENT_BY */}
      {/* dispatch({ type: "INCREMENT_BY", payload: "five" }) */}

      {/* Compile error: INCREMENT doesn't accept payload */}
      {/* dispatch({ type: "INCREMENT", payload: 1 }) */}
    </div>
  );
}
```

**Benefits:**
- Each action type has exactly the payload shape it needs (or no payload at all)
- Wrong payload types are caught at compile time: `{ type: "INCREMENT_BY", payload: "five" }` fails
- Typos in action types are caught: `{ type: "INCRMENT" }` fails
- The reducer switch is exhaustive — no silent fallthrough on unknown actions

Reference: [React TypeScript Cheatsheet — useReducer](https://react-typescript-cheatsheet.netlify.app/docs/basic/getting-started/hooks#usereducer)


---

## ComponentProps and ComponentPropsWithoutRef

**Impact: LOW (reduces boilerplate when wrapping native HTML elements)**

Use `React.ComponentPropsWithoutRef<"element">` to inherit all native HTML attributes when building wrapper components. This avoids manually listing every HTML prop and stays in sync with React's type definitions.

## Incorrect

```tsx
// ❌ Bad
// Manually listing HTML attributes — incomplete and tedious
interface ButtonProps {
  children: React.ReactNode;
  variant: "primary" | "secondary";
  onClick?: () => void;
  disabled?: boolean;
  className?: string;
  type?: "button" | "submit" | "reset";
  id?: string;
  style?: React.CSSProperties;
  // Missing: aria-label, tabIndex, onFocus, onBlur, data-*, ...
  // Every new HTML attribute requires a manual update
}

function Button({ children, variant, ...rest }: ButtonProps) {
  return (
    <button className={`btn-${variant}`} {...rest}>
      {children}
    </button>
  );
}

// Cannot pass aria-label, onMouseEnter, form, etc.
// <Button variant="primary" aria-label="Submit">Save</Button>  // Error
```

**Problems:**
- Manually listing attributes is incomplete — always missing some valid HTML props
- Must update the interface every time you need a new native attribute
- Accessibility attributes (`aria-*`), `data-*` attributes, and event handlers are typically forgotten
- Duplicate effort: React already defines these types

## Correct

```tsx
// ✅ Good

// Extend ComponentPropsWithoutRef to get all native button props
interface ButtonProps extends React.ComponentPropsWithoutRef<"button"> {
  variant: "primary" | "secondary";
  size?: "sm" | "md" | "lg";
}

function Button({ variant, size = "md", className, children, ...rest }: ButtonProps) {
  return (
    <button
      className={`btn-${variant} btn-${size} ${className ?? ""}`}
      {...rest} // All native button props are forwarded
    >
      {children}
    </button>
  );
}

// All HTML button attributes work automatically
<Button variant="primary" aria-label="Save" data-testid="save-btn" type="submit">
  Save
</Button>

// For input wrappers
interface TextFieldProps extends React.ComponentPropsWithoutRef<"input"> {
  label: string;
  error?: string;
}

function TextField({ label, error, id, ...inputProps }: TextFieldProps) {
  const inputId = id ?? label.toLowerCase().replace(/\s+/g, "-");

  return (
    <div>
      <label htmlFor={inputId}>{label}</label>
      <input id={inputId} {...inputProps} />
      {error && <span className="error">{error}</span>}
    </div>
  );
}

// Gets all native input props: value, onChange, placeholder, maxLength, etc.
<TextField
  label="Email"
  type="email"
  placeholder="you@example.com"
  maxLength={100}
  required
  error="Email is required"
/>

// Extracting props from custom components
interface CardProps {
  title: string;
  children: React.ReactNode;
}

function Card({ title, children }: CardProps) {
  return <div><h2>{title}</h2>{children}</div>;
}

// Get props type of an existing component
type CardPropsFromComponent = React.ComponentProps<typeof Card>;
// CardPropsFromComponent = { title: string; children: React.ReactNode }
```

**Benefits:**
- All native HTML attributes are included automatically, including `aria-*` and `data-*`
- `ComponentPropsWithoutRef` excludes `ref` to avoid conflicts when not forwarding refs
- Custom props are declared once; native props come for free via `extends`
- `ComponentProps<typeof MyComponent>` extracts the props type from any existing component

Reference: [React TypeScript Cheatsheet — ComponentProps](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase#componentprops)


---

## Pick, Omit, Partial for Props

**Impact: LOW (eliminates prop duplication between related components)**

TypeScript's built-in utility types let you derive new prop types from existing ones instead of duplicating properties across related components.

## Incorrect

```tsx
// ❌ Bad
// Full user props — the "source of truth"
interface UserProps {
  id: string;
  name: string;
  email: string;
  avatar: string;
  role: "admin" | "user";
  createdAt: Date;
  lastLogin: Date;
}

// Duplicated subset for the header
interface UserHeaderProps {
  name: string;     // Duplicated from UserProps
  email: string;    // Duplicated from UserProps
  avatar: string;   // Duplicated from UserProps
}

// Duplicated subset for editing — must stay in sync manually
interface EditUserProps {
  name: string;     // Duplicated
  email: string;    // Duplicated
  avatar: string;   // Duplicated
  role: "admin" | "user"; // Duplicated
}

// If UserProps.name changes to firstName + lastName, you must update 3 places
```

**Problems:**
- Props are copied between interfaces and quickly fall out of sync
- Renaming or changing a type in the source requires updating every duplicate
- No compiler enforcement that derived types stay consistent with the source
- More code to maintain with no added safety

## Correct

```tsx
// ✅ Good

// Single source of truth
interface UserProps {
  id: string;
  name: string;
  email: string;
  avatar: string;
  role: "admin" | "user";
  createdAt: Date;
  lastLogin: Date;
}

// Pick — select only the properties you need
type UserHeaderProps = Pick<UserProps, "name" | "email" | "avatar">;

function UserHeader({ name, email, avatar }: UserHeaderProps) {
  return (
    <div className="header">
      <img src={avatar} alt={name} />
      <h2>{name}</h2>
      <p>{email}</p>
    </div>
  );
}

// Omit — exclude properties that don't apply
type EditUserProps = Omit<UserProps, "id" | "createdAt" | "lastLogin">;

function EditUserForm({ name, email, avatar, role }: EditUserProps) {
  return (
    <form>
      <input defaultValue={name} />
      <input defaultValue={email} />
      <input defaultValue={avatar} />
      <select defaultValue={role}>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
    </form>
  );
}

// Partial — make all properties optional (useful for update payloads)
type UpdateUserPayload = Partial<Omit<UserProps, "id" | "createdAt">>;

function updateUser(id: string, updates: UpdateUserPayload) {
  // Only changed fields are required
  // updateUser("1", { name: "New Name" }) — valid
  // updateUser("1", { email: "new@email.com", role: "admin" }) — valid
}

// Required — make optional properties required
interface FormFields {
  name?: string;
  email?: string;
  phone?: string;
}

type CompleteFormFields = Required<FormFields>;
// All fields are now required: { name: string; email: string; phone: string }

// Combining utility types
type UserSummary = Pick<UserProps, "id" | "name"> & {
  postCount: number;
};

function UserCard({ id, name, postCount }: UserSummary) {
  return (
    <div>
      <h3>{name}</h3>
      <p>{postCount} posts</p>
    </div>
  );
}

// Readonly — prevent mutation
type ImmutableUser = Readonly<UserProps>;

function UserDisplay({ user }: { user: ImmutableUser }) {
  // user.name = "new"; // Compile error — cannot assign to readonly property
  return <span>{user.name}</span>;
}
```

**Benefits:**
- `Pick` and `Omit` derive from a single source of truth, so changes propagate automatically
- `Partial` creates update/patch types without listing every optional field
- Combining utility types (`Partial<Omit<T, ...>>`) expresses precise constraints concisely
- Compiler catches inconsistencies immediately when the source type changes

Reference: [React TypeScript Cheatsheet — Useful Patterns by Use Case](https://react-typescript-cheatsheet.netlify.app/docs/advanced/patterns_by_usecase)


---

## Discriminated Unions for State

**Impact: LOW (makes impossible states unrepresentable at the type level)**

Instead of combining booleans and nullable fields (where impossible combinations exist), use a discriminated union where each state variant carries only the data it needs. A `never` check in the default case ensures exhaustiveness at compile time.

## Incorrect

```tsx
// ❌ Bad
interface RequestState<T> {
  isLoading: boolean;
  error: string | null;
  data: T | null;
}

function UserProfile() {
  const [state, setState] = useState<RequestState<User>>({
    isLoading: false,
    error: null,
    data: null,
  });

  // Impossible states are representable:
  // { isLoading: true, error: "fail", data: someUser } — loading AND error AND data?
  // { isLoading: false, error: null, data: null } — not loading, no error, no data?

  if (state.isLoading) {
    return <p>Loading...</p>;
  }

  if (state.error) {
    return <p>Error: {state.error}</p>;
  }

  // TypeScript still thinks data could be null here
  return <div>{state.data?.name}</div>;
}
```

**Problems:**
- `isLoading: true` + `data: someUser` is representable but makes no sense
- `isLoading: true` + `error: "fail"` is representable but contradictory
- After checking `isLoading` and `error`, TypeScript still can't narrow `data` to non-null
- Every consumer must add defensive null checks even in states where data is guaranteed

## Correct

```tsx
// ✅ Good

// Each state has only the properties it needs
type AsyncState<T> =
  | { status: "idle" }
  | { status: "loading" }
  | { status: "error"; error: Error }
  | { status: "success"; data: T };

// Exhaustive check helper — compile error if a case is missed
function assertNever(value: never): never {
  throw new Error(`Unexpected value: ${value}`);
}

interface User {
  id: string;
  name: string;
  email: string;
}

function UserProfile() {
  const [state, setState] = useState<AsyncState<User>>({ status: "idle" });

  const fetchUser = async (id: string) => {
    setState({ status: "loading" });

    try {
      const response = await fetch(`/api/users/${id}`);
      if (!response.ok) throw new Error("Failed to fetch");
      const data: User = await response.json();
      setState({ status: "success", data }); // data is required here
    } catch (e) {
      setState({ status: "error", error: e instanceof Error ? e : new Error(String(e)) });
    }
  };

  // Render with exhaustive switch
  switch (state.status) {
    case "idle":
      return <button onClick={() => fetchUser("1")}>Load User</button>;

    case "loading":
      return <p>Loading...</p>;

    case "error":
      // state.error is Error — guaranteed by the union
      return <p>Error: {state.error.message}</p>;

    case "success":
      // state.data is User — guaranteed, no null check needed
      return (
        <div>
          <h1>{state.data.name}</h1>
          <p>{state.data.email}</p>
        </div>
      );

    default:
      // Compile error if a new status is added but not handled
      return assertNever(state);
  }
}

// Discriminated unions for component variants
type NotificationProps =
  | { type: "success"; message: string }
  | { type: "error"; message: string; retry: () => void }
  | { type: "warning"; message: string; dismissable: boolean };

function Notification(props: NotificationProps) {
  switch (props.type) {
    case "success":
      return <div className="success">{props.message}</div>;

    case "error":
      return (
        <div className="error">
          {props.message}
          <button onClick={props.retry}>Retry</button>
        </div>
      );

    case "warning":
      return (
        <div className="warning">
          {props.message}
          {props.dismissable && <button>Dismiss</button>}
        </div>
      );

    default:
      return assertNever(props);
  }
}

// Usage — TypeScript enforces the correct shape for each type
<Notification type="success" message="Saved!" />
<Notification type="error" message="Failed" retry={() => refetch()} />
<Notification type="warning" message="Disk almost full" dismissable />

// Compile errors:
// <Notification type="error" message="Failed" />  // Missing 'retry'
// <Notification type="success" message="OK" retry={() => {}} />  // 'retry' doesn't exist on success
```

**Benefits:**
- Impossible states (loading + data, error + success) cannot be represented
- TypeScript narrows the type inside each `case` branch, so `data` and `error` are non-null where guaranteed
- `assertNever` in the default case causes a compile error when a new variant is added but not handled
- Component variant props are enforced: `error` requires `retry`, `success` does not accept it

Reference: [React TypeScript Cheatsheet — Discriminated Unions](https://react-typescript-cheatsheet.netlify.app/docs/basic/troubleshooting/types#discriminated-unions)


---

