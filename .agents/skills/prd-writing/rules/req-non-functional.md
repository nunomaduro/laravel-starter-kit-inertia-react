---
title: Define Non-Functional Requirements
impact: HIGH
impactDescription: "Quality attributes that determine production readiness"
tags: requirements, non-functional, performance, security, accessibility
---

## Define Non-Functional Requirements

**Impact: HIGH (Quality attributes that determine production readiness)**

Non-functional requirements (NFRs) define how the system should behave — performance, security, accessibility, reliability, and scalability. Missing NFRs lead to production surprises.

## Incorrect

```markdown
<!-- Bad: vague or missing NFRs -->
## Non-Functional Requirements
- The system should be fast
- It should be secure
- Must handle lots of users
- Should work on mobile
```

**Problems:**
- "Fast" is not measurable — fast compared to what?
- "Secure" with no specifics is meaningless
- "Lots of users" is not a number
- "Work on mobile" doesn't specify what "work" means

## Correct

```markdown
<!-- Good: specific, measurable NFRs -->
## Non-Functional Requirements

### Performance
- **NFR-1:** Redirect (/{slug}) response time: p95 < 100ms
- **NFR-2:** Dashboard page load: < 2s for up to 10,000 short URLs
- **NFR-3:** Analytics page load: < 3s for links with up to 100K clicks
- **NFR-4:** Click recording job completes within 5s of redirect

### Security
- **NFR-5:** All routes except redirect require authentication
- **NFR-6:** Users can only access their own short URLs (policy-enforced)
- **NFR-7:** Admin routes require is_admin flag
- **NFR-8:** CSRF protection on all form submissions
- **NFR-9:** Security headers: CSP, X-Frame-Options, X-Content-Type-Options

### Reliability
- **NFR-10:** Failed click recording does not block redirect
- **NFR-11:** Geolocation lookup failure does not fail the click recording job
- **NFR-12:** System operates normally with database queue driver

### Accessibility
- **NFR-13:** All pages meet WCAG 2.1 AA compliance
- **NFR-14:** Full keyboard navigation support
- **NFR-15:** Forms have associated labels and error messages

### Compatibility
- **NFR-16:** Responsive design: functional on viewport widths 320px to 2560px
- **NFR-17:** Supported browsers: Chrome, Firefox, Safari, Edge (latest 2 versions)
```

**Benefits:**
- Performance NFRs become automated test assertions
- Security NFRs guide middleware and policy implementation
- Accessibility NFRs are auditable with tools like axe
- Every NFR has a clear pass/fail threshold

## Why

1. **Prevents production surprises**: "It's slow" is caught before launch, not after
2. **Defines done**: A feature isn't complete until NFRs pass
3. **Guides architecture**: NFR-1 (100ms redirect) rules out certain approaches
4. **Enables SLAs**: NFRs become the basis for service level agreements
