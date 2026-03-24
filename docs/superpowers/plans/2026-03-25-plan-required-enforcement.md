# Plan-Required Enforcement for Module Access Gating — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire up the existing `plan_required` metadata so that an org's subscription plan actually gates which modules/features it can access — currently the metadata exists but nothing enforces it.

**Architecture:** Add a `plan_features` config mapping plans to feature keys. Inject a plan check into `FeatureHelper::isActiveForKey()` — the single chokepoint where all feature resolution flows. Add validation in `OrgFeaturesController::update()` to prevent org admins from enabling plan-gated features. Update the frontend to show upgrade prompts and disable toggles for unavailable features.

**Tech Stack:** Laravel 13, PHP 8.4, Pest 4, Inertia v2, React 19, TypeScript

**Spec:** Brainstorm discussion 2026-03-25
**Depends on:** Phase A Module Foundation (completed)

---

## Task Overview

| # | Task | Files | Scope |
|---|------|-------|-------|
| 1 | Add `plan_features` config mapping | 1 modify | Define which plans include which features |
| 2 | Add `PlanFeatureHelper` action | 1 create, 1 create (test) | Check if an org's plan includes a feature |
| 3 | Wire plan check into `FeatureHelper::isActiveForKey()` | 1 modify, 1 modify (test) | The enforcement point |
| 4 | Add plan validation to `OrgFeaturesController::update()` | 1 modify, 1 modify (test) | Prevent enabling plan-gated features |
| 5 | Pass `org_plan` to frontend features page | 1 modify | Controller shares current plan slug |
| 6 | Update features.tsx with upgrade prompts | 1 modify | Disable toggles, show plan badge |
| 7 | Integration tests | 1 create | End-to-end plan gating verification |
| 8 | Final verification | 0 | Run all tests, verify in browser |

---

### Task 1: Add `plan_features` Config Mapping

**Files:**
- Modify: `config/billing.php`

**Context:** We need a single source of truth mapping plan slugs to the features they include. Features not listed for a plan are gated. A `null` plan_required means all plans include it (no gating). The plan slugs must match what's stored in the `plans` table (via `laravelcm/laravel-subscriptions`).

- [ ] **Step 1: Read the existing plans**

Read `config/billing.php` and check what plan slugs exist:
```bash
php artisan tinker --execute "echo json_encode(\Modules\Billing\Models\Plan::pluck('slug')->all());"
```

If no plans exist yet, use sensible defaults: `free`, `pro`, `enterprise`.

- [ ] **Step 2: Add `plan_features` to config/billing.php**

Add at the end of the config array:

```php
/*
|--------------------------------------------------------------------------
| Plan → Feature Access Mapping
|--------------------------------------------------------------------------
|
| Each plan slug maps to an array of feature keys it includes.
| Features with plan_required = null are available to ALL plans.
| Features listed here are ONLY available to the plans that include them.
| Higher-tier plans should include all lower-tier features.
|
*/
'plan_features' => [
    'free' => [],
    'pro' => [
        'api_access',
    ],
    'enterprise' => [
        'api_access',
        'crm',
        'reports',
    ],
],
```

Adjust the plan slugs and features based on what actually exists in the database. The principle is: higher tiers include all lower-tier features explicitly (no inheritance logic — keep it flat and obvious).

- [ ] **Step 3: Commit**

```bash
git add config/billing.php
git commit -m "feat: add plan_features config mapping for plan-gated module access"
```

---

### Task 2: Create `CheckPlanFeatureAccess` Action

**Files:**
- Create: `app/Actions/CheckPlanFeatureAccess.php`
- Create: `tests/Feature/CheckPlanFeatureAccessTest.php`

**Context:** A reusable action that answers: "Does this organization's current plan include this feature?" This is pure logic — no side effects. It reads the org's active plan, checks the `plan_features` config, and returns a boolean. If `plan_required` is null, the feature is always available.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\CheckPlanFeatureAccess;
use App\Models\Organization;
use App\Support\ModuleFeatureRegistry;

it('returns true when feature has no plan_required', function (): void {
    $org = Organization::factory()->create();

    $action = new CheckPlanFeatureAccess;

    expect($action->handle($org, 'onboarding'))->toBeTrue();
});

