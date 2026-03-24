# Codebase Hardening — Production Readiness Spec

**Date:** 2026-03-24
**Status:** Draft
**Goal:** Close all identified gaps in security, testing, code quality, and accessibility before production deployment.

---

## Scope

4 phases, dependency-ordered so each phase unblocks the next.

| Phase | Domain | Deliverables | Depends On |
|-------|--------|-------------|------------|
| 1 | Foundations | Factories, policies, FormRequests | — |
| 2 | Security Hardening | Job resilience, webhook auth, tenant isolation, N+1 fixes | Phase 1 (policies) |
| 3 | Test Coverage | Policy tests, job tests, controller tests, action tests, API tests | Phase 1 (factories), Phase 2 (jobs) |
| 4 | Frontend Quality | Accessibility, type definitions, component extraction | — (4A/4C can parallel with 2-3; 4B after Phase 3 to avoid import conflicts) |

---

## Phase 1: Foundations

### 1A. Missing Factories

Create factories for models that lack them. Tests (Phase 3) depend on these.

**App Models (10):**

| Model | Location | Notes |
|-------|----------|-------|
| AgentConversation | app/Models | Needs user + optional org relationship |
| AgentConversationMessage | app/Models | Needs AgentConversation parent |
| AuditLog | app/Models | Has organization_id |
| NotificationPreference | app/Models | Needs user relationship |
| SlugRedirect | app/Models | Has organization_id |
| SocialAccount | app/Models | Needs user + provider fields |
| TermsVersion | app/Models | Standalone, version + content |
| UserTermsAcceptance | app/Models | Needs user + TermsVersion |
| VisibilityDemo | app/Models | Has organization_id + visibility |
| VoucherScope | app/Models | Standalone |

**Module Models (needed for Phase 3 tests):**

| Model | Module | Notes |
|-------|--------|-------|
| Announcement | announcements | Has organization_id |
| Post | blog | Has organization_id, BelongsToOrganization |
| ChangelogEntry | changelog | Has organization_id |
| ContactSubmission | contact | Has organization_id |
| Dashboard | dashboards | Has organization_id |
| HelpArticle | help | Has organization_id |
| Contact | module-crm | Has organization_id |
| Deal | module-crm | Has organization_id, belongs to Pipeline |
| Pipeline | module-crm | Has organization_id |
| Activity | module-crm | Has organization_id |
| Department | module-hr | Has organization_id |
| Employee | module-hr | Has organization_id |
| LeaveRequest | module-hr | Has organization_id |
| Page | page-builder | Has organization_id |
| PageRevision | page-builder | Belongs to Page |
| Report | reports | Has organization_id |
| ReportOutput | reports | Belongs to Report |
| Plan | billing | Standalone |
| Credit | billing | Has organization_id |
| Invoice | billing | Has organization_id |
| Subscription | billing | Belongs to Organization |
| RefundRequest | billing | Has organization_id |

**Approach:** Use `php artisan make:factory` for each. Check existing model `$fillable`/`$casts` to determine correct factory fields. Use Faker for realistic data. Wire up relationship factories with `.for()` and `.has()`. Module factories go in `modules/{name}/database/factories/` or `database/factories/` depending on module structure. Update seeders where appropriate.

### 1B. Missing Policies (15 app models + 32 module models)

**App Models needing policies (15):**
AgentConversation, AgentConversationMessage, AuditLog, Category, EmbeddingDemo, EnterpriseInquiry, ModelFlag, NotificationPreference, OrganizationDomain, SlugRedirect, SocialAccount, TermsVersion, UserTermsAcceptance, VisibilityDemo, VoucherScope

**Module Models needing policies (select — not all need full CRUD):**
- billing: Plan, Credit, Invoice, RefundRequest, Subscription, WebhookLog
- blog: Post
- changelog: ChangelogEntry
- contact: ContactSubmission
- dashboards: Dashboard
- help: HelpArticle
- module-crm: Contact, Deal, Pipeline, Activity
- module-hr: Department, Employee, LeaveRequest
- page-builder: Page, PageRevision
- reports: Report, ReportOutput
- announcements: Announcement

**Policy patterns:**
- Org-scoped models: Check `$user->canInOrganization('action', $model)` and verify `organization_id` matches tenant context
- User-scoped models (SocialAccount, NotificationPreference): Owner-only access
- Admin-only models (AuditLog, TermsVersion, ModelFlag): Gate check for admin permission
- Public-read models (HelpArticle, ChangelogEntry, Announcement): Anyone can view, org members can manage

**Approach:** Use `php artisan make:policy` with `--model`. Follow existing `UserPolicy` and `OrganizationPolicy` patterns. Laravel 13 uses auto-discovery (`App\Policies\{Model}Policy`) — no manual registration needed for app models. Module models with non-standard namespaces may need explicit `Gate::policy()` in their module service provider.

### 1C. Extract Inline Validation to FormRequests (15 controllers)

