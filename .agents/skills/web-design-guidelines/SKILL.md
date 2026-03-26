---
name: web-design-guidelines
description: UI/UX best practices and accessibility audit. Use when reviewing UI code, checking accessibility, running accessibility audits, auditing forms, or ensuring web interface best practices. Triggers on "audit accessibility", "check WCAG", "review UI", "check accessibility", "audit design", "review UX", or "check best practices".
license: MIT
metadata:
  author: agent-skills
  version: "2.1.0"
---

# Web Design Guidelines

WCAG accessibility, semantic HTML, keyboard navigation, forms, and performance patterns for inclusive web interfaces. Contains 23 rules across 4 categories. Supports both **coding reference** and **audit mode**.

## Metadata

- **Version:** 2.1.0
- **Rule Count:** 23 rules across 4 categories
- **License:** MIT

## How to Audit

When the user asks to "audit accessibility", "check WCAG compliance", or "review accessibility" — run the checklist below against their codebase.

### Step 1: Determine Scope

- If arguments provided (`$ARGUMENTS`): audit only those files or components
- If no arguments: audit all UI components and pages in the codebase

### Step 2: Detect Stack

Check the project for:
- `.tsx`/`.jsx` files → React
- `.vue` files → Vue
- `.blade.php` files → Laravel Blade
- `.html` files → Static HTML

### Step 3: Run Accessibility Checklist

Work through every item below. For each, output:
- **PASS** — brief confirmation of what was verified
- **FAIL** — exact `file:line`, description of the issue, and fix recommendation
- **N/A** — if the check does not apply to this project

#### Semantic HTML & Structure
- [ ] Pages use semantic elements (`<header>`, `<main>`, `<nav>`, `<footer>`, `<article>`, `<section>`) — not `<div>` soup
- [ ] Heading hierarchy is sequential (`h1` → `h2` → `h3`) — no skipped levels
- [ ] One `<h1>` per page
- [ ] Skip link exists to bypass navigation (`<a href="#main-content">Skip to content</a>`)
- [ ] Landmark regions are labelled when duplicated (`aria-label` on multiple `<nav>` elements)

#### Keyboard Navigation
- [ ] All interactive elements are reachable via Tab key
- [ ] Focus order follows visual order (no positive `tabindex` values)
- [ ] Focus indicator is visible on all focusable elements (not removed with `outline: none` without replacement)
- [ ] Modal dialogs trap focus and return focus on close
- [ ] Custom components (dropdowns, tabs, accordions) support expected keyboard patterns (Arrow keys, Escape, Enter)

#### Images & Media
- [ ] All `<img>` elements have `alt` attributes — descriptive for content images, empty (`alt=""`) for decorative
- [ ] Complex images (charts, diagrams) have extended descriptions
- [ ] Video content has captions or transcripts

#### Color & Contrast
- [ ] Text meets WCAG AA contrast ratio (4.5:1 for normal text, 3:1 for large text)
- [ ] Information is not conveyed by color alone (e.g., error states use icons + text, not just red)
- [ ] UI components and focus indicators meet 3:1 contrast ratio against background

#### Forms
- [ ] Every input has an associated `<label>` (not just placeholder text)
- [ ] Required fields indicated with both visual cue and `aria-required="true"`
- [ ] Error messages linked to inputs via `aria-describedby`
- [ ] Error messages use `role="alert"` or `aria-live="assertive"` for screen reader announcement
- [ ] Correct `type` attributes on inputs (`email`, `tel`, `url`, `number`)
- [ ] `autocomplete` attributes present on common fields (name, email, address, credit card)

#### ARIA & Screen Readers
- [ ] ARIA labels on icon-only buttons (`aria-label="Close"`)
- [ ] Dynamic content updates use `aria-live` regions
- [ ] Loading states announced to screen readers (`aria-busy="true"`, status messages)
- [ ] Decorative elements hidden from screen readers (`aria-hidden="true"`)

#### Motion & Animation
- [ ] `prefers-reduced-motion` respected — animations disabled or reduced
- [ ] No auto-playing video or audio without user control
- [ ] No content that flashes more than 3 times per second

### Step 4: Summary

End the audit with:
```
## Accessibility Audit Summary
- **PASS**: X checks
- **FAIL**: X checks
- **N/A**: X checks
- **WCAG Level**: AA / Partial AA / Below AA
- **Top Priority Fixes**: (list the 3 most impactful FAIL items)
```

---

## When to Apply

Reference these guidelines when:
- Running an accessibility audit on a codebase
- Reviewing UI code for accessibility (WCAG compliance)
- Implementing forms and interactions
- Ensuring keyboard navigation works
- Adding ARIA labels and live regions
- Optimizing image loading and layout stability

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Accessibility | CRITICAL | `a11y-` |
| 2 | Forms | HIGH | `form-` |
| 3 | Animation & Motion | CRITICAL | `motion-` |
| 4 | Performance & UX | MEDIUM | `perf-` |

## Quick Reference

### 1. Accessibility (CRITICAL)

- `a11y-semantic-html` - Use semantic HTML elements
- `a11y-heading-hierarchy` - Maintain proper heading hierarchy
- `a11y-screen-reader` - Optimize for screen reader compatibility
- `a11y-skip-links` - Provide skip links for navigation
- `a11y-keyboard-nav` - Ensure full keyboard navigation
- `a11y-focus-management` - Manage keyboard focus properly
- `a11y-aria-labels` - Add ARIA labels to interactive elements
- `a11y-color-contrast` - Ensure sufficient color contrast (WCAG AA)
- `a11y-alt-text` - Provide meaningful alt text for images
- `a11y-error-messages` - Make error messages accessible
- `a11y-form-labels` - Associate labels with form inputs
- `a11y-live-regions` - Announce dynamic content to screen readers

### 2. Forms (HIGH)

- `form-autocomplete` - Use autocomplete attributes
- `form-input-types` - Use correct input types
- `form-error-display` - Display form errors clearly
- `form-validation-ux` - Design user-friendly validation
- `form-inline-validation` - Implement smart inline validation
- `form-multi-step` - Design effective multi-step forms
- `form-placeholder-usage` - Use placeholders appropriately
- `form-submit-feedback` - Provide clear submission feedback

### 3. Animation & Motion (CRITICAL)

- `motion-reduced` - Respect prefers-reduced-motion (WCAG AAA)

### 4. Performance & UX (MEDIUM)

- `perf-image-loading` - Optimize image loading for UX
- `perf-layout-stability` - Prevent cumulative layout shift

## Essential Guidelines

### Semantic HTML

```tsx
// ❌ Div soup
<div className="header"><div className="nav"><div onClick={handleClick}>Home</div></div></div>

// ✅ Semantic HTML
<header><nav aria-label="Main"><a href="/">Home</a></nav></header>
<main><article><h1>Title</h1><p>Content</p></article></main>
```

### Form Accessibility

```tsx
<label htmlFor="email">Email <span aria-hidden="true">*</span></label>
<input id="email" type="email" required aria-required="true" aria-describedby="email-error" autoComplete="email" />
{error && <p id="email-error" role="alert">{error}</p>}
```

### Reduced Motion

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

## How to Use

Read individual rule files for detailed explanations:

```
rules/a11y-semantic-html.md
rules/form-autocomplete.md
rules/motion-reduced.md
rules/perf-image-loading.md
```

## References

- [WCAG 2.2 Quick Reference](https://www.w3.org/WAI/WCAG22/quickref/)
- [WAI-ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [web.dev Accessibility](https://web.dev/accessibility/)
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- [The A11Y Project](https://a11yproject.com/)

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
