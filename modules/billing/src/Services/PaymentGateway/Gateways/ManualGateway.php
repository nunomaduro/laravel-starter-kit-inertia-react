<?php

declare(strict_types=1);

namespace Modules\Billing\Services\PaymentGateway\Gateways;

use App\Models\Organization;
use Illuminate\Support\Str;
use Modules\Billing\Services\PaymentGateway\Contracts\PaymentGatewayInterface;

final class ManualGateway implements PaymentGatewayInterface
{
    public function createCustomer(Organization $organization): string
    {
        return 'manual_'.$organization->id;
    }

    public function createCheckoutSession(Organization $organization, array $lineItems, string $successUrl, string $cancelUrl): string
    {
        return 'manual_session_'.Str::ulid();
    }

    public function createSubscriptionCheckout(Organization $organization, string $planId, string $successUrl, string $cancelUrl): string
    {
        return 'manual_sub_'.Str::ulid();
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        // No-op for manual
    }

    public function resumeSubscription(string $subscriptionId): void
    {
        // No-op for manual
    }

    public function changeSubscriptionPlan(string $subscriptionId, string $newPlanId): void
    {
        // No-op for manual
    }

    public function updateSubscriptionQuantity(string $subscriptionId, int $quantity): bool
    {
        return true;
    }

    public function processRefund(string $paymentIntentId, int $amountCents): void
    {
        // No-op for manual
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        return [];
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        return true;
    }
}
