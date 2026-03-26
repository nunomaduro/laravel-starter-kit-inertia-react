# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Architecture & Structure (arch)

**Impact:** CRITICAL
**Description:** Foundational patterns for organizing Laravel applications. Service classes, action classes, DTOs, and proper separation of concerns are essential for maintainable, scalable codebases. These patterns determine long-term code quality and team productivity.

## 2. Eloquent & Database (eloquent)

**Impact:** CRITICAL
**Description:** Efficient database operations and ORM usage. Preventing N+1 queries through eager loading, using chunking for large datasets, and proper relationship management are critical for performance. Poor database patterns can cripple application performance at scale.

## 3. Controllers & Routing (controller, ctrl)

**Impact:** HIGH
**Description:** RESTful conventions, resource controllers, and proper request handling. Well-structured controllers following Laravel conventions improve code predictability, maintainability, and team collaboration. Thin controllers delegate to services for business logic.

## 4. Validation & Requests (validation, valid)

**Impact:** HIGH
**Description:** Form request classes, custom validation rules, and authorization patterns. Proper validation ensures data integrity, security, and separation of concerns. Centralized validation logic in form requests keeps controllers clean and validation rules reusable.

## 5. Security (sec)

**Impact:** HIGH
**Description:** Protection against common vulnerabilities including mass assignment, SQL injection, XSS, and CSRF attacks. Laravel provides excellent security features, but developers must use them correctly. Security issues can have catastrophic consequences.

## 6. Performance (perf)

**Impact:** MEDIUM
**Description:** Caching strategies, queue usage, and optimization techniques for growing applications. While not critical initially, performance patterns become essential as applications scale. Proper caching and queue usage can provide 2-10× improvements.

## 7. API Design (api)

**Impact:** MEDIUM
**Description:** RESTful API patterns, resource transformers, versioning, and consistent response formatting. Well-designed APIs are essential for frontend-backend communication, third-party integrations, and mobile applications. API resources provide consistent data transformation.
