---
title: Associate Labels with Form Inputs
impact: CRITICAL
impactDescription: "WCAG 2.1 Level A - Info and Relationships"
tags: accessibility, forms, labels, inputs
---

## Associate Labels with Form Inputs

**Impact: CRITICAL (WCAG 2.1 Level A - Info and Relationships)**

Every form input must have an associated label that clearly identifies its purpose. Labels are essential for screen reader users and improve usability for all users.

## Incorrect

```html
<!-- ❌ Bad: Missing or improper labels -->
<form>
  <!-- No label at all -->
  <input type="text" placeholder="Enter your name">

  <!-- Placeholder as label (disappears on input) -->
  <input type="email" placeholder="Email address">

  <!-- Label not associated with input -->
  <div>
    <span>Password</span>
    <input type="password">
  </div>

  <!-- Mismatched for/id -->
  <label for="username">Username</label>
  <input type="text" id="user-name">

  <!-- Non-label element used -->
  <p>Phone Number</p>
  <input type="tel">

  <!-- Label too far from input -->
  <label for="address">Address</label>
  <p>Enter your full mailing address including zip code</p>
  <div class="spacer"></div>
  <input type="text" id="address">
</form>
```

**Problems:**
- Screen readers announce nothing when inputs lack associated labels
- Placeholders vanish on focus, leaving users with no context
- Mismatched `for`/`id` breaks the programmatic association
- Non-`<label>` elements do not provide click-to-focus behavior
- Voice control users cannot target inputs without proper labels

## Correct

```html
<!-- ✅ Good: Properly associated labels -->
<form>
  <!-- Explicit label association with for/id -->
  <div class="form-group">
    <label for="full-name">Full Name</label>
    <input type="text" id="full-name" name="fullName" autocomplete="name">
  </div>

  <!-- Implicit label (input inside label) -->
  <label class="form-group">
    Email Address
    <input type="email" name="email" autocomplete="email">
  </label>

  <!-- Label with required indicator -->
  <div class="form-group">
    <label for="password">
      Password
      <span aria-hidden="true">*</span>
      <span class="visually-hidden">(required)</span>
    </label>
    <input type="password" id="password" name="password"
           required aria-required="true"
           aria-describedby="password-requirements">
    <p id="password-requirements" class="helper-text">
      Must be at least 8 characters with one number
    </p>
  </div>

  <!-- Visually hidden label (when design requires no visible label) -->
  <div class="form-group search-box">
    <label for="search" class="visually-hidden">Search products</label>
    <input type="search" id="search" name="search"
           placeholder="Search..." autocomplete="off">
    <button type="submit" aria-label="Submit search">
      <svg aria-hidden="true"><!-- search icon --></svg>
    </button>
  </div>

  <!-- Fieldset for grouped inputs -->
  <fieldset>
    <legend>Shipping Address</legend>

    <div class="form-group">
      <label for="street">Street Address</label>
      <input type="text" id="street" name="street" autocomplete="street-address">
    </div>

    <div class="form-group">
      <label for="city">City</label>
      <input type="text" id="city" name="city" autocomplete="address-level2">
    </div>
  </fieldset>

  <!-- Radio buttons with fieldset/legend -->
  <fieldset>
    <legend>Preferred Contact Method</legend>

    <div class="radio-group">
      <input type="radio" id="contact-email" name="contact" value="email">
      <label for="contact-email">Email</label>
    </div>

    <div class="radio-group">
      <input type="radio" id="contact-phone" name="contact" value="phone">
      <label for="contact-phone">Phone</label>
    </div>

    <div class="radio-group">
      <input type="radio" id="contact-sms" name="contact" value="sms">
      <label for="contact-sms">Text Message</label>
    </div>
  </fieldset>

  <!-- Checkbox with label -->
  <div class="checkbox-group">
    <input type="checkbox" id="newsletter" name="newsletter">
    <label for="newsletter">Subscribe to our newsletter</label>
  </div>

  <!-- Multiple related inputs with single label -->
  <fieldset>
    <legend>Date of Birth</legend>
    <div class="date-inputs">
      <div>
        <label for="dob-month">Month</label>
        <select id="dob-month" name="dobMonth">
          <option value="">--</option>
          <option value="1">January</option>
          <!-- ... -->
        </select>
      </div>
      <div>
        <label for="dob-day">Day</label>
        <input type="text" id="dob-day" name="dobDay" maxlength="2">
      </div>
      <div>
        <label for="dob-year">Year</label>
        <input type="text" id="dob-year" name="dobYear" maxlength="4">
      </div>
    </div>
  </fieldset>
</form>

<style>
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
```

**Benefits:**
- Screen readers announce the label when an input receives focus
- Clicking a label focuses its associated input, increasing the click target
- Voice control users can identify and interact with inputs by label text
- Grouped inputs with `<fieldset>`/`<legend>` provide clear context for related fields
- Meets WCAG requirements for programmatically associated labels

Reference: [WCAG 1.3.1 Info and Relationships](https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html)
