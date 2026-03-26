# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Accessibility (a11y)

**Impact:** CRITICAL
**Description:** WCAG 2.2 compliance patterns for inclusive web interfaces. Semantic HTML structure, heading hierarchy, keyboard navigation, focus management, ARIA labels, color contrast ratios, meaningful alt text, accessible error messages, form label associations, live regions for dynamic content, skip links, and screen reader optimization.

## 2. Forms (form)

**Impact:** HIGH
**Description:** Accessible and user-friendly form patterns. Autocomplete attributes for autofill, correct input types for mobile keyboards, clear error display with ARIA associations, user-friendly validation timing, inline validation with debounce, multi-step form progression, appropriate placeholder usage, and clear submission feedback.

## 3. Animation & Motion (motion)

**Impact:** CRITICAL
**Description:** Respecting user motion preferences. The prefers-reduced-motion media query detects users with vestibular disorders who need reduced or eliminated animations. WCAG 2.1 SC 2.3.3 (Level AAA) requires providing controls to disable non-essential animations.

## 4. Performance & UX (perf)

**Impact:** MEDIUM
**Description:** Image loading optimization and layout stability. Preventing Cumulative Layout Shift (CLS) with explicit dimensions, lazy loading below-fold images, responsive images with modern formats, skeleton placeholders, and font loading strategies that minimize visual disruption.
