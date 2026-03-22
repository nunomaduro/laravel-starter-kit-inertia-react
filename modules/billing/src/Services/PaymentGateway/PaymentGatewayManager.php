<?php

declare(strict_types=1);

namespace Modules\Billing\Services\PaymentGateway;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Modules\Billing\Models\PaymentGateway as PaymentGatewayModel;
use Modules\Billing\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use Modules\Billing\Services\PaymentGateway\Gateways\LemonSqueezyGateway;
use Modules\Billing\Services\PaymentGateway\Gateways\ManualGateway;
use Modules\Billing\Services\PaymentGateway\Gateways\PaddleGateway;
use Modules\Billing\Services\PaymentGateway\Gateways\StripeGateway;

final class PaymentGatewayManager
{
    /**
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    private array $gateways = [
        'none' => ManualGateway::class,
        'stripe' => StripeGateway::class,
        'paddle' => PaddleGateway::class,
        'lemon_squeezy' => LemonSqueezyGateway::class,
        'manual' => ManualGateway::class,
    ];

    public function driver(?string $type = null): PaymentGatewayInterface
    {
        $type ??= config('billing.default_gateway', 'stripe');
        if ($type === 'none' || $type === '') {
            $type = 'manual';
        }

        $model = $this->getDefaultGatewayModel();
        if ($model && $model->type->value === $type) {
            return $this->resolveFromModel($model);
        }

        return $this->resolve($type);
    }

    public function resolve(string $type): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$type])) {
            throw new InvalidArgumentException("Unsupported payment gateway type: {$type}");
        }

        return resolve($this->gateways[$type]);
    }

    public function resolveFromModel(PaymentGatewayModel $model): PaymentGatewayInterface
    {
        $gateway = $this->resolve($model->type->value);
        if (method_exists($gateway, 'setGatewayModel')) {
            $gateway->setGatewayModel($model);
        }

        return $gateway;
    }

    public function extend(string $type, string $class): void
    {
        $this->gateways[$type] = $class;
    }

    private function getDefaultGatewayModel(): ?PaymentGatewayModel
    {
        return Cache::remember('billing.default_gateway_model', 3600, fn () => PaymentGatewayModel::query()->where('is_default', true)->where('is_active', true)->first());
    }
}
