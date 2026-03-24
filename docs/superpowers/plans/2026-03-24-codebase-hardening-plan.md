# Codebase Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close all identified gaps in security, testing, code quality, and accessibility before production deployment.

**Architecture:** 4 dependency-ordered phases. Phase 1 creates foundations (factories, policies, FormRequests) that Phase 2 (security) and Phase 3 (tests) depend on. Phase 4 (frontend) runs independently. Each task is a self-contained unit with its own commit.

**Tech Stack:** Laravel 13, PHP 8.4, Pest 4, Inertia v2, React 19, TypeScript, Tailwind v4

**Spec:** `docs/superpowers/specs/2026-03-24-codebase-hardening-design.md`

---

## File Structure

### Phase 1: Foundations

**New App Factories (10):**
- `database/factories/AgentConversationFactory.php`
- `database/factories/AgentConversationMessageFactory.php`
- `database/factories/AuditLogFactory.php`
- `database/factories/NotificationPreferenceFactory.php`
- `database/factories/SlugRedirectFactory.php`
- `database/factories/SocialAccountFactory.php`
- `database/factories/TermsVersionFactory.php`
- `database/factories/UserTermsAcceptanceFactory.php`
- `database/factories/VisibilityDemoFactory.php`
- `database/factories/VoucherScopeFactory.php`

**New Module Factories (gaps only — many already exist):**
- `modules/module-crm/database/factories/ContactFactory.php`
- `modules/module-crm/database/factories/DealFactory.php`
- `modules/module-crm/database/factories/PipelineFactory.php`
- `modules/module-crm/database/factories/ActivityFactory.php`
- `modules/module-hr/database/factories/DepartmentFactory.php`
- `modules/module-hr/database/factories/EmployeeFactory.php`
- `modules/module-hr/database/factories/LeaveRequestFactory.php`
- `modules/reports/database/factories/ReportFactory.php`
- `modules/reports/database/factories/ReportOutputFactory.php`
- `modules/dashboards/database/factories/DashboardFactory.php`
- `modules/billing/database/factories/PlanFactory.php`
- `modules/billing/database/factories/SubscriptionFactory.php`

**Already Existing Module Factories (verify, do NOT recreate):**
- `modules/blog/database/factories/PostFactory.php` ✅
- `modules/changelog/database/factories/ChangelogEntryFactory.php` ✅
- `modules/contact/database/factories/ContactSubmissionFactory.php` ✅
- `modules/help/database/factories/HelpArticleFactory.php` ✅
- `modules/announcements/database/factories/AnnouncementFactory.php` ✅
- `modules/page-builder/database/factories/PageFactory.php` ✅
- `modules/page-builder/database/factories/PageRevisionFactory.php` ✅
- `modules/billing/database/factories/CreditFactory.php` ✅
- `modules/billing/database/factories/InvoiceFactory.php` ✅
- `modules/billing/database/factories/RefundRequestFactory.php` ✅
- `modules/billing/database/factories/RefundRequestFactory.php`

**New Policies (15 app + 25 module):**
- `app/Policies/AgentConversationPolicy.php`
- `app/Policies/AgentConversationMessagePolicy.php`
- `app/Policies/AuditLogPolicy.php`
- `app/Policies/CategoryPolicy.php`
- `app/Policies/EmbeddingDemoPolicy.php`
- `app/Policies/EnterpriseInquiryPolicy.php`
- `app/Policies/ModelFlagPolicy.php`
- `app/Policies/NotificationPreferencePolicy.php`
- `app/Policies/OrganizationDomainPolicy.php`
- `app/Policies/SlugRedirectPolicy.php`
- `app/Policies/SocialAccountPolicy.php`
- `app/Policies/TermsVersionPolicy.php`
- `app/Policies/UserTermsAcceptancePolicy.php`
- `app/Policies/VisibilityDemoPolicy.php`
- `app/Policies/VoucherScopePolicy.php`
- Module policies registered in their respective service providers

**New FormRequests (15):**
- `app/Http/Requests/Api/StoreChatMessageRequest.php`
- `app/Http/Requests/Api/UpdateConversationRequest.php`
- `app/Http/Requests/Crm/UpdateDealRequest.php`
- `app/Http/Requests/Hr/UpdateLeaveRequestRequest.php`
- `app/Http/Requests/SearchRequest.php`
- `app/Http/Requests/Settings/UpdateNotificationPreferencesRequest.php`
- `app/Http/Requests/Settings/UpdateOrgBrandingUserControlsRequest.php`
- `app/Http/Requests/Settings/StoreOrgDomainRequest.php`
- `app/Http/Requests/Settings/UpdateOrgFeaturesRequest.php`
- `app/Http/Requests/Settings/StoreOrgRoleRequest.php`
- `app/Http/Requests/Settings/UpdateOrgSlugRequest.php`
- `app/Http/Requests/UpdateUserPreferencesRequest.php`
- `app/Http/Requests/StoreWizardStep1Request.php`
- `app/Http/Requests/StoreWizardStep2Request.php`
- `app/Http/Requests/StoreWizardStep3Request.php`

### Phase 2: Security Hardening

**Modified files:**
- `app/Jobs/GenerateEmbedding.php` — add `$timeout`, `failed()`
- `app/Jobs/GenerateOgImageJob.php` — add `$tries`, `$timeout`, `$backoff`, `failed()`
- `app/Jobs/NotifyUsersOfNewTermsVersion.php` — add `$tries`, `$timeout`, `$backoff`, `failed()`
- `app/Jobs/ProcessWebhookJob.php` — add `$timeout`, `failed()` (check parent class first)
- `app/Jobs/VerifyOrganizationDomain.php` — add `$tries`, `$timeout`, `$backoff`, `failed()`
- `app/Models/AuditLog.php` — add `BelongsToOrganization` trait
- `app/Models/OrganizationDomain.php` — add `BelongsToOrganization` trait
- `app/Models/SlugRedirect.php` — add `BelongsToOrganization` trait
- `config/webhook-client.php` — verify signing_secret config

### Phase 3: Test Coverage

**New test files (~40+):**
- `tests/Feature/Policies/` — one test per policy
- `tests/Feature/Jobs/` — one test per job
- `tests/Feature/Settings/` — settings controller tests
- `tests/Feature/Api/` — API endpoint tests
- `tests/Feature/TenantIsolationTest.php`

### Phase 4: Frontend Quality

**Modified files:**
- `resources/js/components/data-table/data-table.tsx` — ARIA fixes
- `resources/js/components/composed/notification-center.tsx` — ARIA fixes
- `resources/js/components/composed/file-manager.tsx` — ARIA fixes
- `resources/js/components/composed/command-bar.tsx` — ARIA fixes
- ~20 additional component files with ARIA gaps
- `resources/js/types/organizations.ts` — new
- `resources/js/types/billing.ts` — new
- `resources/js/types/crm.ts` — new
- `resources/js/types/content.ts` — new
- `resources/js/types/data-table.ts` — new

