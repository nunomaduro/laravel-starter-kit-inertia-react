---
title: Ensure Full Keyboard Navigation
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Required for keyboard-only users"
tags: accessibility, keyboard, focus, navigation
---

## Ensure Full Keyboard Navigation

**Impact: CRITICAL (WCAG 2.1 Level A - Required for keyboard-only users)**

Ensure all interactive elements and functionality are fully accessible via keyboard. Users should be able to navigate, activate, and interact with all features without requiring a mouse.

## Incorrect

```html
<!-- ❌ Bad: Click-only interactions -->
<div class="card" onclick="showDetails()">
  <img src="product.jpg">
  <span class="title">Product Name</span>
</div>

<span class="link" onclick="navigate('/page')">Go to page</span>

<div class="dropdown">
  <div class="trigger" onclick="toggleMenu()">Menu</div>
  <div class="menu">
    <div class="item" onclick="selectItem(1)">Option 1</div>
    <div class="item" onclick="selectItem(2)">Option 2</div>
    <div class="item" onclick="selectItem(3)">Option 3</div>
  </div>
</div>

<div class="slider" onmousedown="startDrag()">
  <div class="handle"></div>
</div>
```

**Problems:**
- `<div>` and `<span>` elements are not focusable or activatable by keyboard
- Click-only handlers exclude all keyboard and switch-device users
- No ARIA roles or states to communicate component behavior to assistive technology
- Custom slider has no keyboard interaction pattern

## Correct

```html
<!-- ✅ Good: Full keyboard support -->
<article class="card"
         tabindex="0"
         role="button"
         onclick="showDetails()"
         onkeydown="handleCardKey(event)">
  <img src="product.jpg" alt="Product Name - Blue Widget">
  <h3 class="title">Product Name</h3>
</article>

<a href="/page" class="link">Go to page</a>

<div class="dropdown">
  <button class="trigger"
          aria-expanded="false"
          aria-haspopup="menu"
          onclick="toggleMenu()"
          onkeydown="handleTriggerKey(event)">
    Menu
  </button>
  <ul class="menu" role="menu" hidden>
    <li role="menuitem" tabindex="-1" onkeydown="handleMenuKey(event, 0)">Option 1</li>
    <li role="menuitem" tabindex="-1" onkeydown="handleMenuKey(event, 1)">Option 2</li>
    <li role="menuitem" tabindex="-1" onkeydown="handleMenuKey(event, 2)">Option 3</li>
  </ul>
</div>

<div class="slider"
     role="slider"
     tabindex="0"
     aria-valuemin="0"
     aria-valuemax="100"
     aria-valuenow="50"
     aria-label="Volume"
     onkeydown="handleSliderKey(event)">
  <div class="handle"></div>
</div>

<script>
function handleCardKey(event) {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    showDetails();
  }
}

function handleTriggerKey(event) {
  switch (event.key) {
    case 'ArrowDown':
    case 'Enter':
    case ' ':
      event.preventDefault();
      openMenu();
      focusFirstItem();
      break;
    case 'Escape':
      closeMenu();
      break;
  }
}

function handleMenuKey(event, index) {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      focusNextItem(index);
      break;
    case 'ArrowUp':
      event.preventDefault();
      focusPrevItem(index);
      break;
    case 'Enter':
    case ' ':
      event.preventDefault();
      selectItem(index);
      closeMenu();
      break;
    case 'Escape':
      closeMenu();
      focusTrigger();
      break;
  }
}

function handleSliderKey(event) {
  const slider = event.target;
  let value = parseInt(slider.getAttribute('aria-valuenow'));

  switch (event.key) {
    case 'ArrowRight':
    case 'ArrowUp':
      event.preventDefault();
      value = Math.min(100, value + 1);
      break;
    case 'ArrowLeft':
    case 'ArrowDown':
      event.preventDefault();
      value = Math.max(0, value - 1);
      break;
    case 'PageUp':
      event.preventDefault();
      value = Math.min(100, value + 10);
      break;
    case 'PageDown':
      event.preventDefault();
      value = Math.max(0, value - 10);
      break;
    case 'Home':
      event.preventDefault();
      value = 0;
      break;
    case 'End':
      event.preventDefault();
      value = 100;
      break;
  }

  slider.setAttribute('aria-valuenow', value);
  updateSliderPosition(value);
}
</script>
```

**Benefits:**
- All interactive elements are reachable and operable via keyboard
- Standard keyboard patterns (Enter, Space, Arrow keys, Escape) are implemented
- ARIA roles and states communicate component behavior to screen readers
- Native HTML elements (`<a>`, `<button>`) provide built-in keyboard support

Reference: [WCAG 2.1.1 Keyboard](https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html)
