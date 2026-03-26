---
title: Design Effective Multi-Step Forms
impact: MEDIUM
impactDescription: "Increases completion rates by 15-30%"
tags: forms, multi-step, ux, progress-indication
---

## Design Effective Multi-Step Forms

**Impact: MEDIUM (Increases completion rates by 15-30%)**

Design multi-step forms that are easy to navigate, maintain context, and don't overwhelm users. Break complex forms into logical, manageable steps.

## Incorrect

```html
<!-- ❌ Bad: Poor multi-step form -->
<form>
  <!-- No progress indication -->
  <!-- No step numbers or titles -->

  <div class="step" id="step1">
    <input type="text" placeholder="Name">
    <input type="email" placeholder="Email">
    <button type="button" onclick="nextStep()">Next</button>
  </div>

  <div class="step" id="step2" hidden>
    <input type="text" placeholder="Address">
    <!-- No back button -->
    <button type="button" onclick="nextStep()">Next</button>
  </div>

  <div class="step" id="step3" hidden>
    <!-- No summary of previous steps -->
    <button type="submit">Submit</button>
  </div>
</form>

<script>
let step = 1;
function nextStep() {
  // Doesn't validate before proceeding
  document.getElementById(`step${step}`).hidden = true;
  step++;
  document.getElementById(`step${step}`).hidden = false;
  // Doesn't save progress
  // Doesn't announce step change to screen readers
}
</script>
```

**Problems:**
- No progress indicator, so users do not know how many steps remain
- No back navigation to return to previous steps
- No validation before advancing, allowing incomplete data
- No screen reader announcements for step changes
- No data persistence if the user navigates away
- No review step before final submission

## Correct

