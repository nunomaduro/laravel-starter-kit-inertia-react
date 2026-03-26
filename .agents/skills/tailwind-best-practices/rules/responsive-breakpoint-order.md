---
id: responsive-breakpoint-order
title: Responsive Breakpoint Order
priority: CRITICAL
category: Responsive Design
---

# Responsive Breakpoint Order

Maintain consistent breakpoint ordering in class names for readability and predictability.

## Bad Example

```html
<!-- Random breakpoint order (hard to read) -->
<div class="lg:px-8 px-4 xl:px-12 md:px-6 sm:px-5">
  Content
</div>

<!-- Mixed ordering makes it hard to understand responsive behavior -->
<h1 class="xl:text-5xl text-2xl md:text-3xl sm:text-2xl lg:text-4xl">
  Heading
</h1>

<!-- Inconsistent grouping -->
<div class="w-full md:w-1/2 p-4 lg:w-1/3 md:p-6 lg:p-8">
  Card
</div>
```

## Good Example

```html
<!-- Ascending breakpoint order (mobile to desktop) -->
<div class="px-4 sm:px-5 md:px-6 lg:px-8 xl:px-12">
  Content
</div>

<!-- Clear progression from smallest to largest -->
<h1 class="text-2xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl">
  Heading
</h1>

<!-- Group by property, then order by breakpoint -->
<div class="w-full md:w-1/2 lg:w-1/3 p-4 md:p-6 lg:p-8">
  Card
</div>
```

## Why

1. **Predictable scanning**: Developers can quickly scan left-to-right to understand how styles change across breakpoints.

2. **Easier debugging**: When breakpoints are ordered, it's clear which style applies at each screen size.

3. **Consistent team conventions**: A standard order ensures all team members write classes the same way.

4. **Mirrors CSS cascade**: The left-to-right order matches how CSS media queries would naturally be written.

5. **Automated tooling support**: Tools like Prettier with tailwind plugin automatically sort classes this way.

## Recommended Class Order

```html
<div class="
  [base styles]
  [sm: styles]
  [md: styles]
  [lg: styles]
  [xl: styles]
  [2xl: styles]
">
```

## Using Prettier Plugin

Install `prettier-plugin-tailwindcss` to automatically sort classes:

```bash
npm install -D prettier prettier-plugin-tailwindcss
```

```json
// .prettierrc
{
  "plugins": ["prettier-plugin-tailwindcss"]
}
```