| Controller | Method | Line |
|-----------|--------|------|
| Api/ChatController | store | 40 |
| Api/ConversationController | update | 91 |
| Crm/DealController | update | 69 |
| Hr/LeaveRequestController | update | 70 |
| SearchController | index | 21 |
| Settings/NotificationPreferencesController | update | 58 |
| Settings/OrgBrandingUserControlsController | update | 34 |
| Settings/OrgDomainsController | store | 61 |
| Settings/OrgFeaturesController | update | 62 |
| Settings/OrgRolesController | store | 62 |
| Settings/OrgSlugController | update | 39 |
| UserPreferencesController | update | 17 |
| WizardController | storeStep1 | 46 |
| WizardController | storeStep2 | 60 |
| WizardController | storeStep3 | 75 |

**Approach:** Use `php artisan make:request`. Extract validation rules and custom messages. Type-hint the FormRequest in the controller method. Check sibling FormRequests for array vs string rule style.

---

## Phase 2: Security Hardening

### 2A. Queue Job Resilience (5 jobs)

| Job | Current | Fix |
|-----|---------|-----|
| GenerateEmbedding | None confirmed | Add $tries=3, $timeout=120, $backoff=[10,30,60], failed() with logging |
| GenerateOgImageJob | None confirmed | Add $tries=2, $timeout=60, $backoff=[5,15], failed() with logging |
| NotifyUsersOfNewTermsVersion | None | Add $tries=3, $timeout=30, $backoff=[10,30], failed() |
| ProcessWebhookJob | None | Add $tries=5, $timeout=60, $backoff=[5,15,30,60,120], failed() with alerting |
| VerifyOrganizationDomain | None | Add $tries=3, $timeout=30, $backoff=[10,30,60], failed() |

**Every job gets:**
- `public int $tries` — max attempts
- `public int $timeout` — seconds before kill
- `public array $backoff` — exponential backoff array
- `public function failed(Throwable $e): void` — log error, optionally notify

### 2B. Webhook Secret Verification

`ProcessWebhookJob` extends Spatie's `SpatieProcessWebhookJob`. Incoming signature verification is handled by Spatie's webhook-client package via `config/webhook-client.php` (`signing_secret`). The commented-out code in the job is for *forwarding* secrets, not incoming verification.

**Fix:**
- Verify `config/webhook-client.php` has a proper `signing_secret` referencing `config('services.webhook.secret')`
- Ensure `WEBHOOK_CLIENT_SECRET` is in `.env.example` with documentation
- Verify the Spatie webhook-client middleware validates signatures on incoming webhook routes
- Do NOT re-implement HMAC verification in the job itself — Spatie handles this

**Note:** When adding `$tries`/`$backoff`/`$timeout` to `ProcessWebhookJob` (2A), check `SpatieProcessWebhookJob` parent class first to avoid property conflicts.

### 2C. BelongsToOrganization Trait (app models)

Models with `organization_id` column that lack the trait:

| Model | Has org_id | Has Trait | Action |
|-------|-----------|-----------|--------|
| AuditLog | Yes | No | Add trait |
| OrganizationDomain | Yes | No | Add trait |
| SlugRedirect | Yes | No | Add trait |
| VisibilityDemo | Yes | No | Uses HasVisibility instead — skip |

**Note:** Models using `HasVisibility` should NOT also use `BelongsToOrganization` (per CLAUDE.md). `VisibilityDemo` is correct as-is.

**Module models** already use BelongsToOrganization where appropriate (billing: BillingMetric, Credit, Invoice, RefundRequest; blog: Post; etc.). Verify these are all correct.

### 2D. N+1 Query Prevention

Add `Model::preventLazyLoading(!app()->isProduction())` in `AppServiceProvider::boot()`. This is NOT currently present in the codebase — it is a new addition. This will catch N+1 issues in development/testing while allowing lazy loading in production.

Review controllers for missing eager loads — particularly:
- Index/list endpoints loading relationships in loops
- Dashboard controllers aggregating across models
- API controllers returning nested resources

### 2E. API Route Middleware Audit

Verify `routes/api.php` middleware stack:
- All endpoints have `auth:sanctum` (or explicit public access)
- Rate limiting is configured and applied (`throttle:api` or custom)
- Tenant context middleware applied to org-scoped API endpoints

### 2F. Blade Unescaped Output Audit

94 instances of `{!! !!}` found — **all in vendor views** (Filament, Livewire, invoices). These are framework-managed and safe. No action needed for app views.

---

## Phase 3: Test Coverage

Target: Bring direct test coverage from 13.1% to >60% on critical paths.

### 3A. Policy Tests (4 existing + new policies)

Test every policy method: `viewAny`, `view`, `create`, `update`, `delete`, `forceDelete`, `restore`.

Test scenarios:
- Owner can manage their resources
- Org member can access org-scoped resources
- Non-member cannot access other org's resources
- Admin can manage admin-only resources
- Guest cannot access protected resources

### 3B. Job Tests (5 jobs)

For each job:
- Test successful execution
- Test failure handling (mock exception, verify `failed()` called)
- Test retry behavior
- Test timeout configuration
- Test idempotency where applicable

### 3C. Controller Tests (45 untested controllers)