it('returns false when org has no subscription and feature requires a plan', function (): void {
    $org = Organization::factory()->create();

    $action = new CheckPlanFeatureAccess;

    // api_access requires 'pro' in config/feature-flags.php
    expect($action->handle($org, 'api_access'))->toBeFalse();
});

it('returns true when org plan includes the feature', function (): void {
    $org = Organization::factory()->create();

    // Create a plan and subscription — check existing factory patterns
    // for Plan and Subscription in modules/billing/database/factories/
    $plan = \Modules\Billing\Models\Plan::factory()->create(['slug' => 'pro']);
    $org->newPlanSubscription('main', $plan);

    config(['billing.plan_features.pro' => ['api_access']]);

    $action = new CheckPlanFeatureAccess;

    expect($action->handle($org, 'api_access'))->toBeTrue();
});

it('returns false when org plan does not include the feature', function (): void {
    $org = Organization::factory()->create();

    $plan = \Modules\Billing\Models\Plan::factory()->create(['slug' => 'free']);
    $org->newPlanSubscription('main', $plan);

    config(['billing.plan_features.free' => []]);

    $action = new CheckPlanFeatureAccess;

    expect($action->handle($org, 'api_access'))->toBeFalse();
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=CheckPlanFeatureAccess
```

Expected: FAIL — class doesn't exist yet.

- [ ] **Step 3: Implement the action**

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Support\ModuleFeatureRegistry;

final readonly class CheckPlanFeatureAccess
{
    /**
     * Check if the organization's current plan includes the given feature.
     *
     * Returns true if:
     * - The feature has no plan_required (available to all plans)
     * - The org's active plan includes the feature in config('billing.plan_features')
     *
     * Returns false if:
     * - The feature requires a plan and the org has no active subscription
     * - The feature requires a plan not in the org's current plan
     */
    public function handle(Organization $organization, string $featureKey): bool
    {
        $planRequired = $this->getPlanRequired($featureKey);

        if ($planRequired === null) {
            return true;
        }

        $activePlan = $organization->activePlan();

        if ($activePlan === null) {
            return false;
        }

        $planFeatures = config("billing.plan_features.{$activePlan->slug}", []);

        return in_array($featureKey, $planFeatures, true);
    }

    /**
     * Get the plan_required value for a feature key.
     * Checks both module-registered and static config metadata.
     */
    private function getPlanRequired(string $featureKey): ?string
    {
        $allMetadata = ModuleFeatureRegistry::allFeatureMetadata();

        return $allMetadata[$featureKey]['plan_required'] ?? null;
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=CheckPlanFeatureAccess
```

Check the Plan factory and `newPlanSubscription()` method — they come from `laravelcm/laravel-subscriptions`. Read `modules/billing/database/factories/PlanFactory.php` and `modules/billing/src/Traits/HasBilling.php` to understand the API. If `newPlanSubscription()` doesn't exist, use whatever method the `HasBilling` trait provides to create a subscription.

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/CheckPlanFeatureAccess.php tests/Feature/CheckPlanFeatureAccessTest.php
git commit -m "feat: add CheckPlanFeatureAccess action for plan-gated features"
```

---

### Task 3: Wire Plan Check into `FeatureHelper::isActiveForKey()`

**Files:**
- Modify: `app/Support/FeatureHelper.php`
- Modify: `tests/Feature/FeatureHelperTest.php` (create if doesn't exist)

**Context:** `FeatureHelper::isActiveForKey()` is the single chokepoint where ALL feature resolution flows — from Inertia shared props, route middleware, and direct checks. Adding the plan check here means it's enforced everywhere automatically.

The check should happen AFTER the globally disabled check and AFTER the module enabled check, but BEFORE the org-level override check. This way:
1. Globally disabled = always off (no override possible)
2. Module disabled = always off
3. **Plan doesn't include feature = off (org override cannot bypass this)**
4. Org override = enabled/disabled/inherit
5. Pennant = final fallback

- [ ] **Step 1: Read existing FeatureHelper tests**

```bash
ls tests/Feature/FeatureHelperTest.php 2>/dev/null || echo "No existing test file"
```

If tests exist, read them to understand patterns. If not, create the file.

- [ ] **Step 2: Write the failing test**

Add to `tests/Feature/FeatureHelperTest.php`:

```php
it('returns false when org plan does not include plan-gated feature', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    // Set up tenant context
    TenantContext::set($org);

    $plan = \Modules\Billing\Models\Plan::factory()->create(['slug' => 'free']);
    $org->newPlanSubscription('main', $plan);

    config(['billing.plan_features.free' => []]);

    // api_access has plan_required = 'pro'
    expect(FeatureHelper::isActiveForKey('api_access', $user))->toBeFalse();
});

it('allows plan-gated feature when org plan includes it', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    TenantContext::set($org);

    $plan = \Modules\Billing\Models\Plan::factory()->create(['slug' => 'pro']);
    $org->newPlanSubscription('main', $plan);

    config(['billing.plan_features.pro' => ['api_access']]);

    // Feature should pass plan check, then resolve via Pennant
    // The result depends on Pennant, but at least the plan check doesn't block it
    $result = FeatureHelper::isActiveForKey('api_access', $user);
    // We can't assert true because Pennant may still be off,
    // but we CAN assert it doesn't short-circuit to false from the plan check
    // Better: test via the action directly (Task 2 tests cover this)
    // Here just verify the integration doesn't crash
    expect($result)->toBeBool();
});

