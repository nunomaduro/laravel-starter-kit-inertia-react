<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravelcm\Subscriptions\Models\Plan;
use Modules\Billing\Models\FailedPaymentAttempt;
use Modules\Billing\Models\GatewayProduct;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\WebhookLog;
use Modules\Billing\Services\PaymentGateway\Gateways\PaddleGateway;
use Throwable;

/**
 * Handles incoming Paddle webhooks.
 *
 * Events handled:
 * - subscription.created, subscription.updated, subscription.canceled
 * - transaction.completed, transaction.payment_failed
 * - customer.created
 */
final readonly class PaddleWebhookController
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Paddle-Signature', '');

        $webhookLog = WebhookLog::query()->create([
            'gateway' => 'paddle',
            'event_type' => 'raw',
            'payload' => json_decode($payload, true),
            'processed' => false,
        ]);

        $gateway = resolve(PaddleGateway::class);
        if (! $gateway->validateWebhook($payload, $signature)) {
            return response('Invalid signature', 400);
        }

        $result = $gateway->handleWebhook($payload, $signature);
        $eventType = $result['event'] ?? '';
        $data = $result['data'] ?? [];

        DB::transaction(function () use ($eventType, $data, $webhookLog): void {
            $organizationId = null;

            try {
                $organizationId = match ($eventType) {
                    'subscription.created', 'subscription.updated', 'subscription.canceled',
                    'subscription_created', 'subscription_updated', 'subscription_cancelled' => $this->handleSubscriptionEvent($eventType, $data),
                    'transaction.completed', 'subscription_payment_succeeded' => $this->handlePaymentSucceeded($data),
                    'transaction.payment_failed', 'subscription_payment_failed' => $this->handlePaymentFailed($data),
                    'customer.created', 'customer_created' => null,
                    default => null,
                };

                if ($organizationId) {
                    $webhookLog->update([
                        'organization_id' => $organizationId,
                        'event_type' => $eventType,
                        'processed' => true,
                    ]);
                } else {
                    $webhookLog->update(['event_type' => $eventType]);
                }
            } catch (Throwable $throwable) {
                Log::error('Paddle webhook processing failed', [
                    'event_type' => $eventType,
                    'error' => $throwable->getMessage(),
                ]);
                throw $throwable;
            }
        });

        return response('OK', 200);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handleSubscriptionEvent(string $eventType, array $data): ?int
    {
        if (isset($data['id']) && isset($data['customer_id'])) {
            $object = $data;
        } elseif ($data['subscription_id'] ?? null) {
            $object = ['id' => $data['subscription_id'], 'customer_id' => $data['customer_id'] ?? null];
        } else {
            $object = $data;
        }

        $subscriptionId = $object['id'] ?? $object['subscription_id'] ?? '';
        $customerId = $object['customer_id'] ?? $object['user_id'] ?? '';

        $organization = Organization::query()->where('paddle_customer_id', $customerId)->first();
        if (! $organization instanceof Organization) {
            Log::warning('Organization not found for Paddle subscription', ['customer_id' => $customerId]);

            return null;
        }

        $subscription = Subscription::query()
            ->where('subscriber_type', Organization::class)
            ->where('subscriber_id', $organization->id)
            ->where(function ($q) use ($subscriptionId): void {
                $q->where('gateway_subscription_id', $subscriptionId)
                    ->orWhereNull('gateway_subscription_id');
            })
            ->orderByDesc('id')
            ->first();

        $norm = match (true) {
            str_contains($eventType, 'cancel') => 'canceled',
            str_contains($eventType, 'update') => 'updated',
            default => 'created',
        };

        if ($norm === 'canceled' && $subscription instanceof Subscription) {
            $effectiveAt = $object['scheduled_change']['effective_at'] ?? $object['cancellation_effective_date'] ?? null;
            $subscription->update([
                'canceled_at' => $subscription->canceled_at ?? now(),
                'ends_at' => $effectiveAt ? now()->parse($effectiveAt) : now(),
                'gateway_subscription_id' => $subscriptionId,
            ]);
        }

        if ($norm === 'updated' && $subscription instanceof Subscription) {
            $status = $object['status'] ?? '';
            $quantity = (int) ($object['items'][0]['quantity'] ?? $object['quantity'] ?? 1);
            $subscription->update([
                'gateway_subscription_id' => $subscriptionId,
                'quantity' => max(1, $quantity),
                'canceled_at' => $status === 'canceled' ? ($subscription->canceled_at ?? now()) : null,
            ]);
        }

        if ($norm === 'created') {
            if ($subscription instanceof Subscription) {
                $subscription->update(['gateway_subscription_id' => $subscriptionId]);
            } else {
                $this->createSubscriptionFromPaddle($organization, $object, $subscriptionId);
            }
        }

        return $organization->id;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handlePaymentSucceeded(array $data): ?int
    {
        $object = $data;
        $transactionId = $object['id'] ?? $object['order_id'] ?? '';
        $customerId = $object['customer_id'] ?? $object['user_id'] ?? '';
        $details = $object['details'] ?? [];
        $totals = $details['totals'] ?? [];
        $amount = (float) ($totals['total'] ?? $object['sale_gross'] ?? 0);
        $currency = mb_strtoupper((string) ($object['currency_code'] ?? $object['currency'] ?? 'USD'));

        $organization = Organization::query()->where('paddle_customer_id', $customerId)->first();
        if (! $organization instanceof Organization) {
            Log::warning('Organization not found for Paddle payment', ['customer_id' => $customerId]);

            return null;
        }

        Invoice::query()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                ['gateway_invoice_id' => $transactionId],
                [
                    'organization_id' => $organization->id,
                    'billable_type' => Organization::class,
                    'billable_id' => $organization->id,
                    'number' => 'PDL-'.mb_substr((string) $transactionId, 0, 20),
                    'status' => 'paid',
                    'subtotal' => (int) round($amount * 100),
                    'tax' => 0,
                    'total' => (int) round($amount * 100),
                    'currency' => $currency,
                    'paid_at' => now(),
                ]
            );

        return $organization->id;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handlePaymentFailed(array $data): ?int
    {
        $customerId = $data['customer_id'] ?? $data['user_id'] ?? '';
        $subscriptionId = $data['subscription_id'] ?? null;
        Log::warning('Paddle payment failed', [
            'subscription_id' => $subscriptionId,
            'customer_id' => $customerId,
        ]);

        $organization = Organization::query()->where('paddle_customer_id', $customerId)->first();
        if (! $organization instanceof Organization) {
            return null;
        }

        $existing = FailedPaymentAttempt::query()
            ->where('organization_id', $organization->id)
            ->where('gateway', 'paddle')
            ->where('gateway_subscription_id', (string) $subscriptionId)
            ->first();

        if ($existing instanceof FailedPaymentAttempt) {
            $existing->increment('attempt_number');
            $existing->update(['failed_at' => now()]);
        } else {
            FailedPaymentAttempt::query()->create([
                'organization_id' => $organization->id,
                'gateway' => 'paddle',
                'gateway_subscription_id' => $subscriptionId ? (string) $subscriptionId : null,
                'attempt_number' => 1,
                'failed_at' => now(),
            ]);
        }

        return $organization->id;
    }

    /**
     * Create a local Subscription when Paddle creates one and we have no record yet.
     *
     * @param  array<string, mixed>  $paddleSubscription
     */
    private function createSubscriptionFromPaddle(Organization $organization, array $paddleSubscription, string $gatewaySubscriptionId): void
    {
        $items = $paddleSubscription['items'] ?? [];
        $priceId = $items[0]['price']['id'] ?? $items[0]['price_id'] ?? null;
        if (! $priceId) {
            return;
        }

        $gateway = PaymentGateway::query()->where('type', 'paddle')->where('is_active', true)->first();
        if (! $gateway instanceof PaymentGateway) {
            return;
        }

        $gp = GatewayProduct::query()
            ->where('payment_gateway_id', $gateway->id)
            ->where('gateway_price_id', $priceId)
            ->first();
        if (! $gp instanceof GatewayProduct) {
            return;
        }

        $plan = Plan::query()->find($gp->plan_id);
        if (! $plan instanceof Plan) {
            return;
        }

        $quantity = (int) ($items[0]['quantity'] ?? 1);

        Subscription::query()->create([
            'subscriber_type' => Organization::class,
            'subscriber_id' => $organization->id,
            'plan_id' => $plan->id,
            'slug' => 'paddle-'.$gatewaySubscriptionId,
            'name' => $plan->name,
            'description' => $plan->description,
            'gateway_subscription_id' => $gatewaySubscriptionId,
            'quantity' => max(1, $quantity),
            'starts_at' => now(),
        ]);
    }
}
