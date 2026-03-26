---
title: Display Form Errors Clearly
impact: HIGH
impactDescription: "WCAG 2.1 Level A - Error identification"
tags: forms, errors, validation, accessibility, ux
---

## Display Form Errors Clearly

**Impact: HIGH (WCAG 2.1 Level A - Error identification)**

Display form errors clearly and consistently to help users identify and correct mistakes. Error messages should be visible, associated with their fields, and provide actionable guidance.

## Incorrect

```html
<!-- ❌ Bad: Poor error display -->
<form>
  <!-- Error message far from input -->
  <div class="errors">
    <p>Email is invalid</p>
    <p>Password is required</p>
  </div>

  <input type="text" name="email" value="bad-email">
  <input type="password" name="password">

  <!-- Generic JavaScript alert -->
  <script>
    function validate() {
      alert('There are errors in your form');
    }
  </script>

  <!-- Error shown only by color change -->
  <input type="text" style="border-color: red;" placeholder="Enter name">

  <!-- Tooltip-only errors (disappear quickly) -->
  <input type="email" title="Please enter a valid email">

  <!-- Clearing the field on error -->
  <input type="text" id="phone" oninvalid="this.value=''">
</form>
```

**Problems:**
- Error messages far from their fields make it hard to identify which input has the problem
- JavaScript `alert()` provides no context about specific fields
- Color-only error indicators are invisible to color-blind users
- Tooltip-only errors are not persistent and may not be perceived
- Clearing valid input on error forces users to retype everything

## Correct

```html
<!-- ✅ Good: Clear, accessible error display -->
<form id="contact-form" novalidate>
  <!-- Error summary at top of form -->
  <div id="error-summary"
       role="alert"
       class="error-summary"
       hidden>
    <h2>Please correct the following errors:</h2>
    <ul id="error-list">
      <!-- Populated by JavaScript -->
    </ul>
  </div>

  <div class="form-group" id="email-group">
    <label for="email">
      Email Address
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-describedby="email-hint email-error"
           aria-invalid="false">
    <p id="email-hint" class="hint">Enter your work email address</p>
    <p id="email-error" class="field-error" hidden>
      <span class="error-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 16 16">
          <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M8 4v5M8 11v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="error-text"></span>
    </p>
  </div>

  <div class="form-group" id="phone-group">
    <label for="phone">Phone Number</label>
    <input type="tel"
           id="phone"
           name="phone"
           aria-describedby="phone-error"
           aria-invalid="false">
    <p id="phone-error" class="field-error" hidden>
      <span class="error-icon" aria-hidden="true">
        <svg width="16" height="16" viewBox="0 0 16 16">
          <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
          <path d="M8 4v5M8 11v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
      <span class="error-text"></span>
    </p>
  </div>

  <div class="form-group" id="message-group">
    <label for="message">
      Message
      <span class="required" aria-hidden="true">*</span>
    </label>
    <textarea id="message"
              name="message"
              required
              aria-required="true"
              aria-describedby="message-counter message-error"
              maxlength="500"
              rows="4"></textarea>
    <div class="field-footer">
      <p id="message-counter" class="character-counter">0/500 characters</p>
      <p id="message-error" class="field-error" hidden>
        <span class="error-icon" aria-hidden="true">
          <svg width="16" height="16" viewBox="0 0 16 16">
            <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M8 4v5M8 11v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </span>
        <span class="error-text"></span>
      </p>
    </div>
  </div>

  <button type="submit">Send Message</button>
</form>

<script>
const form = document.getElementById('contact-form');
const errorSummary = document.getElementById('error-summary');
const errorList = document.getElementById('error-list');

form.addEventListener('submit', function(e) {
  e.preventDefault();
  clearAllErrors();

  const errors = validateForm();

  if (errors.length > 0) {
    displayErrors(errors);
  } else {
    // submit form logic here
  }
});

function validateForm() {
  const errors = [];

  // Email validation
  const email = document.getElementById('email');
  if (!email.value) {
    errors.push({ field: 'email', message: 'Email address is required' });
  } else if (!isValidEmail(email.value)) {
    errors.push({ field: 'email', message: 'Enter a valid email address, like name@company.com' });
  }

  // Phone validation (optional but must be valid if provided)
  const phone = document.getElementById('phone');
  if (phone.value && !isValidPhone(phone.value)) {
    errors.push({ field: 'phone', message: 'Enter a valid phone number, like (555) 123-4567' });
  }

  // Message validation
  const message = document.getElementById('message');
  if (!message.value.trim()) {
    errors.push({ field: 'message', message: 'Message is required' });
  } else if (message.value.length < 10) {
    errors.push({ field: 'message', message: 'Message must be at least 10 characters' });
  }

  return errors;
}

function displayErrors(errors) {
  // Build error summary
  errorList.innerHTML = errors.map(error =>
    `<li><a href="#${error.field}">${error.message}</a></li>`
  ).join('');

  errorSummary.hidden = false;

  // Display inline errors
  errors.forEach(error => {
    const input = document.getElementById(error.field);
    const errorEl = document.getElementById(`${error.field}-error`);
    const errorText = errorEl.querySelector('.error-text');

    input.setAttribute('aria-invalid', 'true');
    errorText.textContent = error.message;
    errorEl.hidden = false;

    // Add error class to form group
    document.getElementById(`${error.field}-group`).classList.add('has-error');
  });

  // Focus error summary (or first error field)
  errorSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
  errorList.querySelector('a').focus();
}

function clearAllErrors() {
  errorSummary.hidden = true;
  errorList.innerHTML = '';

  document.querySelectorAll('.field-error').forEach(el => {
    el.hidden = true;
  });

  document.querySelectorAll('[aria-invalid="true"]').forEach(el => {
    el.setAttribute('aria-invalid', 'false');
  });

  document.querySelectorAll('.has-error').forEach(el => {
    el.classList.remove('has-error');
  });
}
</script>

<style>
.error-summary {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.error-summary h2 {
  color: #991b1b;
  font-size: 1rem;
  margin: 0 0 0.5rem;
}

.error-summary ul {
  margin: 0;
  padding-left: 1.5rem;
}

.error-summary a {
  color: #dc2626;
}

.field-error {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.has-error input,
.has-error textarea {
  border-color: #dc2626;
  border-width: 2px;
}

.has-error input:focus,
.has-error textarea:focus {
  outline-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
}
</style>
```

**Benefits:**
- Error summary at the top provides an overview with links to each problem field
- Inline errors next to each field make identification immediate
- Icons and text alongside color ensure errors are perceivable by all users
- ARIA attributes link errors programmatically for screen readers
- User input is preserved on error so nothing needs to be retyped

Reference: [WCAG 3.3.1 Error Identification](https://www.w3.org/WAI/WCAG21/Understanding/error-identification.html)
