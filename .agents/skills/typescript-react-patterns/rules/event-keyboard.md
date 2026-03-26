---
title: Typing Keyboard Events
impact: HIGH
impactDescription: "catches invalid key checks and missing modifier handling at compile time"
tags: events, keyboard, KeyboardEvent
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