---

## Phase 1: Foundations

### Task 1: App Model Factories (Batch 1 — Simple Models)

**Files:**
- Create: `database/factories/VoucherScopeFactory.php`
- Create: `database/factories/TermsVersionFactory.php`
- Create: `database/factories/NotificationPreferenceFactory.php`
- Create: `database/factories/SlugRedirectFactory.php`
- Create: `database/factories/VisibilityDemoFactory.php`

**Context:** Follow existing factory pattern in `database/factories/UserFactory.php`. Use `final class`, `declare(strict_types=1)`, `fake()` helper. All models already use `HasFactory` trait.

- [ ] **Step 1: Create VoucherScopeFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VoucherScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VoucherScope> */
final class VoucherScopeFactory extends Factory
{
    protected $model = VoucherScope::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
```

- [ ] **Step 2: Create TermsVersionFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TermsType;
use App\Models\TermsVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TermsVersion> */
final class TermsVersionFactory extends Factory
{
    protected $model = TermsVersion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'body' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(TermsType::cases()),
            'effective_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'summary' => fake()->sentence(),
            'is_required' => true,
        ];
    }

    public function optional(): self
    {
        return $this->state(fn (): array => ['is_required' => false]);
    }
}
```

- [ ] **Step 3: Create NotificationPreferenceFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationPreference> */
final class NotificationPreferenceFactory extends Factory
{
    protected $model = NotificationPreference::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => fake()->randomElement(['announcement', 'billing', 'security', 'system']),
            'via_database' => true,
            'via_email' => fake()->boolean(),
        ];
    }
}
```

- [ ] **Step 4: Create SlugRedirectFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SlugRedirect> */
final class SlugRedirectFactory extends Factory
{
    protected $model = SlugRedirect::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'old_slug' => fake()->unique()->slug(),
            'organization_id' => Organization::factory(),
            'redirects_to_slug' => fake()->unique()->slug(),
            'expires_at' => null,
        ];
    }

    public function expiring(): self
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->addDays(30),
        ]);
    }
}
```

- [ ] **Step 5: Create VisibilityDemoFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VisibilityEnum;
use App\Models\Organization;
use App\Models\VisibilityDemo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VisibilityDemo> */
final class VisibilityDemoFactory extends Factory
{
    protected $model = VisibilityDemo::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'organization_id' => Organization::factory(),
            'visibility' => VisibilityEnum::Organization,
        ];
    }

    public function global(): self
    {
        return $this->state(fn (): array => [
            'visibility' => VisibilityEnum::Global,
            'organization_id' => null,
        ]);
    }
}
```

- [ ] **Step 6: Run Pint and verify factories compile**

Run: `vendor/bin/pint --dirty --format agent && php artisan tinker --execute "App\Models\VoucherScope::factory()->make(); App\Models\TermsVersion::factory()->make(); echo 'OK';"`

- [ ] **Step 7: Commit**

```bash
git add database/factories/VoucherScopeFactory.php database/factories/TermsVersionFactory.php database/factories/NotificationPreferenceFactory.php database/factories/SlugRedirectFactory.php database/factories/VisibilityDemoFactory.php
git commit -m "feat: add factories for VoucherScope, TermsVersion, NotificationPreference, SlugRedirect, VisibilityDemo"
```

---

### Task 2: App Model Factories (Batch 2 — Relational Models)

**Files:**
- Create: `database/factories/SocialAccountFactory.php`
- Create: `database/factories/UserTermsAcceptanceFactory.php`
- Create: `database/factories/AuditLogFactory.php`
- Create: `database/factories/AgentConversationFactory.php`
- Create: `database/factories/AgentConversationMessageFactory.php`

**Context:** These have relationships. AgentConversation/Message use string PKs (UUIDs). AuditLog has polymorphic actor. Check model `$fillable` for exact fields. **IMPORTANT:** AgentConversation and AgentConversationMessage do NOT currently have the `HasFactory` trait — you must add it to both models before the factories will work via `Model::factory()`.

- [ ] **Step 1: Create SocialAccountFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SocialAccount> */
final class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['google', 'github']),
            'provider_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => now()->addHour(),
        ];
    }
}
```

- [ ] **Step 2: Create UserTermsAcceptanceFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TermsVersion;
use App\Models\User;
use App\Models\UserTermsAcceptance;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserTermsAcceptance> */
final class UserTermsAcceptanceFactory extends Factory
{
    protected $model = UserTermsAcceptance::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'terms_version_id' => TermsVersion::factory(),
            'accepted_at' => now(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
```

- [ ] **Step 3: Create AuditLogFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
final class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'actor_id' => User::factory(),
            'actor_type' => User::class,
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'slug.changed']),
            'subject_type' => 'organization',
            'subject_id' => fake()->numberBetween(1, 100),
            'old_value' => null,
            'new_value' => null,
            'ip_address' => fake()->ipv4(),
        ];
    }

    public function withChanges(array $old, array $new): self
    {
        return $this->state(fn (): array => [
            'old_value' => $old,
            'new_value' => $new,
        ]);
    }
}
```

- [ ] **Step 4: Add HasFactory trait to AgentConversation and AgentConversationMessage models**

Add `use Illuminate\Database\Eloquent\Factories\HasFactory;` import and `use HasFactory;` in class body to both `app/Models/AgentConversation.php` and `app/Models/AgentConversationMessage.php`.

- [ ] **Step 5: Create AgentConversationFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<AgentConversation> */
final class AgentConversationFactory extends Factory
{
    protected $model = AgentConversation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
        ];
    }
}
```

- [ ] **Step 6: Create AgentConversationMessageFactory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<AgentConversationMessage> */
final class AgentConversationMessageFactory extends Factory
{
    protected $model = AgentConversationMessage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'conversation_id' => AgentConversation::factory(),
            'user_id' => User::factory(),
            'agent' => 'default',
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
            'attachments' => [],
            'tool_calls' => [],
            'tool_results' => [],
            'usage' => [],
            'meta' => [],
        ];
    }

    public function fromUser(): self
    {
        return $this->state(fn (): array => ['role' => 'user']);
    }

    public function fromAssistant(): self
    {
        return $this->state(fn (): array => ['role' => 'assistant']);
    }
}
```

- [ ] **Step 7: Run Pint and verify**

Run: `vendor/bin/pint --dirty --format agent && php artisan tinker --execute "App\Models\AuditLog::factory()->make(); App\Models\AgentConversation::factory()->make(); echo 'OK';"`