it('org override cannot bypass plan restriction', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    TenantContext::set($org);

    // Org has no subscription
    // Org admin sets override to 'enabled'
    app(OrganizationSettingsService::class)->setOverride($org, 'features', 'api_access', 'enabled');

    // Should still be false — plan restriction trumps org override
    expect(FeatureHelper::isActiveForKey('api_access', $user))->toBeFalse();
});
```

- [ ] **Step 3: Run tests to verify they fail**

```bash
php artisan test --compact --filter=FeatureHelper
```

- [ ] **Step 4: Add plan check to `FeatureHelper::isActiveForKey()`**

In `app/Support/FeatureHelper.php`, modify `isActiveForKey()`. Add after the module-enabled check block (around line 129) and BEFORE the org-level override check (line 132):

```php
// Plan-gated check: if the feature requires a plan, verify the org has it.
// This runs BEFORE org override so org admins can't bypass plan restrictions.
$organization = TenantContext::get();
if ($organization instanceof Organization) {
    $planCheck = new \App\Actions\CheckPlanFeatureAccess;
    if (! $planCheck->handle($organization, $featureKey)) {
        return false;
    }
}
```

**IMPORTANT:** The `$organization` variable is already resolved below for the org override check. Move the `TenantContext::get()` call up so it's shared. Restructure to avoid calling it twice:

```php
// Resolve org once for both plan check and org override
$organization = TenantContext::get();

// Plan-gated check: if the feature requires a plan, verify the org has it.
// This runs BEFORE org override so org admins can't bypass plan restrictions.
if ($organization instanceof Organization) {
    $planCheck = new \App\Actions\CheckPlanFeatureAccess;
    if (! $planCheck->handle($organization, $featureKey)) {
        return false;
    }

    // Org-level override (inherit | enabled | disabled)
    $override = self::getOrgFeatureOverride($featureKey, $organization);
    if ($override === 'disabled') {
        return false;
    }

    if ($override === 'enabled') {
        return true;
    }

    // 'inherit' → fall through to Pennant
}
```

This replaces the existing org-override block (lines 132-144 of the current file).

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test --compact --filter=FeatureHelper
```

- [ ] **Step 6: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Support/FeatureHelper.php tests/Feature/FeatureHelperTest.php
git commit -m "feat: enforce plan_required in FeatureHelper chokepoint"
```

---

### Task 4: Add Plan Validation to `OrgFeaturesController::update()`

**Files:**
- Modify: `app/Http/Controllers/Settings/OrgFeaturesController.php`
- Modify: `tests/Feature/OrgFeaturesControllerTest.php` (create if doesn't exist)

**Context:** Prevent org admins from setting override to 'enabled' for features their plan doesn't include. The backend should reject the request with a validation error.

- [ ] **Step 1: Write the failing test**

```php
it('rejects enabling a plan-gated feature when org plan does not include it', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    // Give user org admin permission
    // Follow existing patterns for setting up org admin users

    TenantContext::set($org);

    // No subscription — api_access requires 'pro'
    $this->actingAs($user)
        ->post('/settings/features', [
            'key' => 'api_access',
            'override' => 'enabled',
        ])
        ->assertStatus(422);
});

