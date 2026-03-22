<?php

declare(strict_types=1);

namespace Modules\Billing\Services\PaymentGateway\Gateways;

use App\Models\Organization;
use Modules\Billing\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use Stripe\StripeClient;
use Throwable;

final readonly class StripeGateway implements PaymentGatewayInterface
{
    private StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('stripe.secret'));
    }

    public function createCustomer(Organization $organization): string
    {
        $customer = $this->client->customers->create([
            'email' => $organization->billing_email ?? $organization->owner?->email,
            'name' => $organization->name,
            'metadata' => ['organization_id' => (string) $organization->id],
        ]);

        return $customer->id;
    }

    public function createCheckoutSession(Organization $organization, array $lineItems, string $successUrl, string $cancelUrl): string
    {
        $customerId = $organization->stripe_customer_id ?? $this->createCustomer($organization);
        if (! $organization->stripe_customer_id) {
            $organization->update(['stripe_customer_id' => $customerId]);
        }

        $session = $this->client->checkout->sessions->create([
            'customer' => $customerId,
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->id;
    }

    public function createSubscriptionCheckout(Organization $organization, string $planId, string $successUrl, string $cancelUrl): string
    {
        $customerId = $organization->stripe_customer_id ?? $this->createCustomer($organization);
        if (! $organization->stripe_customer_id) {
            $organization->update(['stripe_customer_id' => $customerId]);
        }

        $session = $this->client->checkout->sessions->create([
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items' => [['price' => $planId, 'quantity' => 1]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->id;
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        $this->client->subscriptions->cancel($subscriptionId);
    }

    public function resumeSubscription(string $subscriptionId): void
    {
        $this->client->subscriptions->update($subscriptionId, ['cancel_at_period_end' => false]);
    }

    public function changeSubscriptionPlan(string $subscriptionId, string $newPlanId): void
    {
        $sub = $this->client->subscriptions->retrieve($subscriptionId);

        $this->client->subscriptions->update($subscriptionId, [
            'items' => [
                [
                    'id' => $sub->items->data[0]->id,
                    'price' => $newPlanId,
                ],
            ],
            'proration_behavior' => 'create_prorations',
        ]);
    }

    public function updateSubscriptionQuantity(string $subscriptionId, int $quantity): bool
    {
        $sub = $this->client->subscriptions->retrieve($subscriptionId);
        $itemId = $sub->items->data[0]->id ?? null;

        if (! $itemId) {
            return false;
        }

        $this->client->subscriptions->update($subscriptionId, [
            'items' => [
                ['id' => $itemId, 'quantity' => max(1, $quantity)],
            ],
            'proration_behavior' => 'create_prorations',
        ]);

        return true;
    }

    public function processRefund(string $paymentIntentId, int $amountCents): void
    {
        $this->client->refunds->create([
            'payment_intent' => $paymentIntentId,
            'amount' => $amountCents,
        ]);
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            config('stripe.webhook_secret')
        );

        return ['event' => $event->type, 'data' => $event->data->toArray()];
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        try {
            \Stripe\Webhook::constructEvent($payload, $signature, config('stripe.webhook_secret'));

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
