---
title: ForwardRef Typing
impact: CRITICAL
impactDescription: "ensures ref types match actual DOM elements"
tags: component, forwardRef, ref, useImperativeHandle
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
