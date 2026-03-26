---
id: component-modals
title: Modal Components
priority: HIGH
category: Component Patterns
---

# Modal Components

Create accessible modal dialogs with proper focus management, backdrop, and animations.

## Bad Example

```html
<!-- Inaccessible modal -->
<div class="fixed inset-0 bg-black/50">
  <div class="bg-white p-4 mx-auto mt-20">
    <h2>Modal Title</h2>
    <p>Content</p>
    <button>Close</button>
  </div>
</div>

<!-- No focus trap or keyboard handling -->
<div id="modal" class="fixed inset-0 hidden">
  <div class="bg-white">
    Content without accessibility considerations
  </div>
</div>

<!-- Modal without proper centering -->
<div class="fixed top-0 left-0 w-full h-full">
  <div class="bg-white w-96">
    Not properly centered or positioned
  </div>
</div>
```

## Good Example

```html
<!-- Accessible modal with proper structure -->
<div
  class="fixed inset-0 z-50 overflow-y-auto"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modal-title"
>
  <!-- Backdrop -->
  <div
    class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
    aria-hidden="true"
  ></div>

  <!-- Modal positioning wrapper -->
  <div class="flex min-h-full items-center justify-center p-4">
    <!-- Modal panel -->
    <div class="
      relative w-full max-w-lg
      bg-white dark:bg-gray-800
      rounded-xl shadow-xl
      transform transition-all
    ">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">
          Modal Title
        </h2>
        <button
          type="button"
          class="p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          aria-label="Close modal"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <p class="text-gray-600 dark:text-gray-300">
          Modal content goes here. This modal is fully accessible with proper ARIA attributes.
        </p>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-xl">
        <button
          type="button"
          class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          class="px-4 py-2 rounded-lg text-white bg-primary-600 hover:bg-primary-700 transition-colors"
        >
          Confirm
        </button>
      </div>
    </div>
  </div>
</div>
```

## Why

1. **Accessibility**: Proper ARIA roles, labels, and focus management.

2. **Keyboard support**: Escape key closes, Tab traps focus within modal.

3. **Visual feedback**: Backdrop indicates modal context, animation feels natural.

4. **Responsive**: Modal adapts to different screen sizes.

5. **Scroll handling**: Body scroll locked, modal content scrollable.

## Modal Sizes

```html
<!-- Small modal -->
<div class="relative w-full max-w-sm ...">...</div>

<!-- Medium modal (default) -->
<div class="relative w-full max-w-lg ...">...</div>

<!-- Large modal -->
<div class="relative w-full max-w-2xl ...">...</div>

<!-- Extra large modal -->
<div class="relative w-full max-w-4xl ...">...</div>

<!-- Full width modal -->
<div class="relative w-full max-w-full mx-4 ...">...</div>
```

## Confirmation Dialog

```html
<div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50" aria-hidden="true"></div>

  <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 text-center">
    <!-- Warning icon -->
    <div class="mx-auto w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
      <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>

    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
      Delete Item?
    </h3>
    <p class="text-gray-600 dark:text-gray-300 mb-6">
      Are you sure you want to delete this item? This action cannot be undone.
    </p>

    <div class="flex gap-3 justify-center">
      <button class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
        Cancel
      </button>
      <button class="px-4 py-2 rounded-lg text-white bg-red-600 hover:bg-red-700">
        Delete
      </button>
    </div>
  </div>
</div>
```

## Slide-over Panel

```html
<div class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-black/50" aria-hidden="true"></div>

  <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
    <div class="w-screen max-w-md transform transition-transform duration-300 ease-in-out">
      <div class="flex h-full flex-col bg-white dark:bg-gray-800 shadow-xl">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Panel Title
          </h2>
          <button class="p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content (scrollable) -->
        <div class="flex-1 overflow-y-auto p-6">
          <p class="text-gray-600 dark:text-gray-300">
            Slide-over panel content...
          </p>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
          <button class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
```

## Modal Animation Classes

```html
<!-- Opening animation -->
<div class="
  transform transition-all duration-300 ease-out
  opacity-0 scale-95
  data-[open]:opacity-100 data-[open]:scale-100
">
  Modal content
</div>

<!-- Closing animation -->
<div class="
  transform transition-all duration-200 ease-in
  data-[closing]:opacity-0 data-[closing]:scale-95
">
  Modal content
</div>
```

## Focus Management (JavaScript)

```javascript
// Trap focus within modal
function trapFocus(element) {
  const focusableElements = element.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  const firstFocusable = focusableElements[0];
  const lastFocusable = focusableElements[focusableElements.length - 1];

  element.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
      if (e.shiftKey && document.activeElement === firstFocusable) {
        e.preventDefault();
        lastFocusable.focus();
      } else if (!e.shiftKey && document.activeElement === lastFocusable) {
        e.preventDefault();
        firstFocusable.focus();
      }
    }
    if (e.key === 'Escape') {
      closeModal();
    }
  });

  firstFocusable.focus();
}
```
