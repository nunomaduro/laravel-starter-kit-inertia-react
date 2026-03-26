---
title: Implement Smart Inline Validation
impact: MEDIUM
impactDescription: "Reduces errors by 20-40%"
tags: forms, validation, ux, inline-validation
---

## Implement Smart Inline Validation

**Impact: MEDIUM (Reduces errors by 20-40%)**

Implement inline validation that provides immediate, contextual feedback as users complete form fields. Validate at the right moment to help without interrupting.

## Incorrect

```html
<!-- ❌ Bad: Aggressive inline validation -->
<form>
  <input type="email"
         id="email"
         oninput="validateEmail()"
         placeholder="Email">
  <span id="email-error"></span>
</form>

<script>
// Validates on every keystroke (too aggressive)
function validateEmail() {
  const email = document.getElementById('email').value;
  const error = document.getElementById('email-error');

  // Shows error immediately when user starts typing
  if (!email.includes('@')) {
    error.textContent = 'Invalid email!';
    error.style.color = 'red';
  } else {
    error.textContent = '';
  }
}
</script>

<!-- ❌ Bad: No inline validation (only on submit) -->
<form onsubmit="return validate()">
  <input type="text" name="username">
  <input type="password" name="password">
  <!-- User only finds out about errors after clicking submit -->
  <button type="submit">Sign Up</button>
</form>
```

**Problems:**
- Validating on every keystroke shows errors before the user has finished typing
- Submit-only validation delays feedback until the very end, causing frustration
- No ARIA attributes to communicate validation state to screen readers
- Error messages lack specificity and actionable guidance

## Correct

