---
name: laravel-owasp-security
description: OWASP Top 10 security audit and secure coding guidelines for Laravel + React/Inertia.js applications. Use when auditing for vulnerabilities ("run OWASP audit", "security review", "check my app security") or writing secure Laravel code involving auth, payments, file uploads, or API design. Triggers on security-related tasks, payment handling, authentication, or any request to audit a Laravel codebase.
license: MIT
metadata:
  author: AsyrafHussin
  version: "1.0.3"
  laravelVersion: "13.x"
  phpVersion: "8.3+"
---

# Laravel OWASP Security

Dual-purpose security skill for Laravel 13 + React/Inertia.js applications. Run a full OWASP Top 10 audit against a codebase, or use as a secure coding reference when building features.

## How to Audit

### Step 1: Detect Stack

Check if the project uses React + Inertia.js by looking for:
- `app/Http/Middleware/HandleInertiaRequests.php` exists
- `resources/js/` contains `.tsx` or `.jsx` files
- `inertiajs/inertia-laravel` in `composer.json`
- `@inertiajs/react` in `package.json`

**If detected**, state at the top of the report:
> "React + Inertia.js detected — Laravel OWASP checklist AND React/Inertia security checks will both be applied."

**If not detected**, state:
> "No React/Inertia.js detected — applying Laravel OWASP checklist only."

### Step 2: Determine Scope

- If arguments provided (`$ARGUMENTS`): review only those files or features
- If no arguments: review the entire codebase

### Step 3: Run Checklist

Work through every item below. For each, output:
- **PASS** — brief confirmation of what was verified
- **FAIL** — exact `file:line`, a description of the vulnerability (do NOT reproduce any code, values, API keys, tokens, or .env contents from the file), and a fix recommendation
- **N/A** — if the check does not apply to this project

---

## OWASP Top 10 Checklist

### 1. Broken Access Control (A01:2021)

- [ ] Middleware protects all route groups by role (`auth`, `role:admin`, etc.)
- [ ] Resource queries scoped to authenticated user — `->where('user_id', auth()->id())`
- [ ] No direct object reference without ownership check
- [ ] Gates and Policies used to authorize resource access
- [ ] Frontend role checks are mirrored server-side — never rely on React UI checks alone

### 2. Cryptographic Failures (A02:2021)

- [ ] Passwords hashed with `Hash::make()` or `'hashed'` Eloquent cast — never stored as plaintext
- [ ] No MD5 or SHA1 used for password hashing
- [ ] Sensitive fields (API keys, secrets) encrypted with `Crypt::encryptString()` or `'encrypted'` Eloquent cast
- [ ] `APP_KEY` is long, random, and unique per environment
- [ ] Signed URLs (`URL::signedRoute()`) used for sensitive one-time actions (password reset, email verify)

### 3. Injection (A03:2021)

**SQL & Mass Assignment:**
- [ ] No string concatenation in `whereRaw()`, `selectRaw()`, `orderByRaw()` — use `?` bindings
- [ ] Column names never derived from user input without a whitelist
- [ ] No `$request->all()` passed directly to `create()`, `fill()`, or `update()`
- [ ] No `forceFill()` or `forceCreate()` with unvalidated user input
- [ ] Models define `$fillable` explicitly — not `$guarded = []`
- [ ] Controllers use `$request->validated()` for mass operations

**XSS — Blade & React:**
- [ ] No `{!! $userInput !!}` in Blade templates with untrusted data
- [ ] `{{ }}` used for all user-supplied Blade output
- [ ] No `dangerouslySetInnerHTML` in React without `DOMPurify.sanitize()` first
- [ ] `href` and `src` attributes not set from unvalidated user input
- [ ] No `eval()`, `new Function()`, or `setTimeout(string)` with user-controlled strings
- [ ] External CDN scripts use Subresource Integrity (`integrity="sha384-..."`)

### 4. Insecure Design (A04:2021)

- [ ] Business logic enforced server-side — prices, totals, and discounts never trusted from client input
- [ ] Sensitive operations require secondary confirmation (e.g. password re-entry for account deletion)
- [ ] No mass action endpoints without per-item authorization check
- [ ] Admin-only features isolated behind separate middleware — not just hidden in the UI
- [ ] Payment amounts and enrollment states calculated server-side, not passed as form inputs

### 5. Security Misconfiguration (A05:2021)

