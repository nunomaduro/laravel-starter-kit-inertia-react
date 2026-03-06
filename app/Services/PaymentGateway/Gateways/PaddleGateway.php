<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway\Gateways;

use App\Models\Organization;
use App\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

/**
 * Paddle Billing payment gateway implementation.
 * Uses Paddle Billing API; Paddle Classic is deprecated.
 */
final class PaddleGateway implements PaymentGatewayInterface
{
    private ?string $baseUrl = null;

    public function createCustomer(Organization $organization): string
    {
        $email = $organization->billing_email ?? $organization->owner?->email ?? '';
        $name = $organization->name ?? '';

        if ($organization->paddle_customer_id) {
            $this->request('patch', '/customers/'.$organization->paddle_customer_id, [
                'email' => $email,
                'name' => $name,
            ]);

            return $organization->paddle_customer_id;
        }

        $response = $this->request('post', '/customers', [
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

        $response = $this->request('post', '/transactions', [
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
        $this->request('post', sprintf('/subscriptions/%s/cancel', $subscriptionId), [
            'effective_from' => 'next_billing_period',
        ]);
    }

    public function resumeSubscription(string $subscriptionId): void
    {
        $this->request('post', sprintf('/subscriptions/%s/resume', $subscriptionId), [
            'effective_from' => 'immediately',
        ]);
    }

    public function changeSubscriptionPlan(string $subscriptionId, string $newPlanId): void
    {
        $sub = $this->request('get', '/subscriptions/'.$subscriptionId);
        $currentItem = $sub['data']['items'][0] ?? null;
        if (! $currentItem) {
            return;
        }

        $this->request('patch', '/subscriptions/'.$subscriptionId, [
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
        $sub = $this->request('get', '/subscriptions/'.$subscriptionId);
        $item = $sub['data']['items'][0] ?? null;
        if (! $item) {
            return false;
        }

        $this->request('patch', '/subscriptions/'.$subscriptionId, [
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
        $this->request('post', sprintf('/transactions/%s/refund', $transactionId), [
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

    private function getBaseUrl(): string
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = config('paddle.sandbox', true)
                ? 'https://sandbox-api.paddle.com'
                : 'https://api.paddle.com';
        }

        return $this->baseUrl;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->getBaseUrl().$endpoint;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('paddle.vendor_auth_code'),
            'Content-Type' => 'application/json',
        ])->{$method}($url, $data);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Paddle API error: '.$response->body(),
                $response->status()
            );
        }

        return $response->json();
    }
}
