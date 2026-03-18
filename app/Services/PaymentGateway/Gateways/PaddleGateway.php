<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway\Gateways;

use App\Http\Integrations\Paddle\PaddleConnector;
use App\Http\Integrations\Paddle\Requests\PaddleApiRequest;
use App\Http\Integrations\Paddle\Requests\PaddleGetRequest;
use App\Models\Organization;
use App\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;
use RuntimeException;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\RequestException;

/**
 * Paddle Billing payment gateway implementation.
 * Uses Paddle Billing API via Saloon connector; Paddle Classic is deprecated.
 */
final readonly class PaddleGateway implements PaymentGatewayInterface
{
    public function __construct(
        private PaddleConnector $connector
    ) {}

    public function createCustomer(Organization $organization): string
    {
        $email = $organization->billing_email ?? $organization->owner?->email ?? '';
        $name = $organization->name ?? '';

        if ($organization->paddle_customer_id) {
            $this->send(Method::PATCH, '/customers/'.$organization->paddle_customer_id, [
                'email' => $email,
                'name' => $name,
            ]);

            return $organization->paddle_customer_id;
        }

        $response = $this->send(Method::POST, '/customers', [
            'email' => $email,
            'name' => $name,
        ]);

        $customerId = $response['data']['id'] ?? '';
        if ($customerId) {
            $organization->update(['paddle_customer_id' => $customerId]);
        }

        return $customerId;
    }

    public function createCheckoutSession(Organization $organization, array $lineItems, string $successUrl, string $cancelUrl): string
    {
        $customerId = $organization->paddle_customer_id ?? $this->createCustomer($organization);
        throw_unless($customerId, InvalidArgumentException::class, 'Could not create or retrieve Paddle customer.');

        $items = [];
        foreach ($lineItems as $item) {
            $priceId = $item['price_id'] ?? null;
            if (! $priceId) {
                continue;
            }

            $items[] = [
                'price_id' => $priceId,
                'quantity' => (int) ($item['quantity'] ?? 1),
            ];
        }

        throw_if($items === [], InvalidArgumentException::class, 'Paddle checkout requires at least one item with price_id.');

        $response = $this->send(Method::POST, '/transactions', [
            'items' => $items,
            'customer_id' => $customerId,
            'custom_data' => [
                'success_url' => $successUrl,
                'return_url' => $cancelUrl,
            ],
        ]);

        $checkoutUrl = $response['data']['checkout']['url'] ?? null;
        throw_unless($checkoutUrl, RuntimeException::class, 'Paddle did not return a checkout URL.');

        return $checkoutUrl;
    }

    public function createSubscriptionCheckout(Organization $organization, string $planId, string $successUrl, string $cancelUrl): string
    {
        return $this->createCheckoutSession($organization, [
            ['price_id' => $planId, 'quantity' => 1],
        ], $successUrl, $cancelUrl);
    }

    public function cancelSubscription(string $subscriptionId): void
    {
        $this->send(Method::POST, sprintf('/subscriptions/%s/cancel', $subscriptionId), [
            'effective_from' => 'next_billing_period',
        ]);
    }

    public function resumeSubscription(string $subscriptionId): void
    {
        $this->send(Method::POST, sprintf('/subscriptions/%s/resume', $subscriptionId), [
            'effective_from' => 'immediately',
        ]);
    }

    public function changeSubscriptionPlan(string $subscriptionId, string $newPlanId): void
    {
        $sub = $this->sendGet('/subscriptions/'.$subscriptionId);
        $currentItem = $sub['data']['items'][0] ?? null;
        if (! $currentItem) {
            return;
        }

        $this->send(Method::PATCH, '/subscriptions/'.$subscriptionId, [
            'items' => [
                [
                    'item_id' => $currentItem['id'],
                    'price_id' => $newPlanId,
                    'quantity' => (int) ($currentItem['quantity'] ?? 1),
                ],
            ],
            'proration_billing_mode' => 'prorated_immediately',
        ]);
    }

    public function updateSubscriptionQuantity(string $subscriptionId, int $quantity): bool
    {
        $sub = $this->sendGet('/subscriptions/'.$subscriptionId);
        $item = $sub['data']['items'][0] ?? null;
        if (! $item) {
            return false;
        }

        $this->send(Method::PATCH, '/subscriptions/'.$subscriptionId, [
            'items' => [
                [
                    'item_id' => $item['id'],
                    'price_id' => $item['price']['id'],
                    'quantity' => max(1, $quantity),
                ],
            ],
            'proration_billing_mode' => 'prorated_immediately',
        ]);

        return true;
    }

    public function processRefund(string $transactionId, int $amountCents): void
    {
        $this->send(Method::POST, sprintf('/transactions/%s/refund', $transactionId), [
            'amount' => (string) ($amountCents / 100),
            'reason' => 'Customer request',
        ]);
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        if (! $this->validateWebhook($payload, $signature)) {
            return [];
        }

        $data = json_decode($payload, true);

        return [
            'event' => $data['event_type'] ?? $data['alert_name'] ?? 'unknown',
            'data' => $data['data'] ?? $data,
        ];
    }

    public function validateWebhook(string $payload, string $signature): bool
    {
        $secret = config('paddle.webhook_secret');
        if (! $secret || ! $signature) {
            return false;
        }

        $parts = [];
        foreach (explode(';', $signature) as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $ts = $parts['ts'] ?? '';
        $h1 = $parts['h1'] ?? '';
        if ($ts === '' || $h1 === '') {
            return false;
        }

        $signed = $ts.':'.$payload;
        $expected = hash_hmac('sha256', $signed, (string) $secret);

        return hash_equals($expected, $h1);
    }

    public function isConfigured(): bool
    {
        return ! empty(config('paddle.vendor_auth_code'));
    }

    /**
     * @return array<string, mixed>
     */
    private function sendGet(string $endpoint): array
    {
        try {
            $response = $this->connector->send(new PaddleGetRequest($endpoint));

            return $response->json();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Paddle API error: '.$e->getResponse()->body(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function send(Method $method, string $endpoint, array $data = []): array
    {
        try {
            $response = $this->connector->send(new PaddleApiRequest($method, $endpoint, $data));

            return $response->json();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'Paddle API error: '.$e->getResponse()->body(),
                $e->getCode(),
                $e
            );
        }
    }
}
