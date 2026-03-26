---
title: Provide Accessible Error Messages
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Error identification and suggestions"
tags: accessibility, forms, errors, aria, validation
---

## Provide Accessible Error Messages

**Impact: CRITICAL (WCAG 2.1 Level A - Error identification and suggestions)**

Error messages must be perceivable, understandable, and programmatically associated with their related form fields. Users should be able to identify and correct errors easily.

## Incorrect

```html
<!-- ❌ Bad: Poor error handling -->
<form>
  <!-- Error only shown visually with color -->
  <div class="form-group error">
    <label for="email">Email</label>
    <input type="email" id="email" style="border-color: red;">
  </div>

  <!-- Error message not associated with input -->
  <div class="form-group">
    <label for="password">Password</label>
    <input type="password" id="password">
  </div>
  <div class="error-text">Password must be 8 characters</div>

  <!-- Generic error message -->
  <div class="error-message">
    Please correct the errors above.
  </div>

  <!-- Error not announced to screen readers -->
  <div class="form-group">
    <label for="phone">Phone</label>
    <input type="tel" id="phone">
    <span class="error">Invalid phone number</span>
  </div>
</form>

<script>
// Anti-pattern: Alert boxes for errors
function validateForm() {
  if (!isValid) {
    alert('Please fix the errors in the form');
  }
}
</script>
```

**Problems:**
- Color-only error indicators are invisible to color-blind users
- Error messages not linked to inputs via `aria-describedby`
- Generic messages do not help users identify or fix specific errors
- Screen readers cannot discover errors that lack ARIA roles or live regions
- JavaScript `alert()` interrupts the user and provides no field context

## Correct

```html
<!-- ✅ Good: Accessible error handling -->
<form novalidate aria-describedby="form-error-summary">
  <!-- Error summary at top of form -->
  <div id="form-error-summary"
       class="error-summary"
       role="alert"
       tabindex="-1"
       hidden>
    <h2>There were 2 errors with your submission</h2>
    <ul>
      <li><a href="#email">Email address is required</a></li>
      <li><a href="#password">Password must be at least 8 characters</a></li>
    </ul>
  </div>

  <!-- Field with error - complete implementation -->
  <div class="form-group">
    <label for="email">
      Email Address
      <span aria-hidden="true">*</span>
    </label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-invalid="true"
           aria-describedby="email-error email-hint">
    <p id="email-hint" class="hint-text">
      We'll never share your email with anyone
    </p>
    <p id="email-error" class="error-message" role="alert">
      <svg aria-hidden="true" class="error-icon"><!-- error icon --></svg>
      <span>Please enter a valid email address (e.g., name@example.com)</span>
    </p>
  </div>

  <!-- Field with error - visual and programmatic indicators -->
  <div class="form-group has-error">
    <label for="password">
      Password
      <span aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-invalid="true"
           aria-describedby="password-error password-requirements">
    <p id="password-requirements" class="hint-text">
      Must contain at least 8 characters, one uppercase, one number
    </p>
    <p id="password-error" class="error-message" role="alert">
      <svg aria-hidden="true" class="error-icon"><!-- error icon --></svg>
      <span>Password is too short. Please use at least 8 characters.</span>
    </p>
  </div>

  <!-- Success state after correction -->
  <div class="form-group has-success">
    <label for="username">Username</label>
    <input type="text"
           id="username"
           name="username"
           aria-invalid="false"
           aria-describedby="username-success">
    <p id="username-success" class="success-message">
      <svg aria-hidden="true" class="success-icon"><!-- checkmark --></svg>
      <span>Username is available</span>
    </p>
  </div>
</form>

<script>
function showError(input, message) {
  const formGroup = input.closest('.form-group');
  const errorElement = formGroup.querySelector('.error-message');

  // Set ARIA attributes
  input.setAttribute('aria-invalid', 'true');

  // Show error message
  errorElement.textContent = message;
  errorElement.hidden = false;

  // Move focus to input (for form submission errors)
  input.focus();
}

function clearError(input) {
  const formGroup = input.closest('.form-group');
  const errorElement = formGroup.querySelector('.error-message');

  input.setAttribute('aria-invalid', 'false');
  errorElement.hidden = true;
}

function showErrorSummary(errors) {
  const summary = document.getElementById('form-error-summary');
  const list = summary.querySelector('ul');

  // Build error list
  list.innerHTML = errors.map(error =>
    `<li><a href="#${error.fieldId}">${error.message}</a></li>`
  ).join('');

  // Update heading
  summary.querySelector('h2').textContent =
    `There ${errors.length === 1 ? 'was 1 error' : `were ${errors.length} errors`} with your submission`;

  // Show and focus summary
  summary.hidden = false;
  summary.focus();
}
</script>

<style>
.error-summary {
  background-color: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 4px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.error-summary:focus {
  outline: 3px solid #005fcc;
  outline-offset: 2px;
}

.error-message {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.has-error input {
  border-color: #dc2626;
  border-width: 2px;
}

.has-error input:focus {
  outline-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
}

.success-message {
  color: #059669;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>
```

**Benefits:**
- Errors are announced to screen readers via `role="alert"` live regions
- Each error is programmatically linked to its field with `aria-describedby`
- Error summary with links lets users jump directly to problem fields
- Visual indicators (icons, borders, text) work alongside color for all users
- Specific, actionable messages guide users to correct their input

Reference: [WCAG 3.3.1 Error Identification](https://www.w3.org/WAI/WCAG21/Understanding/error-identification.html)
