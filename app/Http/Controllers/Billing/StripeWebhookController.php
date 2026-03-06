<?php

declare(strict_types=1);

namespace App\Http\Controllers\Billing;

use App\Events\Billing\InvoicePaid;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Models\Billing\WebhookLog;
use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use App\Services\PaymentGateway\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Handles incoming Stripe webhooks.
 *
 * Webhook events handled:
 * - customer.subscription.created
 * - customer.subscription.updated
 * - customer.subscription.deleted
 * - invoice.paid
 * - invoice.payment_failed
 */
final readonly class StripeWebhookController
{
    public function __invoke(Request $request, PaymentGatewayManager $manager): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        $webhookLog = WebhookLog::query()->create([
            'gateway' => 'stripe',
            'event_type' => 'raw',
            'payload' => json_decode($payload, true),
            'processed' => false,
        ]);

        $gateway = $manager->driver('stripe');
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
                    'customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted' => $this->handleSubscriptionEvent($eventType, $data),
                    'invoice.paid', 'invoice.payment_failed' => $this->handleInvoiceEvent($eventType, $data),
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
                Log::error('Stripe webhook processing failed', [
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
        $object = $data['object'] ?? [];
        $subscriptionId = $object['id'] ?? '';
        $customerId = $object['customer'] ?? '';

        $organization = Organization::query()->where('stripe_customer_id', $customerId)->first();
        if (! $organization instanceof Organization) {
            Log::warning('Organization not found for Stripe customer', ['customer_id' => $customerId]);

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

        if ($eventType === 'customer.subscription.deleted' && $subscription instanceof Subscription) {
            $subscription->update([
                'canceled_at' => $subscription->canceled_at ?? now(),
                'ends_at' => now(),
                'gateway_subscription_id' => $subscriptionId,
            ]);

            return $organization->id;
        }

        if ($eventType === 'customer.subscription.updated' && $subscription instanceof Subscription) {
            $status = $object['status'] ?? '';
            $quantity = (int) ($object['items']['data'][0]['quantity'] ?? 1);

            $subscription->update([
                'gateway_subscription_id' => $subscriptionId,
                'quantity' => max(1, $quantity),
                'canceled_at' => $status === 'canceled' ? ($subscription->canceled_at ?? now()) : null,
            ]);

            return $organization->id;
        }

        if ($eventType === 'customer.subscription.created' && $subscription instanceof Subscription) {
            $subscription->update(['gateway_subscription_id' => $subscriptionId]);

            return $organization->id;
        }

        return $organization->id;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handleInvoiceEvent(string $eventType, array $data): ?int
    {
        $object = $data['object'] ?? [];
        $invoiceId = $object['id'] ?? '';
        $customerId = $object['customer'] ?? '';

        $organization = Organization::query()->where('stripe_customer_id', $customerId)->first();
        if (! $organization instanceof Organization) {
            Log::warning('Organization not found for Stripe invoice', ['customer_id' => $customerId]);

            return null;
        }

        if ($eventType === 'invoice.paid') {
            $amountPaid = (int) ($object['amount_paid'] ?? 0);

            $invoice = Invoice::query()->withoutGlobalScope(OrganizationScope::class)
                ->firstOrNew(['gateway_invoice_id' => $invoiceId]);
            $invoice->organization_id = $organization->id;
            $invoice->billable_type = Organization::class;
            $invoice->billable_id = $organization->id;
            $invoice->number = $object['number'] ?? 'INV-'.mb_substr((string) $invoiceId, 0, 20);
            $invoice->status = 'paid';
            $invoice->subtotal = (int) ($object['subtotal'] ?? $amountPaid);
            $invoice->tax = (int) ($object['tax'] ?? 0);
            $invoice->total = $amountPaid;
            $invoice->currency = mb_strtoupper($object['currency'] ?? 'usd');
            $invoice->paid_at = now();
            $invoice->save();
            $invoice->load('organization.owner');
            event(new InvoicePaid($invoice));
        }

        if ($eventType === 'invoice.payment_failed') {
            Invoice::query()->withoutGlobalScope(OrganizationScope::class)
                ->where('gateway_invoice_id', $invoiceId)
                ->update(['status' => 'open']);
        }

        return $organization->id;
    }
}
