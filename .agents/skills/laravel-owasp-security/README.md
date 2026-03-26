# Laravel OWASP Security

OWASP Top 10 security audit and secure coding guidelines for Laravel 13 + React/Inertia.js applications.

## Overview

This skill provides:
- Full OWASP Top 10 security audit checklist mapped to Laravel 13 patterns
- 6 React/Inertia.js-specific security checks (R1–R6)
- Auto-detection of React + Inertia.js stack
- PASS/FAIL/N/A audit output with `file:line` references
- Secure coding reference with incorrect vs. correct PHP/TSX examples

## Categories

### OWASP Top 10 Checklist (A01–A10)
1. Broken Access Control (A01:2021) — CRITICAL
2. Cryptographic Failures (A02:2021) — CRITICAL
3. Injection — SQL, Mass Assignment, XSS (A03:2021) — CRITICAL
4. Insecure Design (A04:2021) — HIGH
5. Security Misconfiguration (A05:2021) — HIGH
6. Vulnerable & Outdated Components (A06:2021) — MEDIUM
7. Identification & Authentication Failures + Session (A07:2021) — HIGH
8. Software & Data Integrity Failures — CSRF + Deserialization (A08:2021) — HIGH
9. Security Logging & Monitoring Failures (A09:2021) — MEDIUM
10. Server-Side Request Forgery — SSRF (A10:2021) — HIGH

### Additional Checks
- Command Injection & Dangerous Functions
- Security Headers

### React + Inertia.js Checks (R1–R6)
- R1. XSS in React Components
- R2. Inertia.js Data Exposure (Critical)
- R3. CSRF in Inertia.js
- R4. Authentication State in React
- R5. Sensitive Data in Browser
- R6. Dependency Security

## Usage

**Run a security audit:**
- "Run OWASP audit on my Laravel app"
- "Security review of app/Http/Controllers"
- "Check my codebase for OWASP vulnerabilities"

**Use as a secure coding reference:**
- "How do I safely handle file uploads in Laravel?"
- "How do I prevent XSS in React with Inertia?"
- "What is the correct way to pass data from Laravel to Inertia?"

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Laravel Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Laravel_Cheat_Sheet.html)
- [Laravel 13 Security Documentation](https://laravel.com/docs/13.x/authentication)
- [Inertia.js Documentation](https://inertiajs.com)
- [DOMPurify](https://github.com/cure53/DOMPurify)
