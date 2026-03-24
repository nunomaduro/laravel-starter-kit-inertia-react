<?php

declare(strict_types=1);

namespace Modules\Billing\Providers;

use App\Events\OrganizationMemberAdded;
use App\Events\OrganizationMemberRemoved;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use LemonSqueezy\Laravel\Events\OrderCreated;
use Modules\Billing\Features\BillingFeature;
use Modules\Billing\Listeners\AddCreditsFromLemonSqueezyOrder;
use Modules\Billing\Listeners\SyncSubscriptionSeatsOnMemberChange;
use Modules\Billing\Models\Credit;
use Modules\Billing\Models\FailedPaymentAttempt;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Plan;
use Modules\Billing\Models\RefundRequest;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\WebhookLog;
use Modules\Billing\Observers\FailedPaymentAttemptObserver;
use Modules\Billing\Observers\InvoiceObserver;
use Modules\Billing\Observers\SubscriptionObserver;
use Modules\Billing\Policies\CreditPolicy;
use Modules\Billing\Policies\InvoicePolicy;
use Modules\Billing\Policies\PlanPolicy;
use Modules\Billing\Policies\RefundRequestPolicy;
use Modules\Billing\Policies\SubscriptionPolicy;
use Modules\Billing\Policies\WebhookLogPolicy;
use Modules\Billing\Services\PaymentGateway\PaymentGatewayManager;

final class BillingModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'billing',
            version: '1.0.0',
            description: 'Billing system with selectable payment gateway providers (Stripe, Paddle, LemonSqueezy).',
            models: [
                Credit::class,
                FailedPaymentAttempt::class,
                Invoice::class,
                Plan::class,
                RefundRequest::class,
                Subscription::class,
                WebhookLog::class,
            ],
            navigation: [
                ['label' => 'Billing', 'route' => 'billing.index', 'icon' => 'credit-card', 'group' => 'Organization'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return BillingFeature::class;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(PaymentGatewayManager::class);
    }

    protected function bootModule(): void
    {
        $this->registerPolicies();
        $this->registerObservers();
        $this->registerListeners();
        $this->loadGatewayRoutes();
    }

    private function registerPolicies(): void
    {
        Gate::policy(Credit::class, CreditPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(RefundRequest::class, RefundRequestPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(WebhookLog::class, WebhookLogPolicy::class);
    }

    private function registerObservers(): void
    {
        Invoice::observe(InvoiceObserver::class);
        Subscription::observe(SubscriptionObserver::class);
        FailedPaymentAttempt::observe(FailedPaymentAttemptObserver::class);
    }

    private function registerListeners(): void
    {
        Event::listen(OrganizationMemberAdded::class, SyncSubscriptionSeatsOnMemberChange::class);
        Event::listen(OrganizationMemberRemoved::class, SyncSubscriptionSeatsOnMemberChange::class);
        Event::listen(OrderCreated::class, AddCreditsFromLemonSqueezyOrder::class);
    }

    /**
     * Load gateway-specific routes based on the configured default gateway.
     */
    private function loadGatewayRoutes(): void
    {
        $gateway = config('billing.default_gateway', 'stripe');

        $gatewayRoutesPath = $this->moduleSourcePath('Gateways/'.$this->normalizeGatewayName($gateway).'/routes.php');

        if (file_exists($gatewayRoutesPath)) {
            $this->loadRoutesFrom($gatewayRoutesPath);
        }
    }

    private function normalizeGatewayName(string $gateway): string
    {
        return match ($gateway) {
            'stripe' => 'Stripe',
            'paddle' => 'Paddle',
            'lemon_squeezy', 'lemonsqueezy' => 'LemonSqueezy',
            default => ucfirst($gateway),
        };
    }
}
