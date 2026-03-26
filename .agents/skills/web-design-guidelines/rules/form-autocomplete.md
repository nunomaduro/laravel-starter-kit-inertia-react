---
title: Use Autocomplete Attributes for Forms
impact: CRITICAL
impactDescription: "WCAG 2.1 Level AA - Identify input purpose"
tags: forms, autocomplete, accessibility, ux
---

## Use Autocomplete Attributes for Forms

**Impact: CRITICAL (WCAG 2.1 Level AA - Identify input purpose)**

The `autocomplete` attribute enables browsers and password managers to autofill forms correctly. Proper autocomplete improves user experience, reduces errors, and is required for WCAG 1.3.5 compliance.

## Incorrect

```tsx
// ❌ Bad: No autocomplete attributes
<form>
  <input type="text" name="name" />
  <input type="text" name="email" />
  <input type="password" name="password" />
</form>

// ❌ Bad: Autocomplete disabled (frustrating for users)
<input type="email" autoComplete="off" />
```

**Problems:**
- Browsers and password managers cannot autofill fields without autocomplete hints
- Users must manually type every field, increasing friction and errors
- Disabling autocomplete on login/address fields is user-hostile
- Fails WCAG 1.3.5 Identify Input Purpose compliance

## Correct

```tsx
// ✅ Good: Proper autocomplete attributes
<form>
  {/* Name fields */}
  <input type="text" name="name" autoComplete="name" />
  <input type="text" name="firstName" autoComplete="given-name" />
  <input type="text" name="lastName" autoComplete="family-name" />

  {/* Contact fields */}
  <input type="email" name="email" autoComplete="email" />
  <input type="tel" name="phone" autoComplete="tel" />

  {/* Address fields */}
  <input type="text" name="address" autoComplete="street-address" />
  <input type="text" name="city" autoComplete="address-level2" />
  <input type="text" name="state" autoComplete="address-level1" />
  <input type="text" name="zip" autoComplete="postal-code" />
  <input type="text" name="country" autoComplete="country-name" />

  {/* Payment fields */}
  <input type="text" name="ccName" autoComplete="cc-name" />
  <input type="text" name="ccNumber" autoComplete="cc-number" />
  <input type="text" name="ccExp" autoComplete="cc-exp" />
  <input type="text" name="ccCsc" autoComplete="cc-csc" />
</form>
```

**Benefits:**
- Faster form completion via browser and password manager autofill
- Fewer input errors from manual typing
- Better mobile experience with native keyboard hints
- Password manager integration for secure credential handling
- WCAG 1.3.5 compliance

## Login Form

```tsx
// ✅ Good: Login form with proper autocomplete
<form>
  <div>
    <label htmlFor="username">Username or Email</label>
    <input
      id="username"
      type="email"
      name="email"
      autoComplete="username"  // Tells password managers this is the identifier
    />
  </div>

  <div>
    <label htmlFor="password">Password</label>
    <input
      id="password"
      type="password"
      name="password"
      autoComplete="current-password"  // For existing password
    />
  </div>

  <button type="submit">Sign In</button>
</form>
```

## Registration Form

```tsx
// ✅ Good: Registration form
<form>
  <div>
    <label htmlFor="email">Email</label>
    <input
      id="email"
      type="email"
      name="email"
      autoComplete="email"
    />
  </div>

  <div>
    <label htmlFor="newPassword">Password</label>
    <input
      id="newPassword"
      type="password"
      name="password"
      autoComplete="new-password"  // For new password creation
    />
  </div>

  <div>
    <label htmlFor="confirmPassword">Confirm Password</label>
    <input
      id="confirmPassword"
      type="password"
      name="confirmPassword"
      autoComplete="new-password"
    />
  </div>

  <button type="submit">Create Account</button>
</form>
```

## One-Time Codes (OTP)

```tsx
// ✅ Good: OTP input
<div>
  <label htmlFor="otp">Verification Code</label>
  <input
    id="otp"
    type="text"
    inputMode="numeric"
    pattern="[0-9]*"
    autoComplete="one-time-code"  // Triggers SMS autofill on mobile
    maxLength={6}
  />
</div>
```

## Common Autocomplete Values

| Value | Use For |
|-------|---------|
| `name` | Full name |
| `given-name` | First name |
| `family-name` | Last name |
| `email` | Email address |
| `tel` | Full phone number |
| `username` | Username (login identifier) |
| `new-password` | New password (registration) |
| `current-password` | Existing password (login) |
| `one-time-code` | OTP/verification code |
| `street-address` | Full street address |
| `address-level1` | State/Province |
| `address-level2` | City |
| `postal-code` | ZIP/Postal code |
| `country-name` | Country name |
| `cc-name` | Cardholder name |
| `cc-number` | Credit card number |
| `cc-exp` | Card expiration (MM/YY) |
| `cc-csc` | Card security code |
| `bday` | Birthday |
| `organization` | Company name |

## Shipping vs Billing

```tsx
// ✅ Good: Distinguish shipping and billing addresses
<fieldset>
  <legend>Shipping Address</legend>
  <input
    type="text"
    name="shippingAddress"
    autoComplete="shipping street-address"
  />
  <input
    type="text"
    name="shippingCity"
    autoComplete="shipping address-level2"
  />
</fieldset>

<fieldset>
  <legend>Billing Address</legend>
  <input
    type="text"
    name="billingAddress"
    autoComplete="billing street-address"
  />
  <input
    type="text"
    name="billingCity"
    autoComplete="billing address-level2"
  />
</fieldset>
```

## When to Disable Autocomplete

```tsx
// ✅ Good: Legitimate cases to disable autocomplete
<input
  type="text"
  name="search"
  autoComplete="off"  // Search boxes
/>

<input
  type="text"
  name="captcha"
  autoComplete="off"  // CAPTCHA responses
/>

// ❌ Bad: Don't disable for these (user hostile)
<input type="email" autoComplete="off" />    // Email
<input type="password" autoComplete="off" /> // Password
<input type="text" name="address" autoComplete="off" /> // Address
```

Reference: [WCAG 1.3.5 Identify Input Purpose](https://www.w3.org/WAI/WCAG21/Understanding/identify-input-purpose.html)
