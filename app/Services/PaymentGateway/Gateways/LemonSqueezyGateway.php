<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway\Gateways;

use App\Models\Billing\PaymentGateway as PaymentGatewayModel;
use App\Models\Organization;
use App\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use LemonSqueezy\Laravel\Checkout;
use Throwable;

/**
 * Lemon Squeezy payment gateway for one-time products (generic).
 * Supports custom-priced checkouts via a single product variant; subscriptions are not supported.
 */
final class LemonSqueezyGateway implements PaymentGatewayInterface
{
    private ?PaymentGatewayModel $gatewayModel = null;

    public function setGatewayModel(PaymentGatewayModel $model): self
    {
        $this->gatewayModel = $model;

        return $this;
    }

    public function createCustomer(Organization $organization): string
    {
        return 'ls_'.$organization->id;
    }

    public function createCheckoutSession(Organization $organization, array $lineItems, string $successUrl, string $cancelUrl): string
    {
        $variantId = (string) $this->config('generic_variant_id', '');
        throw_if($variantId === '', InvalidArgumentException::class, 'Lemon Squeezy gateway requires a generic one-time product variant ID (generic_variant_id in settings).');

        $first = $lineItems[0] ?? null;
        $amount = isset($first['amount']) ? (int) $first['amount'] : 0;
        $name = $first['name'] ?? 'One-time purchase';

        $customData = [
            'billable_type' => Organization::class,
            'billable_id' => (string) $organization->id,
        ];
        if (isset($first['credits']) && $first['credits'] > 0) {
            $customData['credits'] = (int) $first['credits'];
        }

        if (isset($first['credit_pack_id'])) {
            $customData['credit_pack_id'] = (int) $first['credit_pack_id'];
        }

        $this->applyLemonSqueezyConfig();

        $checkout = Checkout::make($this->config('store_id'), $variantId)
            ->withCustomData($customData)
            ->withProductName($name)
            ->redirectTo($successUrl ?: $this->getSuccessUrl());

        if ($amount > 0) {
            $checkout->withCustomPrice($amount);
        }

        return $checkout->url();
    }

    public function createSubscriptionCheckout(Organization $organization, string $planId, string $successUrl, string $cancelUrl): string
    {
        throw new InvalidArgumentException('Lemon Squeezy gateway supports one-time products only; use Stripe or Paddle for subscriptions.');
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        // No-op: Lemon Squeezy one-time only
    }

    public function resumeSubscription(string $subscriptionId): void
    {
        // No-op: Lemon Squeezy one-time only
    }

    public function changeSubscriptionPlan(string $subscriptionId, string $newPlanId): void
    {
        // No-op: Lemon Squeezy one-time only
    }

    public function updateSubscriptionQuantity(string $subscriptionId, int $quantity): bool
    {
        return true;
    }

    public function processRefund(string $paymentIntentId, int $amountCents): void
    {
        // Refunds via Lemon Squeezy dashboard
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        $data = json_decode($payload, true);

        return [
            'event' => $data['meta']['event_name'] ?? 'unknown',
            'data' => $data ?? [],
        ];
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        $secret = $this->config('signing_secret');
        if ($secret === null || $secret === '') {
            return true;
        }

        if ($signature === '') {
            return false;
        }

        try {
            $computed = hash_hmac('sha256', $payload, (string) $secret);

            return hash_equals($computed, $signature);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Create a one-time checkout (convenience method matching boilerplate).
     *
     * @param  array<int, array{name?: string, amount?: int, quantity?: int}>  $lineItems
     * @param  array{success_url?: string, cancel_url?: string, metadata?: array}  $options
     * @return array{session_id: string, url: string, metadata: array}
     */
    public function createOneTimeCheckout(Organization $organization, array $lineItems, array $options = []): array
    {
        $url = $this->createCheckoutSession(
            $organization,
            $lineItems,
            $options['success_url'] ?? $this->getSuccessUrl(),
            $options['cancel_url'] ?? $this->getCancelUrl()
        );

        $first = $lineItems[0] ?? [];
        $amount = (int) ($first['amount'] ?? 0);

        return [
            'session_id' => 'ls_'.mb_substr(hash('sha256', $url), 0, 32),
            'url' => $url,
            'metadata' => [
                'billable_type' => Organization::class,
                'billable_id' => $organization->id,
                'amount' => $amount,
            ],
        ];
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if ($this->gatewayModel instanceof PaymentGatewayModel) {
            $settings = $this->gatewayModel->getDecryptedSettings();
            if (is_array($settings) && array_key_exists($key, $settings)) {
                return $settings[$key] ?? $default;
            }
        }

        return config('services.lemon_squeezy.'.$key, $default);
    }

    private function applyLemonSqueezyConfig(): void
    {
        Config::set('lemon-squeezy.api_key', $this->config('api_key'));
        Config::set('lemon-squeezy.store', $this->config('store_id'));
    }

    private function getSuccessUrl(): string
    {
        return route('billing.credits.index');
    }

    private function getCancelUrl(): string
    {
        return route('billing.credits.index');
    }
}