- [ ] **Step 8: Commit**

```bash
git add database/factories/SocialAccountFactory.php database/factories/UserTermsAcceptanceFactory.php database/factories/AuditLogFactory.php database/factories/AgentConversationFactory.php database/factories/AgentConversationMessageFactory.php app/Models/AgentConversation.php app/Models/AgentConversationMessage.php
git commit -m "feat: add factories for SocialAccount, UserTermsAcceptance, AuditLog, AgentConversation, AgentConversationMessage"
```

---

### Task 3: Verify Existing Module Factories — Blog, Changelog, Contact, Help, Announcements, Page Builder

**Files:**
- Verify: `modules/blog/database/factories/PostFactory.php` (already exists)
- Verify: `modules/changelog/database/factories/ChangelogEntryFactory.php` (already exists)
- Verify: `modules/contact/database/factories/ContactSubmissionFactory.php` (already exists)
- Verify: `modules/help/database/factories/HelpArticleFactory.php` (already exists)
- Verify: `modules/announcements/database/factories/AnnouncementFactory.php` (already exists)
- Verify: `modules/page-builder/database/factories/PageFactory.php` (already exists)
- Verify: `modules/page-builder/database/factories/PageRevisionFactory.php` (already exists)

**Context:** These factories already exist. Do NOT recreate them. Verify they are complete and working.

- [ ] **Step 1: Verify each factory compiles and produces valid models**

Run: `php artisan tinker --execute "Modules\Blog\Models\Post::factory()->make(); Modules\Changelog\Models\ChangelogEntry::factory()->make(); echo 'OK';"` (adjust namespaces as needed)

- [ ] **Step 2: Verify models have newFactory() methods resolving correctly**

Check each model for `newFactory()` method. If missing and factory resolution fails, add it.

- [ ] **Step 3: Commit only if changes were needed**

---

### Task 4: Module Factories — CRM

**Files:**
- Create: `modules/module-crm/database/factories/ContactFactory.php`
- Create: `modules/module-crm/database/factories/DealFactory.php`
- Create: `modules/module-crm/database/factories/PipelineFactory.php`
- Create: `modules/module-crm/database/factories/ActivityFactory.php`

**Context:** CRM models: Contact has first_name, last_name, email, phone, company, position, source, status, notes, assigned_employee_id. Deal belongs to Pipeline + Contact. Pipeline has stages (array cast). Activity is polymorphic.

- [ ] **Step 1: Read all CRM model files for exact fields**

Read: `modules/module-crm/src/Models/Contact.php`, `Deal.php`, `Pipeline.php`, `Activity.php`

- [ ] **Step 2: Create PipelineFactory** (Deal depends on it)

Pipeline: `name`, `stages` (JSON array), `is_default`, `organization_id`.

- [ ] **Step 3: Create ContactFactory** (CRM Contact, not the contact module)

Contact: `first_name`, `last_name`, `email`, `phone`, `company`, `position`, `source`, `status`, `notes`, `organization_id`.

- [ ] **Step 4: Create DealFactory**

Deal: `contact_id` (CRM Contact factory), `pipeline_id` (Pipeline factory), `title`, `value`, `currency`, `stage`, `probability`, `expected_close_date`, `status`, `organization_id`.

- [ ] **Step 5: Create ActivityFactory**

Activity: Check model for exact fields. Likely polymorphic `subject_type`/`subject_id`, `type`, `description`, `organization_id`.

- [ ] **Step 6: Add newFactory() methods to models if needed**

- [ ] **Step 7: Run Pint and verify**

- [ ] **Step 8: Commit**

```bash
git add modules/module-crm/database/
git commit -m "feat: add factories for CRM Contact, Deal, Pipeline, Activity"
```

---

### Task 5: Module Factories — HR

**Files:**
- Create: `modules/module-hr/database/factories/DepartmentFactory.php`
- Create: `modules/module-hr/database/factories/EmployeeFactory.php`
- Create: `modules/module-hr/database/factories/LeaveRequestFactory.php`

**Context:** Department has name, description, head_employee_id. Employee has user_id, department_id, employee_number, name fields, position, hire_date, salary, status. LeaveRequest has employee_id, type, start/end dates, reason, status, approved_by.

- [ ] **Step 1: Read all HR model files for exact fields**

- [ ] **Step 2: Create DepartmentFactory**

- [ ] **Step 3: Create EmployeeFactory** (depends on Department + User)

- [ ] **Step 4: Create LeaveRequestFactory** (depends on Employee)

- [ ] **Step 5: Add newFactory() methods if needed**

- [ ] **Step 6: Run Pint and verify**

- [ ] **Step 7: Commit**

```bash
git add modules/module-hr/database/
git commit -m "feat: add factories for HR Department, Employee, LeaveRequest"
```

---

### Task 6: Module Factories — Reports, Dashboards

**Files:**
- Create: `modules/reports/database/factories/ReportFactory.php`
- Create: `modules/reports/database/factories/ReportOutputFactory.php`
- Create: `modules/dashboards/database/factories/DashboardFactory.php`

**Note:** Page Builder factories already exist (verified in Task 3).

- [ ] **Step 1: Read Report, ReportOutput, Dashboard model files for exact fields**

- [ ] **Step 2: Create ReportFactory** (organization_id, name, type, config, etc.)

- [ ] **Step 3: Create ReportOutputFactory** (report_id via Report factory)

- [ ] **Step 4: Create DashboardFactory** (organization_id, name, layout, etc.)

- [ ] **Step 5: Add newFactory() methods if needed**

- [ ] **Step 6: Run Pint and verify**

- [ ] **Step 7: Commit**

```bash
git add modules/reports/database/ modules/dashboards/database/
git commit -m "feat: add factories for Report, ReportOutput, Dashboard"
```

---

### Task 7: Module Factories — Billing (Gaps Only)

**Files:**
- Create: `modules/billing/database/factories/PlanFactory.php`
- Create: `modules/billing/database/factories/SubscriptionFactory.php`
- Verify: `modules/billing/database/factories/CreditFactory.php` (already exists)
- Verify: `modules/billing/database/factories/InvoiceFactory.php` (already exists)
- Verify: `modules/billing/database/factories/RefundRequestFactory.php` (already exists)

**Context:** CreditFactory, InvoiceFactory, RefundRequestFactory already exist — do NOT recreate. Plan and Subscription extend `laravelcm/laravel-subscriptions` base models — check parent class `$fillable` fields carefully.

- [ ] **Step 1: Read Plan and Subscription model files and their parent classes**

Read: `modules/billing/src/Models/Plan.php`, `Subscription.php`, and the laravelcm parent classes for their `$fillable`.

- [ ] **Step 2: Create PlanFactory** — Use parent class fields from laravelcm package

