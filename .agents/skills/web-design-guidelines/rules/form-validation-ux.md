---
title: Design User-Friendly Form Validation
impact: HIGH
impactDescription: "Reduces form abandonment by 20-30%"
tags: forms, validation, ux, error-handling
---

## Design User-Friendly Form Validation

**Impact: HIGH (Reduces form abandonment by 20-30%)**

Design form validation that helps users succeed. Good validation provides clear feedback, prevents frustration, and guides users to correct errors efficiently.

## Incorrect

```html
<!-- ❌ Bad: Poor validation UX -->
<form onsubmit="return validateForm()">
  <input type="text" id="email" placeholder="Email">
  <input type="text" id="password" placeholder="Password">
  <button type="submit">Sign Up</button>
</form>

<script>
function validateForm() {
  // Only validates on submit
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;

  // Generic, unhelpful error message
  if (!email || !password) {
    alert('Please fill in all fields');
    return false;
  }

  // Regex-only validation with no feedback
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    alert('Invalid email');
    return false;
  }

  // Unclear requirements
  if (password.length < 8) {
    alert('Password too short');
    return false;
  }

  return true;
}
</script>

<style>
/* Error state with no guidance */
.error { border-color: red; }
</style>
```

**Problems:**
- Submit-only validation delays all feedback until the end
- JavaScript `alert()` provides no field-level context and interrupts the user
- Generic messages ("Invalid email") do not explain how to fix the error
- Password requirements are hidden until the user fails
- No ARIA attributes to communicate errors to screen readers

## Correct

```html
<!-- ✅ Good: User-friendly validation -->
<form id="signup-form" novalidate>
  <div class="form-group">
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
           autocomplete="email">
    <p id="email-hint" class="hint">We'll send your confirmation here</p>
    <p id="email-error" class="error-message" role="alert" hidden></p>
  </div>

  <div class="form-group">
    <label for="password">
      Password
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-describedby="password-requirements password-error"
           minlength="8"
           autocomplete="new-password">

    <!-- Show requirements upfront, not just on error -->
    <div id="password-requirements" class="requirements">
      <p>Password must contain:</p>
      <ul>
        <li id="req-length" class="requirement">
          <span class="icon" aria-hidden="true"></span>
          At least 8 characters
        </li>
        <li id="req-uppercase" class="requirement">
          <span class="icon" aria-hidden="true"></span>
          One uppercase letter
        </li>
        <li id="req-number" class="requirement">
          <span class="icon" aria-hidden="true"></span>
          One number
        </li>
      </ul>
    </div>
    <p id="password-error" class="error-message" role="alert" hidden></p>
  </div>

  <div class="form-group">
    <label for="confirm-password">
      Confirm Password
      <span class="required" aria-hidden="true">*</span>
    </label>
    <input type="password"
           id="confirm-password"
           name="confirmPassword"
           required
           aria-required="true"
           aria-describedby="confirm-error"
           autocomplete="new-password">
    <p id="confirm-error" class="error-message" role="alert" hidden></p>
  </div>

  <button type="submit" id="submit-btn">Create Account</button>
</form>

<script>
const form = document.getElementById('signup-form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm-password');

// Validate on blur (when leaving field)
emailInput.addEventListener('blur', validateEmail);
passwordInput.addEventListener('input', validatePassword);
confirmInput.addEventListener('blur', validateConfirmPassword);

// Also validate on submit
form.addEventListener('submit', function(e) {
  e.preventDefault();

  const isEmailValid = validateEmail();
  const isPasswordValid = validatePassword();
  const isConfirmValid = validateConfirmPassword();

  if (isEmailValid && isPasswordValid && isConfirmValid) {
    // submit form logic here
  } else {
    // Focus first invalid field
    const firstError = form.querySelector('[aria-invalid="true"]');
    if (firstError) firstError.focus();
  }
});

function validateEmail() {
  const email = emailInput.value.trim();
  const errorEl = document.getElementById('email-error');

  if (!email) {
    showError(emailInput, errorEl, 'Email address is required');
    return false;
  }

  if (!isValidEmail(email)) {
    showError(emailInput, errorEl, 'Please enter a valid email address (e.g., name@example.com)');
    return false;
  }

  clearError(emailInput, errorEl);
  return true;
}

function validatePassword() {
  const password = passwordInput.value;
  const requirements = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    number: /[0-9]/.test(password)
  };

  // Update requirement indicators in real-time
  updateRequirement('req-length', requirements.length);
  updateRequirement('req-uppercase', requirements.uppercase);
  updateRequirement('req-number', requirements.number);

  const allMet = Object.values(requirements).every(Boolean);

  if (allMet) {
    passwordInput.setAttribute('aria-invalid', 'false');
    return true;
  }

  passwordInput.setAttribute('aria-invalid', 'true');
  return false;
}

function validateConfirmPassword() {
  const password = passwordInput.value;
  const confirm = confirmInput.value;
  const errorEl = document.getElementById('confirm-error');

  if (!confirm) {
    showError(confirmInput, errorEl, 'Please confirm your password');
    return false;
  }

  if (confirm !== password) {
    showError(confirmInput, errorEl, 'Passwords do not match');
    return false;
  }

  clearError(confirmInput, errorEl);
  return true;
}

function updateRequirement(id, met) {
  const el = document.getElementById(id);
  el.classList.toggle('met', met);
  el.classList.toggle('unmet', !met);
}

function showError(input, errorEl, message) {
  input.setAttribute('aria-invalid', 'true');
  errorEl.textContent = message;
  errorEl.hidden = false;
}

function clearError(input, errorEl) {
  input.setAttribute('aria-invalid', 'false');
  errorEl.hidden = true;
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>

<style>
.requirement.met .icon::before { content: '✓'; color: green; }
.requirement.unmet .icon::before { content: '○'; color: #666; }
.error-message { color: #dc2626; }
</style>
```

**Benefits:**
- Blur validation provides feedback at the right moment without interrupting typing
- Password requirements are shown upfront so users know what is expected
- Real-time requirement indicators give positive feedback as criteria are met
- Specific, actionable error messages guide users to correct input
- Focus moves to the first invalid field on submit for efficient error correction

Reference: [web.dev - Best Practices for Form Validation](https://web.dev/learn/forms/validation)
