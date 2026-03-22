<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Models\Credit;
use Modules\Billing\Models\CreditPack;
use Modules\Billing\Services\PaymentGateway\PaymentGatewayManager;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final readonly class CreditController
{
    public function __construct(
        private PaymentGatewayManager $gateway
    ) {}

    public function index(): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 403, 'No organization selected.');
        $credits = Credit::query()
            ->where('organization_id', $organization->id)
            ->where('creditable_type', $organization->getMorphClass())
            ->where('creditable_id', $organization->getKey())->latest()
            ->limit(50)
            ->get();
        $packs = CreditPack::query()->where('is_active', true)->orderBy('sort_order')->get();

        $lemonSqueezyAvailable = config('services.lemon_squeezy.generic_variant_id')
            && config('services.lemon_squeezy.api_key')
            && config('services.lemon_squeezy.store_id');

        return Inertia::render('billing/credits', [
            'creditBalance' => $organization->creditBalance(),
            'transactions' => $credits,
            'creditPacks' => $packs,
            'lemonSqueezyAvailable' => $lemonSqueezyAvailable,
        ]);
    }

    public function purchase(Request $request): HttpResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 403, 'No organization selected.');

        $packId = $request->integer('credit_pack_id');
        $pack = CreditPack::query()->where('id', $packId)->where('is_active', true)->firstOrFail();

        $organization->purchaseCreditPack($pack);

        return back()->with('success', 'Credits added successfully.');
    }

    public function checkoutLemonSqueezy(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 403, 'No organization selected.');

        $packId = $request->integer('credit_pack_id');
        $pack = CreditPack::query()->where('id', $packId)->where('is_active', true)->firstOrFail();

        $gateway = $this->gateway->resolve('lemon_squeezy');
        $model = \Modules\Billing\Models\PaymentGateway::query()
            ->where('type', 'lemon_squeezy')
            ->where('is_active', true)
            ->first();
        if ($model instanceof \Modules\Billing\Models\PaymentGateway && $gateway instanceof \Modules\Billing\Services\PaymentGateway\Gateways\LemonSqueezyGateway) {
            $gateway->setGatewayModel($model);
        }

        $totalCents = $pack->price;
        $totalCredits = $pack->credits + $pack->bonus_credits;

        $lineItems = [
            [
                'name' => $pack->name,
                'amount' => $totalCents,
                'quantity' => 1,
                'credits' => $totalCredits,
                'credit_pack_id' => $pack->id,
            ],
        ];

        $successUrl = route('billing.credits.index').'?success=1';
        $cancelUrl = route('billing.credits.index');

        $url = $gateway->createCheckoutSession($organization, $lineItems, $successUrl, $cancelUrl);

        return redirect()->away($url);
    }
}
