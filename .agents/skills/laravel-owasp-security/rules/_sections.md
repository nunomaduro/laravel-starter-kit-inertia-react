# Sections

This file defines all sections, their ordering, impact levels, OWASP mapping, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Broken Access Control (sec-broken-access-control)

**Impact:** CRITICAL
**OWASP:** A01:2021
**Description:** Middleware, ownership checks, Gates/Policies, and scoped queries. The most common critical vulnerability in web applications. Attackers exploit missing access controls to access other users' data, perform privileged actions, or bypass authorization entirely. Proper server-side enforcement is mandatory — frontend checks alone are never sufficient.

## 2. Cryptographic Failures (sec-cryptographic-failures)

**Impact:** CRITICAL
**OWASP:** A02:2021
**Description:** Password hashing, encrypted Eloquent casts, and signed URLs. Weak or missing cryptography exposes sensitive data at rest and in transit. Passwords stored as plaintext or with weak algorithms (MD5, SHA1) are trivially cracked. API keys and secrets stored in plaintext database columns are exposed on any data breach.

## 3. Injection Prevention (sec-injection-prevention)

**Impact:** CRITICAL
**OWASP:** A03:2021
**Description:** SQL injection prevention and mass assignment protection. String-concatenated SQL queries allow attackers to read, modify, or delete any data in the database. Mass assignment vulnerabilities allow attackers to set fields they should not have access to (e.g., `is_admin`, `role`). Always use parameterized queries and `$request->validated()`.

## 4. XSS & React/Inertia (sec-xss-react-inertia)

**Impact:** HIGH
**OWASP:** A03:2021
**Description:** dangerouslySetInnerHTML, DOMPurify, and href/src validation. Cross-site scripting allows attackers to inject and execute scripts in the browser of any user who views the infected page. In React, `dangerouslySetInnerHTML` without sanitization and unvalidated `href` attributes are the primary attack vectors. DOMPurify must wrap all user-supplied HTML before rendering.

## 5. CSRF Protection (sec-csrf-protection)

**Impact:** HIGH
**OWASP:** A08:2021
**Description:** VerifyCsrfToken middleware, webhook exclusions, and Inertia CSRF. Cross-site request forgery tricks authenticated users into submitting requests they did not intend. Laravel's `VerifyCsrfToken` middleware prevents this. Only stateless webhook routes should be excluded — all other routes must remain protected.

## 6. Security Misconfiguration (sec-security-misconfiguration)

**Impact:** HIGH
**OWASP:** A05:2021
**Description:** APP_DEBUG, APP_KEY, security headers, and CORS. Misconfigured environments leak stack traces, expose admin panels, and allow cross-origin attacks. `APP_DEBUG=true` in production exposes full stack traces and environment details to attackers. Security headers protect against clickjacking, MIME sniffing, and XSS.

## 7. Authentication & Rate Limiting (sec-authentication-rate-limiting)

**Impact:** HIGH
**OWASP:** A07:2021
**Description:** Throttle middleware, RateLimiter, session fixation, and brute force prevention. Weak authentication allows attackers to brute-force credentials, hijack sessions, or bypass login entirely. Rate limiting on login, password reset, and sensitive action routes is mandatory. Session ID must be regenerated after login to prevent fixation attacks.

## 8. Inertia Data Exposure (sec-inertia-data-exposure)

**Impact:** HIGH
**OWASP:** React/Inertia R2
**Description:** Inertia data-page attribute exposure, secret props in shared state, and API Resources. All Inertia props passed from Laravel controllers are embedded in the `data-page` HTML attribute on initial page load — visible to anyone who views page source. Secret keys, admin flags, or sensitive credentials passed as Inertia props are publicly exposed regardless of frontend rendering logic.