- [ ] `APP_DEBUG=false` in production
- [ ] `.env` is in `.gitignore` and never committed
- [ ] Database uses a restricted user — not root/admin — in production
- [ ] `storage/` and `bootstrap/cache/` have correct permissions (not world-writable)
- [ ] `APP_KEY` is set and unique per environment
- [ ] CORS `allowed_origins` is not `['*']` for authenticated API routes

### 6. Vulnerable & Outdated Components (A06:2021)

- [ ] `composer audit` passes with no known CVEs
- [ ] `npm audit` passes with no known CVEs
- [ ] Laravel framework is on a supported version

### 7. Identification & Authentication Failures (A07:2021)

**Auth:**
- [ ] Using Laravel Breeze, Fortify, or Jetstream — not custom-rolled auth
- [ ] Passwords hashed with bcrypt or argon2 (Laravel default)
- [ ] Login route rate limited — `throttle` middleware or `RateLimiter` in `LoginRequest`
- [ ] Password reset and email verification routes rate limited
- [ ] Payment and sensitive action routes have appropriate rate limits
- [ ] `session()->regenerate()` called after successful login

**Cookie & Session:**
- [ ] `http_only = true` in `config/session.php`
- [ ] `same_site = lax` or `strict` in `config/session.php`
- [ ] `secure = true` or `null` (auto for HTTPS) in `config/session.php`
- [ ] `lifetime` is a reasonable value (15–30 min recommended for most apps)
- [ ] `domain = null` unless subdomains are needed
- [ ] `EncryptCookies` middleware is in the web group

### 8. Software & Data Integrity Failures (A08:2021)

**CSRF:**
- [ ] `VerifyCsrfToken` middleware active in the web group
- [ ] Only stateless routes (webhooks, external callbacks) are excluded from CSRF
- [ ] `@csrf` directive used in all non-Inertia POST forms
- [ ] Excluded routes in `validateCsrfTokens(except: [...])` are justified

**Deserialization:**
- [ ] No `unserialize($request->input(...))`
- [ ] No `eval($request->input(...))`
- [ ] No `extract($request->all())`

### 9. Security Logging & Monitoring Failures (A09:2021)

- [ ] Failed login attempts logged with IP and identifier
- [ ] Payment failures and exceptions logged
- [ ] Log entries do not contain raw passwords or secrets
- [ ] Monitoring in place (Laravel Telescope, Sentry, or similar)

### 10. Server-Side Request Forgery — SSRF (A10:2021)

- [ ] No `Http::get($request->input('url'))` with unvalidated URLs
- [ ] User-supplied URLs validated against an allowlist or scheme check
- [ ] Internal network addresses blocked from user-supplied URLs

---

## Additional Checks

> Not part of the OWASP Top 10 but critical for Laravel applications.

### Command Injection & Dangerous Functions

- [ ] No `exec()`, `shell_exec()`, `system()`, `passthru()` with user input
- [ ] No open redirects — no `redirect($request->input('url'))` with unvalidated URLs
- [ ] File uploads validate `mimes:`, `max:` — filenames never derived from raw user input

### Security Headers

- [ ] `Content-Security-Policy` set — with nonces (`Vite::useCspNonce()`) if possible
- [ ] `X-Frame-Options` set
- [ ] `X-Content-Type-Options` set
- [ ] `Strict-Transport-Security` set for HTTPS
- [ ] `Referrer-Policy` set
- [ ] `Permissions-Policy` set

---

## React + Inertia.js Checks

> Only run if React + Inertia.js detected in Step 1.

### R1. XSS in React Components

- [ ] No `dangerouslySetInnerHTML={{ __html: userInput }}` without `DOMPurify.sanitize()` first
- [ ] `href` and `src` attributes not set from unvalidated user input — `javascript:` URLs execute scripts
- [ ] No `eval()`, `new Function()`, or `setTimeout(string)` with user-controlled strings
- [ ] Links from user input validate scheme (`https://` or `http://` only)

### R2. Inertia.js Data Exposure (Critical)

- [ ] `HandleInertiaRequests::share()` does NOT expose passwords, tokens, or internal-only flags
- [ ] Controllers use `->only([...])` or API Resources — not raw model `toArray()`
- [ ] All Inertia props are treated as public — visible in `data-page` HTML attribute on initial load
- [ ] Payment secret keys and admin-only credentials are never passed as Inertia props
- [ ] Inertia v2 History Encryption enabled for pages with sensitive data

### R3. CSRF in Inertia.js

- [ ] Inertia `X-XSRF-TOKEN` header not disabled
- [ ] Custom `fetch` or `axios` calls include CSRF token manually if bypassing Inertia's router
- [ ] Webhook/callback routes are the ONLY CSRF-excluded routes