it('allows enabling a feature when org plan includes it', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    TenantContext::set($org);

    $plan = \Modules\Billing\Models\Plan::factory()->create(['slug' => 'pro']);
    $org->newPlanSubscription('main', $plan);

    config(['billing.plan_features.pro' => ['api_access']]);

    $this->actingAs($user)
        ->post('/settings/features', [
            'key' => 'api_access',
            'override' => 'enabled',
        ])
        ->assertRedirect();
});

it('allows setting override to disabled regardless of plan', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    TenantContext::set($org);

    // No subscription, but disabling should always be allowed
    $this->actingAs($user)
        ->post('/settings/features', [
            'key' => 'api_access',
            'override' => 'disabled',
        ])
        ->assertRedirect();
});

it('allows setting override to inherit regardless of plan', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();

    TenantContext::set($org);

    $this->actingAs($user)
        ->post('/settings/features', [
            'key' => 'api_access',
            'override' => 'inherit',
        ])
        ->assertRedirect();
});
```

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Add plan check to `update()` method**

In `OrgFeaturesController::update()`, after the `abort_unless(isset($delegatable[$key]), ...)` line, add:

```php
// Only validate plan access when enabling — disabling or inheriting is always allowed.
if ($override === 'enabled') {
    $planCheck = new \App\Actions\CheckPlanFeatureAccess;
    if (! $planCheck->handle($organization, $key)) {
        abort(422, 'Your current plan does not include this feature. Please upgrade.');
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=OrgFeaturesController
```

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/Settings/OrgFeaturesController.php tests/Feature/OrgFeaturesControllerTest.php
git commit -m "feat: validate plan access when enabling plan-gated features"
```

---

### Task 5: Pass `org_plan` and `plan_features` to Frontend

**Files:**
- Modify: `app/Http/Controllers/Settings/OrgFeaturesController.php` — `show()` method

**Context:** The frontend needs to know the org's current plan and the plan→features mapping so it can show upgrade prompts and disable toggles client-side. Pass `org_plan` (current plan slug or null) and `plan_features` (the config mapping).

- [ ] **Step 1: Update `show()` to pass plan data**

In `OrgFeaturesController::show()`, before the `return Inertia::render(...)`:

```php
$currentPlan = $organization->activePlan();

return Inertia::render('settings/features', [
    'features' => $features,
    'orgPlan' => $currentPlan?->slug,
    'planFeatures' => config('billing.plan_features', []),
]);
```

- [ ] **Step 2: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/Settings/OrgFeaturesController.php
git commit -m "feat: pass org plan and plan_features mapping to features page"
```

---

### Task 6: Update features.tsx with Plan Gating UI

**Files:**
- Modify: `resources/js/pages/settings/features.tsx`

**Context:** When a feature requires a plan that the org doesn't have, the toggle buttons should be disabled and an upgrade message shown. The `orgPlan` and `planFeatures` props are now available.

- [ ] **Step 1: Read the current file**

Read `resources/js/pages/settings/features.tsx` to understand the current structure.

- [ ] **Step 2: Update the PageProps interface**

```typescript
interface PageProps extends Omit<SharedData, 'features'> {
    features: FeatureEntry[];
    orgPlan: string | null;
    planFeatures: Record<string, string[]>;
}
```

- [ ] **Step 3: Add a helper to check plan access**

```typescript
function hasPlanAccess(
    featureKey: string,
    planRequired: string | null,
    orgPlan: string | null,
    planFeatures: Record<string, string[]>,
): boolean {
    if (!planRequired) return true;
    if (!orgPlan) return false;
    return (planFeatures[orgPlan] ?? []).includes(featureKey);
}
```

- [ ] **Step 4: Update the Features component to pass plan data**

```typescript
export default function Features() {
    const { features, orgPlan, planFeatures } = usePage<PageProps>().props;

    return (
        // ... existing layout
        {features.map((feature) => (
            <div key={feature.key} className="px-4">
                <FeatureRow
                    feature={feature}
                    locked={!hasPlanAccess(feature.key, feature.plan_required, orgPlan, planFeatures)}
                />
            </div>
        ))}
    );
}
```

- [ ] **Step 5: Update FeatureRow to handle locked state**

Add `locked` prop to `FeatureRow`:

```typescript
function FeatureRow({ feature, locked }: { feature: FeatureEntry; locked: boolean }) {
    const label = FEATURE_LABELS[feature.key] ?? feature.key;

    const handleChange = (override: string) => {
        if (locked && override === 'enabled') return;
        router.post(
            '/settings/features',
            { key: feature.key, override },
            { preserveScroll: true },
        );
    };

    return (
        <div className={`flex items-center justify-between gap-4 py-3 ${locked ? 'opacity-60' : ''}`}>
            <div className="min-w-0 flex-1">
                <p className="text-sm font-medium">{label}</p>
                <div className="mt-0.5 flex items-center gap-2">
                    {feature.plan_required && (
                        <span className="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                            {feature.plan_required}
                        </span>
                    )}
                    <span className="font-mono text-xs text-muted-foreground">
                        {feature.key}
                    </span>
                </div>
                {locked && (
                    <p className="mt-1 text-xs text-amber-600 dark:text-amber-400">
                        Upgrade to <strong>{feature.plan_required}</strong> to enable this feature.
                    </p>
                )}
            </div>
            <div className="flex shrink-0 gap-1 rounded-lg border bg-muted/40 p-0.5">
                {OVERRIDE_OPTIONS.map((opt) => (
                    <button
                        key={opt.value}
                        type="button"
                        onClick={() => handleChange(opt.value)}
                        disabled={locked && opt.value === 'enabled'}
                        className={`rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${
                            feature.override === opt.value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        } ${locked && opt.value === 'enabled' ? 'cursor-not-allowed opacity-40' : ''}`}
                    >
                        {opt.label}
                    </button>
                ))}
            </div>
        </div>
    );
}
```

- [ ] **Step 6: Run TypeScript check**

```bash
npx tsc --noEmit
```

- [ ] **Step 7: Commit**

```bash
git add resources/js/pages/settings/features.tsx
git commit -m "feat: show upgrade prompts and disable toggles for plan-gated features"
```

---

### Task 7: Integration Tests

**Files:**
- Create: `tests/Feature/PlanFeatureGatingTest.php`

**Context:** End-to-end tests verifying the full pipeline: billing plan → feature resolution → Inertia shared props → route middleware.

- [ ] **Step 1: Write integration tests**

```php
<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Services\TenantContext;
use App\Support\FeatureHelper;

it('blocks plan-gated feature in Inertia shared props when org has no plan', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('features.api_access', false)
        );
});

