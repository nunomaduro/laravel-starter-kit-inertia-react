---
id: component-form-elements
title: Form Elements
priority: HIGH
category: Component Patterns
---

# Form Elements

Create accessible, consistent form components with proper states, labels, and validation styling.

## Bad Example

```html
<!-- Missing labels and accessibility -->
<input type="text" class="border p-2" placeholder="Enter name">

<!-- Inconsistent input styling -->
<input type="email" class="border-2 rounded-lg p-3">
<input type="password" class="border p-2 rounded">

<!-- No error or focus states -->
<input type="text" class="border border-gray-300">

<!-- Using placeholder as label -->
<input type="email" placeholder="Email address">
```

## Good Example

```html
<!-- Text input with label -->
<div>
  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Full Name
  </label>
  <input
    type="text"
    id="name"
    name="name"
    class="
      w-full px-4 py-2 rounded-lg
      border border-gray-300 dark:border-gray-600
      bg-white dark:bg-gray-800
      text-gray-900 dark:text-white
      placeholder-gray-400 dark:placeholder-gray-500
      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
      disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed
      transition-colors duration-150
    "
    placeholder="John Doe"
  >
</div>

<!-- Input with error state -->
<div>
  <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Email Address
  </label>
  <input
    type="email"
    id="email"
    name="email"
    class="
      w-full px-4 py-2 rounded-lg
      border border-red-500 dark:border-red-400
      bg-white dark:bg-gray-800
      text-gray-900 dark:text-white
      focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent
    "
    aria-invalid="true"
    aria-describedby="email-error"
  >
  <p id="email-error" class="mt-1 text-sm text-red-600 dark:text-red-400">
    Please enter a valid email address.
  </p>
</div>

<!-- Select dropdown -->
<div>
  <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Country
  </label>
  <select
    id="country"
    name="country"
    class="
      w-full px-4 py-2 rounded-lg
      border border-gray-300 dark:border-gray-600
      bg-white dark:bg-gray-800
      text-gray-900 dark:text-white
      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
      appearance-none
      bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNSA3LjVMMTAgMTIuNUwxNSA3LjUiIHN0cm9rZT0iIzZCNzI4MCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPjwvc3ZnPg==')]
      bg-no-repeat bg-[right_0.75rem_center]
    "
  >
    <option value="">Select a country</option>
    <option value="us">United States</option>
    <option value="uk">United Kingdom</option>
  </select>
</div>

<!-- Textarea -->
<div>
  <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Message
  </label>
  <textarea
    id="message"
    name="message"
    rows="4"
    class="
      w-full px-4 py-2 rounded-lg
      border border-gray-300 dark:border-gray-600
      bg-white dark:bg-gray-800
      text-gray-900 dark:text-white
      placeholder-gray-400 dark:placeholder-gray-500
      focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
      resize-none
    "
    placeholder="Your message..."
  ></textarea>
</div>
```

## Why

1. **Accessibility**: Proper labels, ARIA attributes, and keyboard navigation.

2. **Consistent styling**: All form elements match the design system.

3. **Clear states**: Visual feedback for focus, error, disabled states.

4. **Dark mode support**: Forms work in both light and dark themes.

5. **User experience**: Proper spacing, sizing, and touch targets.

## Checkbox and Radio

```html
<!-- Checkbox -->
<label class="flex items-center gap-3 cursor-pointer">
  <input
    type="checkbox"
    class="
      w-5 h-5 rounded
      border-gray-300 dark:border-gray-600
      text-primary-600
      focus:ring-primary-500 focus:ring-offset-0
      dark:bg-gray-800 dark:checked:bg-primary-600
    "
  >
  <span class="text-gray-700 dark:text-gray-300">Remember me</span>
</label>

<!-- Radio group -->
<fieldset>
  <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
    Select an option
  </legend>
  <div class="space-y-2">
    <label class="flex items-center gap-3 cursor-pointer">
      <input
        type="radio"
        name="option"
        value="1"
        class="
          w-5 h-5
          border-gray-300 dark:border-gray-600
          text-primary-600
          focus:ring-primary-500 focus:ring-offset-0
          dark:bg-gray-800
        "
      >
      <span class="text-gray-700 dark:text-gray-300">Option 1</span>
    </label>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="radio" name="option" value="2" class="w-5 h-5 border-gray-300 text-primary-600 focus:ring-primary-500">
      <span class="text-gray-700 dark:text-gray-300">Option 2</span>
    </label>
  </div>
</fieldset>
```

## Toggle Switch

```html
<label class="flex items-center cursor-pointer">
  <div class="relative">
    <input type="checkbox" class="sr-only peer">
    <div class="
      w-11 h-6 rounded-full
      bg-gray-200 dark:bg-gray-700
      peer-checked:bg-primary-600
      peer-focus:ring-2 peer-focus:ring-primary-500 peer-focus:ring-offset-2
      transition-colors duration-200
    "></div>
    <div class="
      absolute left-0.5 top-0.5
      w-5 h-5 rounded-full
      bg-white shadow-sm
      peer-checked:translate-x-5
      transition-transform duration-200
    "></div>
  </div>
  <span class="ml-3 text-gray-700 dark:text-gray-300">Enable notifications</span>
</label>
```

## Input with Addon

```html
<!-- Prefix addon -->
<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Website
  </label>
  <div class="flex">
    <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm">
      https://
    </span>
    <input
      type="text"
      class="flex-1 px-4 py-2 rounded-r-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
      placeholder="www.example.com"
    >
  </div>
</div>

<!-- Input with button -->
<div class="flex">
  <input
    type="email"
    class="flex-1 px-4 py-2 rounded-l-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
    placeholder="Enter your email"
  >
  <button class="px-6 py-2 bg-primary-600 text-white rounded-r-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
    Subscribe
  </button>
</div>
```

## File Input

```html
<label class="block">
  <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Upload file</span>
  <input
    type="file"
    class="
      block w-full text-sm text-gray-500 dark:text-gray-400
      file:mr-4 file:py-2 file:px-4
      file:rounded-lg file:border-0
      file:text-sm file:font-medium
      file:bg-primary-50 file:text-primary-700
      dark:file:bg-primary-900/50 dark:file:text-primary-300
      hover:file:bg-primary-100 dark:hover:file:bg-primary-900
      cursor-pointer
    "
  >
</label>
```

## Form Layout

```html
<form class="space-y-6">
  <!-- Two column on larger screens -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
      <label for="firstName">First Name</label>
      <input type="text" id="firstName" class="...">
    </div>
    <div>
      <label for="lastName">Last Name</label>
      <input type="text" id="lastName" class="...">
    </div>
  </div>

  <!-- Full width -->
  <div>
    <label for="email">Email</label>
    <input type="email" id="email" class="...">
  </div>

  <!-- Form actions -->
  <div class="flex justify-end gap-3">
    <button type="button" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">Cancel</button>
    <button type="submit" class="px-4 py-2 text-white bg-primary-600 rounded-lg">Submit</button>
  </div>
</form>
```
