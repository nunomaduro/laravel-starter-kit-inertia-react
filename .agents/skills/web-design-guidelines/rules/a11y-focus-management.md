---
title: Manage Keyboard Focus Properly
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Focus order and visibility"
tags: accessibility, keyboard, focus, navigation, modals
---

## Manage Keyboard Focus Properly

**Impact: CRITICAL (WCAG 2.1 Level A - Focus order and visibility)**

Manage keyboard focus intentionally to create a logical, predictable navigation experience. Focus should follow the user's expectations and never get lost or trapped.

## Incorrect

```html
<!-- ❌ Bad: Poor focus management -->
<div class="modal" style="display: block;">
  <div class="modal-content">
    <h2>Delete Item?</h2>
    <p>This action cannot be undone.</p>
    <button onclick="closeModal()">Cancel</button>
    <button onclick="deleteItem()">Delete</button>
  </div>
</div>
<!-- Focus stays on the trigger button behind the modal -->

<script>
function openModal() {
  document.querySelector('.modal').style.display = 'block';
  // No focus management - user is lost
}

function closeModal() {
  document.querySelector('.modal').style.display = 'none';
  // Focus doesn't return to trigger
}
</script>
```

```css
/* ❌ Bad: Removing focus indicator entirely */
*:focus {
  outline: none;
}
```

**Problems:**
- Modal opens without moving focus, leaving keyboard users stranded behind the overlay
- Focus is not trapped inside the modal, so users can tab to hidden content
- Closing the modal does not return focus to the trigger element
- Removing all focus outlines makes keyboard navigation impossible

## Correct

```html
<!-- ✅ Good: Proper focus management -->
<button id="delete-trigger" onclick="openModal()">Delete Item</button>

<div class="modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-title"
     hidden>
  <div class="modal-content">
    <h2 id="modal-title">Delete Item?</h2>
    <p>This action cannot be undone.</p>
    <button id="cancel-btn" onclick="closeModal()">Cancel</button>
    <button onclick="deleteItem()">Delete</button>
  </div>
</div>

<script>
let lastFocusedElement;

function openModal() {
  lastFocusedElement = document.activeElement;
  const modal = document.querySelector('.modal');
  modal.hidden = false;

  // Move focus to first focusable element
  document.getElementById('cancel-btn').focus();

  // Trap focus within modal
  modal.addEventListener('keydown', trapFocus);
}

function closeModal() {
  const modal = document.querySelector('.modal');
  modal.hidden = true;
  modal.removeEventListener('keydown', trapFocus);

  // Return focus to trigger element
  lastFocusedElement.focus();
}

function trapFocus(e) {
  if (e.key !== 'Tab') return;

  const focusableElements = e.currentTarget.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];

  if (e.shiftKey && document.activeElement === firstElement) {
    lastElement.focus();
    e.preventDefault();
  } else if (!e.shiftKey && document.activeElement === lastElement) {
    firstElement.focus();
    e.preventDefault();
  }
}
</script>
```

```css
/* ✅ Good: Custom focus indicator */
*:focus {
  outline: 2px solid #005fcc;
  outline-offset: 2px;
}

/* Enhanced focus for better visibility */
*:focus-visible {
  outline: 3px solid #005fcc;
  outline-offset: 2px;
  box-shadow: 0 0 0 4px rgba(0, 95, 204, 0.3);
}
```

**Benefits:**
- Focus moves into the modal on open, so keyboard users interact with the correct content
- Focus trap prevents users from accidentally leaving the modal
- Focus returns to the trigger element on close, preserving navigation context
- Custom focus indicators ensure keyboard navigation is always visible

Reference: [WCAG 2.4.3 Focus Order](https://www.w3.org/WAI/WCAG21/Understanding/focus-order.html)
