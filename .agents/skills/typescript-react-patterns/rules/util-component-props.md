---
title: ComponentProps and ComponentPropsWithoutRef
impact: LOW
impactDescription: "reduces boilerplate when wrapping native HTML elements"
tags: utility-types, ComponentProps, composition
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
