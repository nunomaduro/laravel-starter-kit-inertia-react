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
