<?php

declare(strict_types=1);

namespace Modules\Billing;

use App\Events\OrganizationMemberAdded;
use App\Events\OrganizationMemberRemoved;
use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use LemonSqueezy\Laravel\Events\OrderCreated;
use Modules\Billing\Features\BillingFeature;
use Modules\Billing\Listeners\AddCreditsFromLemonSqueezyOrder;
use Modules\Billing\Listeners\SyncSubscriptionSeatsOnMemberChange;
use Modules\Billing\Models\FailedPaymentAttempt;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Observers\FailedPaymentAttemptObserver;
use Modules\Billing\Observers\InvoiceObserver;
use Modules\Billing\Observers\SubscriptionObserver;
use Modules\Billing\Services\PaymentGateway\PaymentGatewayManager;

final class BillingServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'billing';
    }

    public function featureKey(): string
    {
        return 'billing';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return BillingFeature::class;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(PaymentGatewayManager::class);
    }

    protected function bootModule(): void
    {
        $this->registerObservers();
        $this->registerListeners();
        $this->loadGatewayRoutes();
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

        $gatewayRoutesPath = __DIR__.'/Gateways/'.$this->normalizeGatewayName($gateway).'/routes.php';

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