- [ ] **Step 3: Create SubscriptionFactory** — Use parent class fields from laravelcm package

- [ ] **Step 4: Verify existing billing factories compile correctly**

Run: `php artisan tinker --execute "Modules\Billing\Models\Credit::factory()->make(); echo 'OK';"`

- [ ] **Step 5: Add newFactory() methods if needed**

- [ ] **Step 6: Run Pint and verify**

- [ ] **Step 7: Commit**

```bash
git add modules/billing/database/
git commit -m "feat: add factories for billing Plan and Subscription"
```

---

### Task 8: App Model Policies (Batch 1 — User-Scoped)

**Files:**
- Create: `app/Policies/NotificationPreferencePolicy.php`
- Create: `app/Policies/SocialAccountPolicy.php`
- Create: `app/Policies/UserTermsAcceptancePolicy.php`
- Create: `app/Policies/AgentConversationPolicy.php`
- Create: `app/Policies/AgentConversationMessagePolicy.php`

**Context:** Follow `app/Policies/UserPolicy.php` pattern. These are user-owned — only the owning user can CRUD. Use `final class`, `declare(strict_types=1)`. Laravel auto-discovers policies matching `App\Policies\{Model}Policy`.

- [ ] **Step 1: Create NotificationPreferencePolicy**

Owner-only: user can only manage their own preferences.

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotificationPreference;
use App\Models\User;

final class NotificationPreferencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    public function delete(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }
}
```

- [ ] **Step 2: Create SocialAccountPolicy** — Owner-only pattern (same as above but with SocialAccount)

- [ ] **Step 3: Create UserTermsAcceptancePolicy** — Owner can view own; admin can viewAny

- [ ] **Step 4: Create AgentConversationPolicy** — Owner-only for conversations

- [ ] **Step 5: Create AgentConversationMessagePolicy** — Delegate to conversation ownership

- [ ] **Step 6: Run Pint**

- [ ] **Step 7: Commit**

```bash
git add app/Policies/NotificationPreferencePolicy.php app/Policies/SocialAccountPolicy.php app/Policies/UserTermsAcceptancePolicy.php app/Policies/AgentConversationPolicy.php app/Policies/AgentConversationMessagePolicy.php
git commit -m "feat: add user-scoped policies for NotificationPreference, SocialAccount, UserTermsAcceptance, AgentConversation, AgentConversationMessage"
```

---

### Task 9: App Model Policies (Batch 2 — Org-Scoped)

**Files:**
- Create: `app/Policies/AuditLogPolicy.php`
- Create: `app/Policies/CategoryPolicy.php`
- Create: `app/Policies/OrganizationDomainPolicy.php`
- Create: `app/Policies/SlugRedirectPolicy.php`
- Create: `app/Policies/VisibilityDemoPolicy.php`

**Context:** Org-scoped models. Use `$user->canInOrganization('permission', $organization)` pattern from `UpdateBrandingRequest`. AuditLog is read-only for admins.

- [ ] **Step 1: Create AuditLogPolicy** — Admin view-only (no create/update/delete)

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

final class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('access admin panel');
    }

    public function view(User $user, AuditLog $log): bool
    {
        return $user->can('access admin panel');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuditLog $log): bool
    {
        return false;
    }

    public function delete(User $user, AuditLog $log): bool
    {
        return false;
    }
}
```

- [ ] **Step 2: Create CategoryPolicy** — Org-scoped CRUD with `canInOrganization`

- [ ] **Step 3: Create OrganizationDomainPolicy** — Org-admin only (org.settings.manage)

- [ ] **Step 4: Create SlugRedirectPolicy** — Admin-only read, system-managed create/update

- [ ] **Step 5: Create VisibilityDemoPolicy** — Follow HasVisibility patterns from ShareablePolicy

- [ ] **Step 6: Run Pint**

- [ ] **Step 7: Commit**

```bash
git add app/Policies/AuditLogPolicy.php app/Policies/CategoryPolicy.php app/Policies/OrganizationDomainPolicy.php app/Policies/SlugRedirectPolicy.php app/Policies/VisibilityDemoPolicy.php
git commit -m "feat: add org-scoped policies for AuditLog, Category, OrganizationDomain, SlugRedirect, VisibilityDemo"
```

---

### Task 10: App Model Policies (Batch 3 — Admin & Misc)

**Files:**
- Create: `app/Policies/EmbeddingDemoPolicy.php`
- Create: `app/Policies/EnterpriseInquiryPolicy.php`
- Create: `app/Policies/ModelFlagPolicy.php`
- Create: `app/Policies/TermsVersionPolicy.php`
- Create: `app/Policies/VoucherScopePolicy.php`

- [ ] **Step 1: Create EmbeddingDemoPolicy** — Admin-only (access admin panel)

- [ ] **Step 2: Create EnterpriseInquiryPolicy** — Public create, admin view/manage

- [ ] **Step 3: Create ModelFlagPolicy** — Admin-only CRUD

- [ ] **Step 4: Create TermsVersionPolicy** — Admin manage, all users can view active

- [ ] **Step 5: Create VoucherScopePolicy** — Admin-only CRUD

- [ ] **Step 6: Run Pint**

- [ ] **Step 7: Commit**

```bash
git add app/Policies/EmbeddingDemoPolicy.php app/Policies/EnterpriseInquiryPolicy.php app/Policies/ModelFlagPolicy.php app/Policies/TermsVersionPolicy.php app/Policies/VoucherScopePolicy.php
git commit -m "feat: add admin policies for EmbeddingDemo, EnterpriseInquiry, ModelFlag, TermsVersion, VoucherScope"
```

---

### Task 11: Module Policies — Blog, Changelog, Help, Announcements, Contact

**Files:**
- Create policies in each module's appropriate location
- Register in module service providers if auto-discovery doesn't work

**Context:** Module models live in `Modules\{Name}\Models\*`. Policies should go alongside in `Modules\{Name}\Policies\*` or `app/Policies/` if auto-discovery is simpler. Check existing module structure for convention.

- [ ] **Step 1: Determine module policy location convention**

Check if any module already has a Policies directory. If not, create them in the module namespace.

- [ ] **Step 2: Create PostPolicy** — Org-scoped: members can CRUD within their org, published posts viewable by all

- [ ] **Step 3: Create ChangelogEntryPolicy** — Org-scoped: admins manage, public view published

- [ ] **Step 4: Create HelpArticlePolicy** — Org-scoped: admins manage, public view

- [ ] **Step 5: Create AnnouncementPolicy** — Check if `tests/Feature/AnnouncementPolicyTest.php` exists and what it expects. Org-scoped.

- [ ] **Step 6: Create ContactSubmissionPolicy** — Org-scoped: admins view/manage, public create