```html
<!-- ✅ Good: Balanced inline validation -->
<form id="signup-form" novalidate>
  <!-- Email with validation on blur and after correction -->
  <div class="form-group" data-validate="email">
    <label for="email">Email Address *</label>
    <input type="email"
           id="email"
           name="email"
           required
           aria-required="true"
           aria-describedby="email-hint email-feedback"
           autocomplete="email">
    <p id="email-hint" class="hint">We'll send your confirmation here</p>
    <div id="email-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <!-- Username with async validation -->
  <div class="form-group" data-validate="username">
    <label for="username">Username *</label>
    <input type="text"
           id="username"
           name="username"
           required
           aria-required="true"
           aria-describedby="username-hint username-feedback"
           pattern="^[a-zA-Z0-9_]{3,20}$"
           autocomplete="username">
    <p id="username-hint" class="hint">3-20 characters, letters, numbers, underscores</p>
    <div id="username-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <!-- Password with real-time strength indicator -->
  <div class="form-group" data-validate="password">
    <label for="password">Password *</label>
    <input type="password"
           id="password"
           name="password"
           required
           aria-required="true"
           aria-describedby="password-requirements password-feedback"
           minlength="8"
           autocomplete="new-password">

    <div id="password-requirements" class="requirements">
      <span class="requirements-label">Requirements:</span>
      <ul class="requirements-list">
        <li data-requirement="length">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">At least 8 characters</span>
        </li>
        <li data-requirement="uppercase">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">One uppercase letter</span>
        </li>
        <li data-requirement="lowercase">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">One lowercase letter</span>
        </li>
        <li data-requirement="number">
          <span class="req-icon" aria-hidden="true"></span>
          <span class="req-text">One number</span>
        </li>
      </ul>
    </div>

    <div class="password-strength">
      <div class="strength-bar">
        <div class="strength-fill" id="strength-fill"></div>
      </div>
      <span id="strength-text" class="strength-text"></span>
    </div>

    <div id="password-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <!-- Confirm password with match validation -->
  <div class="form-group" data-validate="confirmPassword">
    <label for="confirm-password">Confirm Password *</label>
    <input type="password"
           id="confirm-password"
           name="confirmPassword"
           required
           aria-required="true"
           aria-describedby="confirm-feedback"
           autocomplete="new-password">
    <div id="confirm-feedback"
         class="feedback"
         role="status"
         aria-live="polite"></div>
  </div>

  <button type="submit">Create Account</button>
</form>

<script>
// Validation timing configuration
const validationConfig = {
  email: { on: 'blur', revalidate: 'input' },
  username: { on: 'blur', revalidate: 'input', debounce: 500 },
  password: { on: 'input', immediate: true },
  confirmPassword: { on: 'blur', revalidate: 'input' }
};

// Track if field has been touched
const touchedFields = new Set();

// Debounce utility
function debounce(fn, delay) {
  let timeoutId;
  return function(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Email validation
const emailInput = document.getElementById('email');
emailInput.addEventListener('blur', () => {
  touchedFields.add('email');
  validateEmail();
});
emailInput.addEventListener('input', () => {
  if (touchedFields.has('email')) {
    validateEmail();
  }
});

function validateEmail() {
  const email = emailInput.value.trim();
  const feedback = document.getElementById('email-feedback');

  if (!email) {
    showError(emailInput, feedback, 'Email address is required');
    return false;
  }

  if (!isValidEmail(email)) {
    showError(emailInput, feedback, 'Please enter a valid email (e.g., name@example.com)');
    return false;
  }

  showSuccess(emailInput, feedback, 'Email looks good');
  return true;
}

// Username validation with async availability check
const usernameInput = document.getElementById('username');
const checkUsername = debounce(validateUsername, 500);

usernameInput.addEventListener('blur', () => {
  touchedFields.add('username');
  validateUsername();
});
usernameInput.addEventListener('input', () => {
  if (touchedFields.has('username')) {
    checkUsername();
  }
});

async function validateUsername() {
  const username = usernameInput.value.trim();
  const feedback = document.getElementById('username-feedback');

  if (!username) {
    showError(usernameInput, feedback, 'Username is required');
    return false;
  }

  if (username.length < 3) {
    showError(usernameInput, feedback, 'Username must be at least 3 characters');
    return false;
  }

  if (!/^[a-zA-Z0-9_]+$/.test(username)) {
    showError(usernameInput, feedback, 'Only letters, numbers, and underscores allowed');
    return false;
  }

  // Check availability
  showPending(usernameInput, feedback, 'Checking availability...');

  try {
    const response = await fetch(`/api/check-username?username=${username}`);
    const { available } = await response.json();

    if (available) {
      showSuccess(usernameInput, feedback, 'Username is available');
      return true;
    } else {
      showError(usernameInput, feedback, 'Username is already taken');
      return false;
    }
  } catch (error) {
    showError(usernameInput, feedback, 'Could not check availability');
    return false;
  }
}

// Password validation with real-time feedback
const passwordInput = document.getElementById('password');
passwordInput.addEventListener('input', validatePassword);

function validatePassword() {
  const password = passwordInput.value;
  const requirements = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password)
  };

  // Update requirement indicators
  Object.entries(requirements).forEach(([req, met]) => {
    const el = document.querySelector(`[data-requirement="${req}"]`);
    el.classList.toggle('met', met);
    el.classList.toggle('unmet', !met);
  });

  // Calculate and show strength
  const strength = Object.values(requirements).filter(Boolean).length;
  updateStrengthIndicator(strength);

  // Only show feedback after user has typed something
  if (password.length > 0) {
    touchedFields.add('password');
  }

  return Object.values(requirements).every(Boolean);
}

function updateStrengthIndicator(strength) {
  const fill = document.getElementById('strength-fill');
  const text = document.getElementById('strength-text');

  const levels = [
    { label: '', color: '', width: '0%' },
    { label: 'Weak', color: '#dc2626', width: '25%' },
    { label: 'Fair', color: '#f59e0b', width: '50%' },
    { label: 'Good', color: '#3b82f6', width: '75%' },
    { label: 'Strong', color: '#22c55e', width: '100%' }
  ];

  const level = levels[strength];
  fill.style.width = level.width;
  fill.style.backgroundColor = level.color;
  text.textContent = level.label;
  text.style.color = level.color;
}

// Confirm password
const confirmInput = document.getElementById('confirm-password');
confirmInput.addEventListener('blur', () => {
  touchedFields.add('confirmPassword');
  validateConfirm();
});
confirmInput.addEventListener('input', () => {
  if (touchedFields.has('confirmPassword')) {
    validateConfirm();
  }
});

function validateConfirm() {
  const confirm = confirmInput.value;
  const password = passwordInput.value;
  const feedback = document.getElementById('confirm-feedback');

  if (!confirm) {
    showError(confirmInput, feedback, 'Please confirm your password');
    return false;
  }

  if (confirm !== password) {
    showError(confirmInput, feedback, 'Passwords do not match');
    return false;
  }

  showSuccess(confirmInput, feedback, 'Passwords match');
  return true;
}

// Feedback display helpers
function showError(input, feedback, message) {
  input.setAttribute('aria-invalid', 'true');
  feedback.className = 'feedback error';
  feedback.innerHTML = `<span class="icon">&#10005;</span> ${message}`;
}

function showSuccess(input, feedback, message) {
  input.setAttribute('aria-invalid', 'false');
  feedback.className = 'feedback success';
  feedback.innerHTML = `<span class="icon">&#10003;</span> ${message}`;
}

function showPending(input, feedback, message) {
  input.removeAttribute('aria-invalid');
  feedback.className = 'feedback pending';
  feedback.innerHTML = `<span class="spinner"></span> ${message}`;
}
</script>

<style>
.feedback.error { color: #dc2626; }
.feedback.success { color: #22c55e; }
.feedback.pending { color: #6b7280; }
.requirements-list li.met { color: #22c55e; }
.requirements-list li.unmet { color: #6b7280; }
</style>
```

**Benefits:**
- Blur-first validation avoids interrupting users while they type
- Revalidation on input clears errors as soon as the user corrects them
- Password strength indicator provides real-time, positive feedback
- Async username check with debounce avoids overwhelming the server
- ARIA live regions announce validation results to screen readers

Reference: [web.dev - Best Practices for Form Validation](https://web.dev/learn/forms/validation)
