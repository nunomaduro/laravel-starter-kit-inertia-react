<?php

declare(strict_types=1);

namespace Modules\Billing\Services\PaymentGateway\Contracts;

use App\Models\Organization;

interface PaymentGatewayInterface
{
    public function createCustomer(Organization $organization): string;

    public function createCheckoutSession(Organization $organization, array $lineItems, string $successUrl, string $cancelUrl): string;

    public function createSubscriptionCheckout(Organization $organization, string $planId, string $successUrl, string $cancelUrl): string;

    public function cancelSubscription(string $subscriptionId): void;

    public function resumeSubscription(string $subscriptionId): void;

    public function changeSubscriptionPlan(string $subscriptionId, string $newPlanId): void;

    public function updateSubscriptionQuantity(string $subscriptionId, int $quantity): bool;

    public function processRefund(string $paymentIntentId, int $amountCents): void;

    public function handleWebhook(string $payload, string $signature): array;

    public function validateWebhook(string $payload, string $signature): bool;
}
