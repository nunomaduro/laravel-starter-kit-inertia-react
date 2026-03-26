---
title: Event Handler Types
impact: HIGH
impactDescription: "provides autocomplete for event properties and catches errors at compile time"
tags: event, handler, ChangeEvent, FormEvent, KeyboardEvent
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
