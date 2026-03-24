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

    private function getPlanRequired(string $featureKey): ?string
    {
        $allMetadata = ModuleFeatureRegistry::allFeatureMetadata();

        return $allMetadata[$featureKey]['plan_required'] ?? null;
    }
}