- [ ] **Step 7: Register policies in module service providers if needed**

If auto-discovery doesn't work for module namespaces, add `Gate::policy()` calls in each module's service provider.

- [ ] **Step 8: Run Pint**

- [ ] **Step 9: Commit**

```bash
git add modules/blog/ modules/changelog/ modules/help/ modules/announcements/ modules/contact/
git commit -m "feat: add policies for blog Post, ChangelogEntry, HelpArticle, Announcement, ContactSubmission"
```

---

### Task 12: Module Policies — CRM, HR

**Files:**
- Create CRM policies: Contact, Deal, Pipeline, Activity
- Create HR policies: Department, Employee, LeaveRequest

- [ ] **Step 1: Create CRM policies** — All org-scoped with CRUD permissions

- [ ] **Step 2: Create HR policies** — Department (admin), Employee (HR manager), LeaveRequest (employee + manager)

- [ ] **Step 3: Register in module service providers if needed**

- [ ] **Step 4: Run Pint**

- [ ] **Step 5: Commit**

```bash
git add modules/module-crm/ modules/module-hr/
git commit -m "feat: add policies for CRM and HR module models"
```

---

### Task 13: Module Policies — Billing, Page Builder, Reports, Dashboards

**Files:**
- Billing: Plan, Credit, Invoice, Subscription, RefundRequest, WebhookLog
- Page Builder: Page, PageRevision
- Reports: Report, ReportOutput
- Dashboards: Dashboard

- [ ] **Step 1: Create billing policies** — Plan (admin), Credit/Invoice/Subscription (org-scoped), RefundRequest (org-scoped), WebhookLog (admin-only)

- [ ] **Step 2: Create Page Builder policies** — Org-scoped CRUD

- [ ] **Step 3: Create Reports policies** — Org-scoped CRUD

- [ ] **Step 4: Create Dashboard policy** — Org-scoped CRUD

- [ ] **Step 5: Register in module service providers**

- [ ] **Step 6: Run Pint**

- [ ] **Step 7: Commit**

```bash
git add modules/billing/ modules/page-builder/ modules/reports/ modules/dashboards/
git commit -m "feat: add policies for billing, page-builder, reports, dashboards modules"
```

---

### Task 14: Extract FormRequests — Settings Controllers

**Files:**
- Create: `app/Http/Requests/Settings/UpdateOrgSlugRequest.php`
- Create: `app/Http/Requests/Settings/UpdateNotificationPreferencesRequest.php`
- Create: `app/Http/Requests/Settings/UpdateOrgBrandingUserControlsRequest.php`
- Create: `app/Http/Requests/Settings/StoreOrgDomainRequest.php`
- Create: `app/Http/Requests/Settings/UpdateOrgFeaturesRequest.php`
- Create: `app/Http/Requests/Settings/StoreOrgRoleRequest.php`
- Modify: Corresponding controllers to use new FormRequests

**Context:** Follow `app/Http/Requests/Settings/UpdateBrandingRequest.php` pattern. Use `TenantContext::get()` in `authorize()`. Array-style rules.

- [ ] **Step 1: Create UpdateOrgSlugRequest**

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Organization;
use App\Rules\SlugAvailable;
use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrgSlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization instanceof Organization && $this->user()?->canInOrganization('org.settings.manage', $organization);
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        $organization = TenantContext::get();

        return [
            'slug' => ['required', 'string', new SlugAvailable($organization?->id)],
            'confirmed' => ['required', 'accepted'],
        ];
    }
}
```

- [ ] **Step 2: Update OrgSlugController to use UpdateOrgSlugRequest**

Replace `Request $request` with `UpdateOrgSlugRequest $request` in the `update` method. Remove inline `$request->validate()` call. Use `$request->validated()` instead.

- [ ] **Step 3: Create remaining 5 settings FormRequests**

Read each controller's inline validation, extract to FormRequest. For each:
1. Read the controller file
2. Create FormRequest with matching rules
3. Update controller to type-hint the FormRequest
4. Replace `$request->validate()` with `$request->validated()`

- [ ] **Step 4: Update all 6 settings controllers**

- [ ] **Step 5: Run Pint**

- [ ] **Step 6: Run existing tests to verify no regressions**

Run: `php artisan test --compact`

- [ ] **Step 7: Commit**

```bash
git add app/Http/Requests/Settings/ app/Http/Controllers/Settings/
git commit -m "refactor: extract inline validation to FormRequests in settings controllers"
```

---

### Task 15: Extract FormRequests — API, Search, Preferences, Wizard Controllers

**Files:**
- Create: `app/Http/Requests/Api/StoreChatMessageRequest.php`
- Create: `app/Http/Requests/Api/UpdateConversationRequest.php`
- Create: `app/Http/Requests/Crm/UpdateDealRequest.php`
- Create: `app/Http/Requests/Hr/UpdateLeaveRequestRequest.php`
- Create: `app/Http/Requests/SearchRequest.php`
- Create: `app/Http/Requests/UpdateUserPreferencesRequest.php`
- Create: `app/Http/Requests/StoreWizardStep1Request.php`
- Create: `app/Http/Requests/StoreWizardStep2Request.php`
- Create: `app/Http/Requests/StoreWizardStep3Request.php`
- Modify: All corresponding controllers

**Context:** ChatController has complex closure validation for conversation_id. Move the closure to an `after()` callback or a custom rule. WizardController has 3 methods with inline validation.

- [ ] **Step 1: Create API FormRequests**

Read `ChatController` and `ConversationController` for exact rules. Handle the closure validation in ChatController by using a custom rule class or `after()` method.

- [ ] **Step 2: Create CRM/HR FormRequests**

Read `DealController` and `LeaveRequestController` for exact rules.

- [ ] **Step 3: Create Search and UserPreferences FormRequests**

- [ ] **Step 4: Create Wizard FormRequests (3 steps)**

Read `WizardController` for all 3 step validations.

- [ ] **Step 5: Update all controllers**

- [ ] **Step 6: Run Pint**

- [ ] **Step 7: Run existing tests**

Run: `php artisan test --compact`

- [ ] **Step 8: Commit**

```bash
git add app/Http/Requests/ app/Http/Controllers/
git commit -m "refactor: extract inline validation to FormRequests in API, CRM, HR, search, wizard controllers"
```

---

## Phase 2: Security Hardening

### Task 16: Job Resilience — GenerateEmbedding & GenerateOgImageJob

**Files:**
- Modify: `app/Jobs/GenerateEmbedding.php`
- Modify: `app/Jobs/GenerateOgImageJob.php`

**Context:** GenerateEmbedding already has `$tries = 3` and `$backoff = 60`. It needs `$timeout` and `failed()`. GenerateOgImageJob needs all properties. Read each file before modifying.

- [ ] **Step 1: Read current GenerateOgImageJob**

- [ ] **Step 2: Add resilience to GenerateEmbedding**

Add after existing properties:
```php
public int $timeout = 120;