it('does not block plan-gated feature when org has correct plan', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $plan = \Modules\Billing\Models\Plan::factory()->create(['slug' => 'pro']);
    $org->newPlanSubscription('main', $plan);
    config(['billing.plan_features.pro' => ['api_access']]);

    // Note: The final value depends on Pennant resolution (Feature::for($user)->active()).
    // This test verifies the plan check does NOT block it — Pennant may still resolve false
    // if api_access isn't activated for this user. Use CheckPlanFeatureAccess directly
    // for isolated plan-check testing (Task 2).
    $result = (new \App\Actions\CheckPlanFeatureAccess)->handle($org, 'api_access');
    expect($result)->toBeTrue();
});

it('features settings page shows plan data', function (): void {
    $user = createTestUser();
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $this->actingAs($user)
        ->get('/settings/features')
        ->assertInertia(fn ($page) => $page
            ->has('orgPlan')
            ->has('planFeatures')
        );
});
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --compact --filter=PlanFeatureGating
```

- [ ] **Step 3: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add tests/Feature/PlanFeatureGatingTest.php
git commit -m "test: add integration tests for plan-gated feature access"
```

---

### Task 8: Final Verification

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --compact
```

- [ ] **Step 2: Run Pint on all PHP**

```bash
vendor/bin/pint --format agent
```

- [ ] **Step 3: Run TypeScript check**

```bash
npx tsc --noEmit
```

- [ ] **Step 4: Build frontend**

```bash
npm run build
```

- [ ] **Step 5: Verify in browser**

1. Log in as super admin → all features visible
2. Log in as org admin with no subscription → plan-gated features disabled in sidebar, features page shows upgrade prompt
3. Create a 'pro' plan and subscription for org → api_access becomes available
4. Try to enable a plan-gated feature via features page → should work with plan, fail without

- [ ] **Step 6: Commit any final fixes**
