---
title: Default Props Typing
impact: CRITICAL
impactDescription: "avoids deprecated patterns and ensures type-aware defaults"
tags: component, defaultProps, defaults, destructuring
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