public function failed(Throwable $exception): void
{
    Log::error('GenerateEmbedding failed', [
        'model' => $this->model::class,
        'model_id' => $this->model->getKey(),
        'column' => $this->textColumn,
        'error' => $exception->getMessage(),
    ]);
}
```

Add `use Throwable;` and `use Illuminate\Support\Facades\Log;` imports.

- [ ] **Step 3: Add resilience to GenerateOgImageJob**

Add: `$tries = 2`, `$timeout = 60`, `$backoff = [5, 15]`, and `failed()` method with logging.

- [ ] **Step 4: Run Pint**

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/GenerateEmbedding.php app/Jobs/GenerateOgImageJob.php
git commit -m "fix: add timeout and failure handling to GenerateEmbedding and GenerateOgImageJob"
```

---

### Task 17: Job Resilience — NotifyUsersOfNewTermsVersion & VerifyOrganizationDomain

**Files:**
- Modify: `app/Jobs/NotifyUsersOfNewTermsVersion.php`
- Modify: `app/Jobs/VerifyOrganizationDomain.php`

- [ ] **Step 1: Read both job files**

- [ ] **Step 2: Add resilience to NotifyUsersOfNewTermsVersion**

Add: `$tries = 3`, `$timeout = 30`, `$backoff = [10, 30]`, `failed()` with logging.

- [ ] **Step 3: Add resilience to VerifyOrganizationDomain**

Add: `$tries = 3`, `$timeout = 30`, `$backoff = [10, 30, 60]`, `failed()` with logging.

- [ ] **Step 4: Run Pint**

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/NotifyUsersOfNewTermsVersion.php app/Jobs/VerifyOrganizationDomain.php
git commit -m "fix: add retry/timeout/failure handling to NotifyUsersOfNewTermsVersion and VerifyOrganizationDomain"
```

---

### Task 18: Job Resilience — ProcessWebhookJob

**Files:**
- Modify: `app/Jobs/ProcessWebhookJob.php`

**Context:** Extends `Spatie\WebhookClient\Jobs\ProcessWebhookJob`. Check parent class for existing `$tries`/`$timeout`/`$backoff` before adding to avoid conflicts. The commented-out code is for *outgoing* webhook forwarding, not incoming verification — leave as-is but clean up the comment.

- [ ] **Step 1: Check Spatie parent class properties**

Run: `grep -n 'tries\|timeout\|backoff\|failed' vendor/spatie/laravel-webhook-client/src/Jobs/ProcessWebhookJob.php`

- [ ] **Step 2: Add non-conflicting resilience properties**

Based on parent class check, add only properties not already defined. Add `failed()` method.

- [ ] **Step 3: Clean up the commented forwarding code** — make the comment clearer that it's a forwarding example, not incoming verification

- [ ] **Step 4: Run Pint**

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/ProcessWebhookJob.php
git commit -m "fix: add failure handling to ProcessWebhookJob, clean up forwarding comment"
```

---

### Task 19: Webhook Config Verification

**Files:**
- Read: `config/webhook-client.php`
- Possibly modify: `config/webhook-client.php`, `.env.example`

- [ ] **Step 1: Read webhook-client config**

Verify `signing_secret` references a config value (not `env()` directly in the job).

- [ ] **Step 2: Verify .env.example has WEBHOOK_SECRET**

Check `.env.example` for webhook-related env vars.

- [ ] **Step 3: Fix any issues found**

If signing_secret uses `env()` in config, that's correct (config files are the right place for `env()`). If it's hardcoded or missing, fix it.

- [ ] **Step 4: Commit if changes made**

```bash
git add config/webhook-client.php .env.example
git commit -m "fix: verify webhook signing secret configuration"
```

---

### Task 20: BelongsToOrganization Trait

**Files:**
- Modify: `app/Models/AuditLog.php`
- Modify: `app/Models/OrganizationDomain.php`
- Modify: `app/Models/SlugRedirect.php`

**Context:** Add `use BelongsToOrganization;` trait. Do NOT add to VisibilityDemo (uses HasVisibility). **CRITICAL:** All three models already define their own `organization()` method. The `BelongsToOrganization` trait also defines `organization()`. You MUST remove the existing manual `organization()` method from each model before adding the trait, or PHP will throw a fatal error due to method collision.

- [ ] **Step 1: Read AuditLog, OrganizationDomain, SlugRedirect models — confirm each has a manual `organization()` method**

- [ ] **Step 2: Add BelongsToOrganization to AuditLog**

1. Add import: `use App\Models\Concerns\BelongsToOrganization;`
2. Add `use BelongsToOrganization;` in class body
3. **Remove** the existing manual `organization()` BelongsTo relationship method (the trait provides it)

- [ ] **Step 3: Add BelongsToOrganization to OrganizationDomain**

Same pattern: add trait, remove manual `organization()` method.

- [ ] **Step 4: Add BelongsToOrganization to SlugRedirect**

Same pattern: add trait, remove manual `organization()` method.

- [ ] **Step 5: Run Pint**

- [ ] **Step 6: Run existing tests to verify no regressions**

Run: `php artisan test --compact`

- [ ] **Step 7: Commit**

```bash
git add app/Models/AuditLog.php app/Models/OrganizationDomain.php app/Models/SlugRedirect.php
git commit -m "fix: add BelongsToOrganization trait to AuditLog, OrganizationDomain, SlugRedirect"
```

---

### Task 21: N+1 Prevention Verification

**Context:** `Model::shouldBeStrict()` is already called in `AppServiceProvider::bootStrictDefaults()` (line 287). This enables `preventLazyLoading` in non-production. No code change needed — just verify.

- [ ] **Step 1: Verify shouldBeStrict is active**

Run: `grep -n 'shouldBeStrict\|preventLazyLoading' app/Providers/AppServiceProvider.php`

Expected: `Model::shouldBeStrict(! $this->app->isProduction());`

- [ ] **Step 2: Document finding — no change needed**

N+1 prevention is already active via `shouldBeStrict()`. This task is complete.

---

## Phase 3: Test Coverage

### Task 22: Policy Tests — User-Scoped Policies

**Files:**
- Create: `tests/Feature/Policies/NotificationPreferencePolicyTest.php`
- Create: `tests/Feature/Policies/SocialAccountPolicyTest.php`
- Create: `tests/Feature/Policies/AgentConversationPolicyTest.php`

**Context:** Follow Pest test pattern from `tests/Feature/Controllers/SessionControllerTest.php`. Place in `tests/Feature/` to match existing convention (`tests/Feature/AnnouncementPolicyTest.php`). Test that owners can access, non-owners cannot.