Priority order:
1. **Auth controllers** — login, registration, password reset, 2FA
2. **API controllers** — ChatController, ConversationController
3. **Settings controllers** — all 6 settings controllers
4. **CRUD controllers** — organization, user management
5. **Module controllers** — CRM, HR, billing, blog, etc.

Each controller test:
- Test authentication requirement
- Test authorization (policy check)
- Test validation (invalid input rejected)
- Test happy path (correct response/redirect)
- Test edge cases (not found, forbidden)

### 3D. Action Tests (30 untested actions)

Test each action's `handle()` method:
- Valid input produces correct output/side effects
- Invalid input throws appropriate exceptions
- Database transactions roll back on failure (where applicable)
- Dependencies are properly injected

### 3E. Tenant Isolation Tests

Dedicated test suite verifying:
- Org-scoped queries only return data for current tenant
- BelongsToOrganization trait auto-filters correctly
- Cross-org access is blocked at policy level
- API endpoints respect tenant context

### 3F. API Endpoint Tests

Test all routes in `routes/api.php`:
- Authentication required (401 without token)
- Rate limiting works (429 after limit)
- Request validation (422 on bad input)
- Correct response structure (JSON:API or Eloquent Resource format)
- Pagination works correctly

---

## Phase 4: Frontend Quality

### 4A. Accessibility (WCAG 2.1 AA)

**149 interactive elements missing aria-labels.** Fix by category:

| Component Type | Count | Fix |
|---------------|-------|-----|
| Icon-only buttons | ~60 | Add `aria-label` describing the action |
| Clickable divs | ~20 | Convert to `<button>` or add `role="button"` + `aria-label` |
| Form controls | ~30 | Add `aria-label` or associate with `<label>` |
| Navigation links | ~20 | Add `aria-label` for context (e.g., "Navigate to dashboard") |
| Custom components | ~19 | Add appropriate ARIA attributes |

**Priority files:**
- `data-table/data-table.tsx` (8 gaps)
- `composed/notification-center.tsx` (3 gaps)
- `composed/file-manager.tsx` (2 gaps)
- `composed/command-bar.tsx` (1 gap)

**All images already have alt text** — no work needed there.

### 4B. Type Definitions Consolidation

Create domain-specific type files in `resources/js/types/`:

| File | Types to extract |
|------|-----------------|
| `organizations.ts` | OrganizationSummary, OrganizationSettings, OrgMember |
| `billing.ts` | Plan, Subscription, Invoice, Credit |
| `crm.ts` | Contact, Deal, Pipeline, Activity |
| `content.ts` | Post, HelpArticle, ChangelogEntry, Announcement |
| `data-table.ts` | Column configs, filter types, quick view types |

Extract locally-defined interfaces from pages into shared types. Update imports across all consuming files.

### 4C. Large Component Extraction

| File | Lines | Extract |
|------|-------|---------|
| `pages/dev/components.tsx` | 2,195 | Dev-only showcase — split by category (forms, tables, charts, etc.) |
| `pages/users/table.tsx` | 637 | Extract column definitions, filters, toolbar into separate files |
| `pages/welcome.tsx` | 583 | Extract hero, features, pricing, footer sections |
| `pages/dashboard.tsx` | 450 | Extract stat cards, chart panels, activity feed |
| `pages/chat/index.tsx` | 375 | Extract message list, input area, sidebar |

---

## Success Criteria

| Metric | Current | Target |
|--------|---------|--------|
| Models with factories (app) | 9/19 | 19/19 |
| Models with factories (modules) | 0/32 | 22+/32 (critical modules) |
| Models with policies (app) | 4/19 | 19/19 |
| Models with policies (modules) | 0/32 | 25+/32 (not all need full CRUD policies) |
| Controllers using FormRequests | ~70% | 100% |
| Jobs with failure handling | 0/5 | 5/5 |
| Webhook secret verification | Disabled | Enabled |
| Models with BelongsToOrganization (where needed) | 1/3 app (Category only; VisibilityDemo uses HasVisibility) | 3/3 app |
| Direct test coverage | 13.1% (20/153 classes) | >60% critical paths (auth, billing, CRM, settings, API controllers + all policies + all jobs) |
| ARIA compliance | 35/184 (19%) | 184/184 (100%) |
| Type definition files | 3 | 8+ |
| Components >500 lines | 5 | 0 |

---

## Out of Scope

- Vendor blade files ({!! !!}) — framework-managed, safe
- Factories for non-critical module models (gamification, workflows) — can be added later
- i18n/translation extraction — separate effort
- Performance profiling — separate effort after hardening
- Dependency upgrades — separate effort

---

## Estimated Effort

| Phase | Scope | Effort |
|-------|-------|--------|
| Phase 1 | 32 factories + 44 policies + 15 FormRequests | Large |
| Phase 2 | 5 jobs + webhook config + 3 traits + N+1 prevention + API middleware audit | Medium |
| Phase 3 | ~80 test files (policies, jobs, controllers, actions, API, tenant isolation) | Large |
| Phase 4 | 149 ARIA fixes + 5 type files + 5 component splits | Medium-Large |