### R4. Authentication State in React

- [ ] `auth.user` Inertia prop excludes password hash, remember tokens, and 2FA secrets
- [ ] Role/permission checks enforced server-side — React checks are UI-only
- [ ] `auth.user` contains only fields the UI actually needs

### R5. Sensitive Data in Browser

- [ ] No API keys or secrets hardcoded in React components or TypeScript files
- [ ] No sensitive data in `localStorage` or `sessionStorage` — use HttpOnly cookies
- [ ] `VITE_*` env vars contain no secrets — they are public by design

### R6. Dependency Security

- [ ] `npm audit` passes with no high/critical CVEs in React or Inertia packages
- [ ] React is on a supported version
- [ ] Third-party component libraries reviewed for known CVEs

---

## Output Format

Structure the audit report as:

```
## Laravel OWASP Security Audit Report

> React + Inertia.js detected — Laravel OWASP checklist AND React/Inertia security checks will both be applied.

### 1. Broken Access Control (A01:2021)
- **PASS** `app/Http/Middleware/RoleMiddleware.php` — role middleware applied to all route groups
- **FAIL** `app/Http/Controllers/PaymentController.php:42` — Payment model fetched without ownership check (direct object reference exposure). Fix: scope the query to the authenticated user.

[Continue for all 10 OWASP checks + Additional Checks + R1–R6 React/Inertia checks]

---

## Summary

### Critical Issues (fix immediately)
1. ...

### Warnings (fix soon)
1. ...

### Passed
X checks passed.

### Recommended Commands
composer audit
npm audit
```

---

## When to Apply for Guidance

Reference the rule files when:
- Implementing authentication or password handling
- Building payment or webhook integrations
- Writing file upload or download logic
- Designing admin or role-based access control
- Building API endpoints with user-supplied input
- Using `dangerouslySetInnerHTML` in React components
- Passing data from Laravel controllers to Inertia props

## Rule Categories by Priority

| Priority | Category | Impact | Rule File |
|----------|----------|--------|-----------|
| 1 | Broken Access Control | CRITICAL | `sec-broken-access-control` |
| 2 | Cryptographic Failures | CRITICAL | `sec-cryptographic-failures` |
| 3 | Injection Prevention | CRITICAL | `sec-injection-prevention` |
| 4 | XSS & React/Inertia | HIGH | `sec-xss-react-inertia` |
| 5 | CSRF Protection | HIGH | `sec-csrf-protection` |
| 6 | Security Misconfiguration | HIGH | `sec-security-misconfiguration` |
| 7 | Authentication & Rate Limiting | HIGH | `sec-authentication-rate-limiting` |
| 8 | Inertia Data Exposure | HIGH | `sec-inertia-data-exposure` |

## Quick Reference

### 1. Broken Access Control (CRITICAL)
- `sec-broken-access-control` — Middleware, ownership checks, policies, scoped queries

### 2. Cryptographic Failures (CRITICAL)
- `sec-cryptographic-failures` — Password hashing, encrypted casts, signed URLs

### 3. Injection Prevention (CRITICAL)
- `sec-injection-prevention` — SQL injection, mass assignment, raw query bindings

### 4. XSS & React/Inertia (HIGH)
- `sec-xss-react-inertia` — dangerouslySetInnerHTML, DOMPurify, href/src validation

### 5. CSRF Protection (HIGH)
- `sec-csrf-protection` — VerifyCsrfToken, webhook exclusions, Inertia CSRF

### 6. Security Misconfiguration (HIGH)
- `sec-security-misconfiguration` — APP_DEBUG, APP_KEY, security headers, CORS

### 7. Authentication & Rate Limiting (HIGH)
- `sec-authentication-rate-limiting` — Throttle, session regeneration, brute force prevention

### 8. Inertia Data Exposure (HIGH)
- `sec-inertia-data-exposure` — data-page attribute exposure, secret props, API Resources

## How to Use

Read individual rule files for detailed explanations and code examples:

```
rules/sec-broken-access-control.md
rules/sec-cryptographic-failures.md
rules/sec-injection-prevention.md
rules/sec-xss-react-inertia.md
rules/sec-csrf-protection.md
rules/sec-security-misconfiguration.md
rules/sec-authentication-rate-limiting.md
rules/sec-inertia-data-exposure.md
```

Each rule file contains:
- YAML frontmatter with metadata (title, impact, tags)
- Why it matters in Laravel/React context
- Incorrect code example with explanation
- Correct code example with fix
- Laravel 13 and PHP 8.3+ specific context

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