- [ ] **Step 1: Create NotificationPreferencePolicyTest**

```php
<?php

declare(strict_types=1);

use App\Models\NotificationPreference;
use App\Models\User;

it('allows user to view own notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('view', $preference))->toBeTrue();
});

it('prevents user from viewing another user notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $other->id]);

    expect($user->can('view', $preference))->toBeFalse();
});

it('allows user to update own notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $user->id]);

    expect($user->can('update', $preference))->toBeTrue();
});

it('prevents user from updating another user notification preference', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $preference = NotificationPreference::factory()->create(['user_id' => $other->id]);

    expect($user->can('update', $preference))->toBeFalse();
});
```

- [ ] **Step 2: Create SocialAccountPolicyTest** — Same owner-only pattern

- [ ] **Step 3: Create AgentConversationPolicyTest** — Owner-only pattern

- [ ] **Step 4: Run tests**

Run: `php artisan test --compact --filter=PolicyTest`

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Policies/
git commit -m "test: add policy tests for user-scoped models"
```

---

### Task 23: Policy Tests — Org-Scoped & Admin Policies

**Files:**
- Create: `tests/Feature/Policies/AuditLogPolicyTest.php`
- Create: `tests/Feature/Policies/CategoryPolicyTest.php`
- Create: `tests/Feature/Policies/TermsVersionPolicyTest.php`

**Context:** Org-scoped tests need tenant context setup. Admin tests need permission assignment via Spatie.

- [ ] **Step 1: Create AuditLogPolicyTest** — Admin can view, non-admin cannot, nobody can create/update/delete

- [ ] **Step 2: Create CategoryPolicyTest** — Org member with permission can CRUD, non-member cannot

- [ ] **Step 3: Create TermsVersionPolicyTest** — Admin can manage, all users can view

- [ ] **Step 4: Run tests**

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Policies/
git commit -m "test: add policy tests for org-scoped and admin models"
```

---

### Task 24: Job Tests

**Files:**
- Create: `tests/Feature/Jobs/GenerateEmbeddingTest.php`
- Create: `tests/Feature/Jobs/GenerateOgImageJobTest.php`
- Create: `tests/Feature/Jobs/NotifyUsersOfNewTermsVersionTest.php`
- Create: `tests/Feature/Jobs/ProcessWebhookJobTest.php`
- Create: `tests/Feature/Jobs/VerifyOrganizationDomainTest.php`

**Context:** Test job dispatch, failure handling, retry config. Use `Queue::fake()` for dispatch tests. Mock external services.

- [ ] **Step 1: Create GenerateEmbeddingTest**

```php
<?php

declare(strict_types=1);

use App\Jobs\GenerateEmbedding;

it('has correct retry configuration', function (): void {
    $model = \App\Models\EmbeddingDemo::factory()->create();

    $job = new GenerateEmbedding(
        model: $model,
        textColumn: 'title',
    );

    expect($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(120)
        ->and($job->backoff)->toBe(60);
});

it('is dispatched to the queue', function (): void {
    Queue::fake();

    $model = \App\Models\EmbeddingDemo::factory()->create();

    GenerateEmbedding::dispatch($model, 'title');

    Queue::assertPushed(GenerateEmbedding::class);
});
```

- [ ] **Step 2: Create remaining job tests** — Each tests config properties and dispatch

- [ ] **Step 3: Create ProcessWebhookJobTest** — Verify it extends Spatie base, has middleware

- [ ] **Step 4: Run tests**

Run: `php artisan test --compact --filter=JobTest`

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Jobs/
git commit -m "test: add job tests for all 5 queue jobs"
```

---

### Task 25: Settings Controller Tests

**Files:**
- Create: `tests/Feature/Settings/OrgSlugControllerTest.php`
- Create: `tests/Feature/Settings/NotificationPreferencesControllerTest.php`
- Create: `tests/Feature/Settings/OrgDomainsControllerTest.php`
- Create: `tests/Feature/Settings/OrgRolesControllerTest.php`
- Create: `tests/Feature/Settings/OrgFeaturesControllerTest.php`
- Create: `tests/Feature/Settings/OrgBrandingUserControlsControllerTest.php`

**Context:** Settings routes require auth + tenant middleware. Set up tenant context in tests. Follow `SessionControllerTest` patterns.

- [ ] **Step 1: Create OrgSlugControllerTest**

Test: renders page, updates slug, validates required fields, rejects invalid slug, creates redirect, requires authentication, requires org permissions.

- [ ] **Step 2: Create NotificationPreferencesControllerTest**

Test: renders page, updates preferences, validates input, requires auth.

- [ ] **Step 3: Create remaining 4 settings controller tests**

- [ ] **Step 4: Run tests**

Run: `php artisan test --compact --filter=Settings`

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Settings/
git commit -m "test: add feature tests for settings controllers"
```

---

### Task 26: API Endpoint Tests

**Files:**
- Create: `tests/Feature/Api/V1/ApiEndpointSecurityTest.php`

**Context:** Test auth requirement (401), rate limiting (429), validation (422) for all API routes. Check `routes/api.php` for route names.

- [ ] **Step 1: Read routes/api.php for all route definitions**

- [ ] **Step 2: Create API security tests**

Test for each API group:
- Unauthenticated request returns 401
- Rate limiting returns 429 after limit
- Invalid input returns 422
- Valid request returns expected status code

- [ ] **Step 3: Run tests**

Run: `php artisan test --compact --filter=Api`

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Api/
git commit -m "test: add API endpoint security tests (auth, rate-limiting, validation)"
```

---

### Task 27: Tenant Isolation Tests

**Files:**
- Create: `tests/Feature/TenantIsolationTest.php` (or extend existing `MultiTenancyHardeningTest.php`)

**Context:** Verify org-scoped models only return data for current tenant. Test BelongsToOrganization trait scoping.

- [ ] **Step 1: Read existing MultiTenancyHardeningTest.php**

Check what's already tested to avoid duplication.

- [ ] **Step 2: Add tenant isolation tests for newly-added BelongsToOrganization models**

Test: AuditLog, OrganizationDomain, SlugRedirect — queries scoped to current org, cross-org data inaccessible.

- [ ] **Step 3: Run tests**

Run: `php artisan test --compact --filter=Tenant`

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/TenantIsolationTest.php
git commit -m "test: add tenant isolation tests for newly org-scoped models"
```

---

### Task 28: Action Tests (Priority Actions)

**Files:**
- Create tests for highest-priority untested actions

**Context:** Check `app/Actions/` for all actions. Cross-reference with `tests/Unit/Actions/` for existing coverage. Prioritize actions used in critical flows.

