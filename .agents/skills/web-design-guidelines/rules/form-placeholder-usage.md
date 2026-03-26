---
title: Use Placeholders Appropriately
impact: MEDIUM
impactDescription: "Prevents accessibility and usability issues"
tags: forms, placeholders, labels, accessibility
---

## Use Placeholders Appropriately

**Impact: MEDIUM (Prevents accessibility and usability issues)**

Use placeholders appropriately as supplementary hints, never as replacements for labels. Placeholders have significant accessibility and usability limitations.

## Incorrect

```html
<!-- ❌ Bad: Placeholders as labels -->
<form>
  <!-- No visible labels - only placeholders -->
  <input type="text" placeholder="First Name">
  <input type="text" placeholder="Last Name">
  <input type="email" placeholder="Email Address">
  <input type="password" placeholder="Password (min 8 characters)">

  <!-- Required indicator in placeholder -->
  <input type="text" placeholder="Phone Number *">

  <!-- Critical information in placeholder -->
  <input type="text" placeholder="Enter date as MM/DD/YYYY">

  <!-- Placeholder that looks like entered text -->
  <input type="text"
         placeholder="John Smith"
         style="color: #333;">

  <!-- Long placeholder that gets truncated -->
  <input type="text"
         placeholder="Enter your full legal name as it appears on your government ID">
</form>

<style>
/* Anti-pattern: Hiding labels */
label { display: none; }
</style>
```

**Problems:**
- Placeholders vanish when users start typing, removing all context
- Users must remember what each field requires while entering data
- Default placeholder contrast often fails WCAG minimum requirements
- Some screen readers do not announce placeholder text
- Browser autofill may not identify fields without proper labels
- Users with cognitive disabilities struggle when instructions disappear

## Correct

```html
<!-- ✅ Good: Labels with optional placeholder hints -->
<form>
  <div class="form-group">
    <label for="first-name">First Name</label>
    <input type="text"
           id="first-name"
           name="firstName"
           autocomplete="given-name">
    <!-- No placeholder needed - label is clear -->
  </div>

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
           placeholder="name@example.com"
           autocomplete="email">
    <!-- Placeholder shows format example -->
  </div>

  <div class="form-group">
    <label for="phone">Phone Number</label>
    <input type="tel"
           id="phone"
           name="phone"
           placeholder="(555) 123-4567"
           aria-describedby="phone-hint"
           autocomplete="tel">
    <p id="phone-hint" class="hint">Include area code</p>
    <!-- Hint text for persistent instructions -->
  </div>

  <div class="form-group">
    <label for="date">Event Date</label>
    <input type="text"
           id="date"
           name="date"
           aria-describedby="date-format"
           placeholder="MM/DD/YYYY">
    <p id="date-format" class="hint">Format: MM/DD/YYYY</p>
    <!-- Important format info in persistent hint -->
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
           aria-describedby="password-requirements"
           minlength="8"
           autocomplete="new-password">
    <p id="password-requirements" class="hint">
      Must be at least 8 characters with one uppercase letter and one number
    </p>
    <!-- Requirements in persistent text, not placeholder -->
  </div>

  <div class="form-group">
    <label for="search">Search Products</label>
    <input type="search"
           id="search"
           name="search"
           placeholder="e.g., blue widget, SKU-1234"
           autocomplete="off">
    <!-- Placeholder shows example queries -->
  </div>

  <div class="form-group">
    <label for="bio">Bio</label>
    <textarea id="bio"
              name="bio"
              placeholder="Tell us about yourself..."
              aria-describedby="bio-hint"
              rows="4"></textarea>
    <p id="bio-hint" class="hint">Optional. Max 500 characters.</p>
  </div>
</form>

<style>
/* Placeholder styling - clearly different from entered text */
::placeholder {
  color: #9ca3af;
  opacity: 1; /* Firefox fix */
  font-style: italic;
}

/* Hint text is always visible */
.hint {
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* Never hide labels */
label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.25rem;
}
</style>
```

**Benefits:**
- Visible labels persist regardless of input state, providing constant context
- Persistent hint text via `aria-describedby` keeps format instructions available
- Placeholders serve as supplementary examples, not primary labels
- Screen readers announce both the label and the described-by hint text
- Browser autofill correctly identifies fields by their labels

Reference: [MDN Placeholder Attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/placeholder)
