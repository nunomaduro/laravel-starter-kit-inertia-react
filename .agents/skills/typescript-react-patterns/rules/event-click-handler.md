---
title: Click Event Handler Typing
impact: HIGH
impactDescription: "catches wrong element types and event property access at compile time"
tags: event, click, MouseEvent, handler
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