- [ ] **Step 1: Identify untested actions**

Run: `ls app/Actions/` and compare with `ls tests/Unit/Actions/`

- [ ] **Step 2: Create tests for top 10 untested actions**

For each action: test `handle()` with valid input, test edge cases, test DB side effects.

- [ ] **Step 3: Run tests**

Run: `php artisan test --compact --filter=Actions`

- [ ] **Step 4: Commit**

```bash
git add tests/Unit/Actions/
git commit -m "test: add tests for priority untested actions"
```

---

## Phase 4: Frontend Quality

### Task 29: Accessibility — Data Table Component

**Files:**
- Modify: `resources/js/components/data-table/data-table.tsx`

**Context:** 8 interactive elements missing aria-labels. Read the file first, identify all buttons/controls without labels, add appropriate aria-label attributes.

- [ ] **Step 1: Read data-table.tsx and identify all unlabeled interactive elements**

- [ ] **Step 2: Add aria-labels to all icon-only buttons**

Examples: pagination buttons, sort buttons, filter toggles, column visibility toggles. Use descriptive labels like `aria-label="Go to next page"`, `aria-label="Sort by name ascending"`.

- [ ] **Step 3: Ensure all form controls have labels**

Search inputs, select dropdowns, etc.

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/data-table/data-table.tsx
git commit -m "fix(a11y): add aria-labels to data-table interactive elements"
```

---

### Task 30: Accessibility — Composed Components

**Files:**
- Modify: `resources/js/components/composed/notification-center.tsx`
- Modify: `resources/js/components/composed/file-manager.tsx`
- Modify: `resources/js/components/composed/command-bar.tsx`

- [ ] **Step 1: Read each file, identify unlabeled elements**

- [ ] **Step 2: Fix notification-center** (3 gaps)

- [ ] **Step 3: Fix file-manager** (2 gaps)

- [ ] **Step 4: Fix command-bar** (1 gap)

- [ ] **Step 5: Commit**

```bash
git add resources/js/components/composed/
git commit -m "fix(a11y): add aria-labels to notification-center, file-manager, command-bar"
```

---

### Task 31: Accessibility — Remaining Components

**Files:**
- Multiple component files across `resources/js/components/`

**Context:** ~135 remaining elements across ~15+ files. Systematically scan and fix.

- [ ] **Step 1: Search for all buttons/links without aria-label**

Run: `grep -rn '<button\|<Button\|onClick' resources/js/components/ | grep -v 'aria-label'`

Focus on icon-only buttons and clickable elements without visible text.

- [ ] **Step 2: Fix by component category** — prioritize user-facing components

- [ ] **Step 3: Convert clickable divs to semantic elements** where appropriate

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/
git commit -m "fix(a11y): add aria-labels to remaining interactive elements across components"
```

---

### Task 32: Type Definitions Consolidation

> **Dependency note:** This task should run AFTER Phase 3 tests to avoid import conflicts with test files that may reference the same types. Tasks 29-31 (ARIA fixes) can run in parallel with Phase 3.

**Files:**
- Create: `resources/js/types/organizations.ts`
- Create: `resources/js/types/billing.ts`
- Create: `resources/js/types/crm.ts`
- Create: `resources/js/types/content.ts`
- Create: `resources/js/types/data-table.ts`
- Modify: Pages that define local interfaces to import from shared types

- [ ] **Step 1: Search for locally-defined interfaces across pages**

Run: `grep -rn 'interface \|type ' resources/js/pages/ | head -50`

- [ ] **Step 2: Create organizations.ts** — Extract OrganizationSummary, etc.

- [ ] **Step 3: Create billing.ts** — Extract Plan, Subscription, etc.

- [ ] **Step 4: Create crm.ts** — Extract Contact, Deal, Pipeline, etc.

- [ ] **Step 5: Create content.ts** — Extract Post, HelpArticle, etc.

- [ ] **Step 6: Create data-table.ts** — Extract column config types

- [ ] **Step 7: Update page imports** to use shared types

- [ ] **Step 8: Run TypeScript check**

Run: `npx tsc --noEmit`

- [ ] **Step 9: Commit**

```bash
git add resources/js/types/ resources/js/pages/
git commit -m "refactor: consolidate TypeScript type definitions into shared type files"
```

---

### Task 33: Large Component Extraction — users/table.tsx

**Files:**
- Modify: `resources/js/pages/users/table.tsx` (637 lines)
- Create: extracted sub-components

- [ ] **Step 1: Read users/table.tsx**

Identify logical sections: column definitions, filters, toolbar, bulk actions.

- [ ] **Step 2: Extract column definitions** to `resources/js/pages/users/columns.tsx`

- [ ] **Step 3: Extract toolbar/filters** to `resources/js/pages/users/table-toolbar.tsx` if substantial

- [ ] **Step 4: Verify the page still renders correctly**

Run: `npx tsc --noEmit`

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/users/
git commit -m "refactor: extract users table column definitions and toolbar into separate files"
```

---

### Task 34: Large Component Extraction — welcome.tsx & dashboard.tsx

**Files:**
- Modify: `resources/js/pages/welcome.tsx` (583 lines)
- Modify: `resources/js/pages/dashboard.tsx` (450 lines)
- Create: extracted sub-components

- [ ] **Step 1: Read welcome.tsx, identify sections**

Likely: hero, features, pricing, footer, CTA sections.

- [ ] **Step 2: Extract major sections** into `resources/js/pages/welcome/` directory

- [ ] **Step 3: Read dashboard.tsx, identify sections**

Likely: stat cards, charts, activity feed, quick actions.

- [ ] **Step 4: Extract major sections** into `resources/js/pages/dashboard/` directory or `resources/js/components/dashboard/`

- [ ] **Step 5: Verify TypeScript compilation**

Run: `npx tsc --noEmit`

- [ ] **Step 6: Commit**

```bash
git add resources/js/pages/welcome* resources/js/pages/dashboard*
git commit -m "refactor: extract welcome and dashboard page sections into focused components"
```

---

### Task 35: Final Verification

- [ ] **Step 1: Run full test suite**

Run: `php artisan test --compact`

- [ ] **Step 2: Run Pint on all PHP**

Run: `vendor/bin/pint --format agent`

- [ ] **Step 3: Run TypeScript check**

Run: `npx tsc --noEmit`

- [ ] **Step 4: Run ESLint**

Run: `npx eslint resources/js/` (ESLint v10 uses flat config — no `--ext` flag)

- [ ] **Step 5: Build frontend**

Run: `npm run build`

- [ ] **Step 6: Verify all success criteria from spec**

Cross-reference `docs/superpowers/specs/2026-03-24-codebase-hardening-design.md` success criteria table.

- [ ] **Step 7: Commit any final fixes**