```html
<!-- ✅ Good: Accessible multi-step form -->
<div class="multi-step-form">
  <!-- Progress indicator -->
  <nav aria-label="Form progress">
    <ol class="progress-steps" role="list">
      <li class="step-indicator current"
          aria-current="step">
        <span class="step-number">1</span>
        <span class="step-label">Personal Info</span>
      </li>
      <li class="step-indicator">
        <span class="step-number">2</span>
        <span class="step-label">Address</span>
      </li>
      <li class="step-indicator">
        <span class="step-number">3</span>
        <span class="step-label">Review</span>
      </li>
    </ol>
    <div class="progress-bar">
      <div class="progress-fill" style="width: 33%;"
           role="progressbar"
           aria-valuenow="1"
           aria-valuemin="1"
           aria-valuemax="3"
           aria-label="Step 1 of 3"></div>
    </div>
  </nav>

  <!-- Status announcement for screen readers -->
  <div id="step-status"
       role="status"
       aria-live="polite"
       class="visually-hidden">
  </div>

  <form id="registration-form" novalidate>
    <!-- Step 1: Personal Information -->
    <fieldset id="step-1" class="form-step">
      <legend tabindex="-1">
        <span class="step-title">Step 1 of 3: Personal Information</span>
      </legend>

      <div class="form-group">
        <label for="full-name">Full Name *</label>
        <input type="text"
               id="full-name"
               name="fullName"
               required
               aria-required="true"
               autocomplete="name">
        <p id="name-error" class="error" hidden></p>
      </div>

      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email"
               id="email"
               name="email"
               required
               aria-required="true"
               autocomplete="email">
        <p id="email-error" class="error" hidden></p>
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel"
               id="phone"
               name="phone"
               autocomplete="tel">
      </div>

      <div class="form-actions">
        <button type="button"
                class="btn-next"
                onclick="goToStep(2)">
          Continue to Address
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow right --></svg>
        </button>
      </div>
    </fieldset>

    <!-- Step 2: Address -->
    <fieldset id="step-2" class="form-step" hidden>
      <legend tabindex="-1">
        <span class="step-title">Step 2 of 3: Address</span>
      </legend>

      <div class="form-group">
        <label for="street">Street Address *</label>
        <input type="text"
               id="street"
               name="street"
               required
               aria-required="true"
               autocomplete="street-address">
        <p id="street-error" class="error" hidden></p>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="city">City *</label>
          <input type="text"
                 id="city"
                 name="city"
                 required
                 aria-required="true"
                 autocomplete="address-level2">
        </div>

        <div class="form-group">
          <label for="state">State *</label>
          <select id="state"
                  name="state"
                  required
                  aria-required="true"
                  autocomplete="address-level1">
            <option value="">Select...</option>
            <option value="CA">California</option>
            <option value="NY">New York</option>
            <!-- more states -->
          </select>
        </div>

        <div class="form-group">
          <label for="zip">ZIP Code *</label>
          <input type="text"
                 id="zip"
                 name="zip"
                 required
                 aria-required="true"
                 autocomplete="postal-code"
                 inputmode="numeric"
                 pattern="[0-9]{5}">
        </div>
      </div>

      <div class="form-actions">
        <button type="button"
                class="btn-back"
                onclick="goToStep(1)">
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow left --></svg>
          Back
        </button>
        <button type="button"
                class="btn-next"
                onclick="goToStep(3)">
          Review Your Information
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow right --></svg>
        </button>
      </div>
    </fieldset>

    <!-- Step 3: Review -->
    <fieldset id="step-3" class="form-step" hidden>
      <legend tabindex="-1">
        <span class="step-title">Step 3 of 3: Review & Submit</span>
      </legend>

      <div class="review-section">
        <h3>Personal Information</h3>
        <dl class="review-list">
          <dt>Name</dt>
          <dd id="review-name"></dd>
          <dt>Email</dt>
          <dd id="review-email"></dd>
          <dt>Phone</dt>
          <dd id="review-phone"></dd>
        </dl>
        <button type="button"
                class="btn-edit"
                onclick="goToStep(1)">
          Edit Personal Info
        </button>
      </div>

      <div class="review-section">
        <h3>Address</h3>
        <dl class="review-list">
          <dt>Street</dt>
          <dd id="review-street"></dd>
          <dt>City, State ZIP</dt>
          <dd id="review-city-state-zip"></dd>
        </dl>
        <button type="button"
                class="btn-edit"
                onclick="goToStep(2)">
          Edit Address
        </button>
      </div>

      <div class="form-actions">
        <button type="button"
                class="btn-back"
                onclick="goToStep(2)">
          <svg aria-hidden="true" class="arrow-icon"><!-- arrow left --></svg>
          Back
        </button>
        <button type="submit" class="btn-submit">
          Submit Registration
        </button>
      </div>
    </fieldset>
  </form>
</div>

<script>
let currentStep = 1;
const totalSteps = 3;
const form = document.getElementById('registration-form');
const statusEl = document.getElementById('step-status');

function goToStep(newStep) {
  // Validate current step before proceeding forward
  if (newStep > currentStep && !validateStep(currentStep)) {
    return;
  }

  // Save current step data
  saveProgress();

  // Hide current step
  document.getElementById(`step-${currentStep}`).hidden = true;

  // Update progress indicators
  updateProgressIndicators(newStep);

  // Show new step
  currentStep = newStep;
  const newStepEl = document.getElementById(`step-${newStep}`);
  newStepEl.hidden = false;

  // If review step, populate summary
  if (newStep === 3) {
    populateReview();
  }

  // Focus management
  newStepEl.querySelector('legend').focus();

  // Announce step change
  const stepTitle = newStepEl.querySelector('.step-title').textContent;
  statusEl.textContent = `Now on ${stepTitle}`;

  // Scroll to top of form
  newStepEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function validateStep(step) {
  const stepEl = document.getElementById(`step-${step}`);
  const requiredFields = stepEl.querySelectorAll('[required]');
  let isValid = true;
  let firstError = null;

  requiredFields.forEach(field => {
    if (!field.validity.valid) {
      isValid = false;
      showFieldError(field);
      if (!firstError) firstError = field;
    } else {
      clearFieldError(field);
    }
  });

  if (firstError) {
    firstError.focus();
    statusEl.textContent = 'Please correct the errors before continuing.';
  }

  return isValid;
}

function updateProgressIndicators(newStep) {
  const indicators = document.querySelectorAll('.step-indicator');
  indicators.forEach((indicator, index) => {
    const stepNum = index + 1;
    indicator.classList.toggle('completed', stepNum < newStep);
    indicator.classList.toggle('current', stepNum === newStep);
    if (stepNum === newStep) {
      indicator.setAttribute('aria-current', 'step');
    } else {
      indicator.removeAttribute('aria-current');
    }
  });

  // Update progress bar
  const progressFill = document.querySelector('.progress-fill');
  const percentage = (newStep / totalSteps) * 100;
  progressFill.style.width = `${percentage}%`;
  progressFill.setAttribute('aria-valuenow', newStep);
}

function saveProgress() {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());
  localStorage.setItem('registration-progress', JSON.stringify({
    step: currentStep,
    data: data
  }));
}

function populateReview() {
  document.getElementById('review-name').textContent =
    document.getElementById('full-name').value || 'Not provided';
  document.getElementById('review-email').textContent =
    document.getElementById('email').value;
  document.getElementById('review-phone').textContent =
    document.getElementById('phone').value || 'Not provided';
  document.getElementById('review-street').textContent =
    document.getElementById('street').value;
  document.getElementById('review-city-state-zip').textContent =
    `${document.getElementById('city').value}, ${document.getElementById('state').value} ${document.getElementById('zip').value}`;
}

// Restore progress on page load
window.addEventListener('load', () => {
  const saved = localStorage.getItem('registration-progress');
  if (saved) {
    const { step, data } = JSON.parse(saved);
    // Populate form fields
    Object.entries(data).forEach(([name, value]) => {
      const field = form.elements[name];
      if (field) field.value = value;
    });
    // Go to saved step
    if (step > 1) goToStep(step);
  }
});
</script>
```

**Benefits:**
- Progress indicator shows users where they are and how much is left
- Back navigation and edit buttons let users revisit and correct previous steps
- Per-step validation catches errors early before advancing
- Screen reader announcements communicate step changes
- Data persistence via localStorage preserves progress across page reloads
- Review step lets users verify all information before final submission

Reference: [web.dev - Multi-step Forms](https://web.dev/learn/forms/auto)
