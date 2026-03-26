---
title: Ensure Sufficient Color Contrast
impact: CRITICAL
impactDescription: "WCAG 2.1 Level AA - 4.5:1 for text, 3:1 for large text"
tags: accessibility, color, contrast, visual
---

## Ensure Sufficient Color Contrast

**Impact: CRITICAL (WCAG 2.1 Level AA - 4.5:1 for text, 3:1 for large text)**

Ensure sufficient color contrast between text and backgrounds to make content readable for users with low vision or color blindness.

## Incorrect

```css
/* ❌ Bad: Insufficient contrast ratios */
.hero-text {
  color: #999999; /* Gray text */
  background-color: #ffffff; /* White background */
  /* Contrast ratio: 2.85:1 - FAILS */
}

.subtle-text {
  color: #b3b3b3;
  background-color: #f5f5f5;
  /* Contrast ratio: 1.63:1 - FAILS */
}

.button-primary {
  color: #ffffff;
  background-color: #66b3ff; /* Light blue */
  /* Contrast ratio: 2.48:1 - FAILS */
}

.link {
  color: #6699ff; /* Light blue link on white */
  /* Contrast ratio: 3.03:1 - FAILS for normal text */
}

.placeholder {
  color: #cccccc; /* Placeholder text */
  /* Too light to read */
}
```

```html
<!-- ❌ Bad: Relying only on color -->
<p>Required fields are marked in <span style="color: red;">red</span>.</p>
<input type="text" style="border-color: red;">
```

**Problems:**
- Low contrast text is unreadable for users with low vision
- Color-only indicators are invisible to color-blind users
- Light placeholders fail WCAG minimum contrast requirements
- Users in bright environments or on low-quality screens cannot read the text

## Correct

```css
/* ✅ Good: WCAG compliant contrast ratios */
.hero-text {
  color: #595959; /* Darker gray */
  background-color: #ffffff;
  /* Contrast ratio: 7:1 - PASSES AAA */
}

.body-text {
  color: #333333;
  background-color: #ffffff;
  /* Contrast ratio: 12.63:1 - PASSES AAA */
}

.button-primary {
  color: #ffffff;
  background-color: #0066cc; /* Darker blue */
  /* Contrast ratio: 7.05:1 - PASSES AAA */
}

.link {
  color: #0055aa; /* Darker blue link */
  text-decoration: underline;
  /* Contrast ratio: 7.28:1 - PASSES AAA */
}

.link:hover,
.link:focus {
  color: #003366;
  text-decoration: none;
  background-color: #e6f0ff;
}

/* Large text can have lower contrast (3:1 minimum) */
.large-heading {
  font-size: 24px;
  font-weight: bold;
  color: #666666;
  background-color: #ffffff;
  /* Contrast ratio: 5.74:1 - PASSES AA for large text */
}

/* Input placeholder with sufficient contrast */
.input::placeholder {
  color: #757575;
  /* Contrast ratio: 4.6:1 - PASSES AA */
}
```

```html
<!-- ✅ Good: Color plus additional indicators -->
<p>Required fields are marked with an asterisk (*).</p>
<label for="email">
  Email <span aria-hidden="true">*</span>
  <span class="visually-hidden">required</span>
</label>
<input type="email" id="email" required aria-required="true">

<!-- Error state with icon and text, not just color -->
<div class="error-message" role="alert">
  <svg aria-hidden="true" class="error-icon"><!-- X icon --></svg>
  <span>Please enter a valid email address</span>
</div>
```

**Benefits:**
- Text is readable by users with low vision and color deficiencies
- Content remains accessible in bright sunlight or on low-quality screens
- Non-color indicators ensure information reaches all users
- Meets WCAG AA and AAA contrast requirements

Reference: [WCAG 1.4.3 Contrast (Minimum)](https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html)
