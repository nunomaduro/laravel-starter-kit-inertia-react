<?php

declare(strict_types=1);

namespace App\Services\PaymentGateway;

use App\Models\Billing\PaymentGateway as PaymentGatewayModel;
use App\Services\PaymentGateway\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateway\Gateways\LemonSqueezyGateway;
use App\Services\PaymentGateway\Gateways\ManualGateway;
use App\Services\PaymentGateway\Gateways\PaddleGateway;
use App\Services\PaymentGateway\Gateways\StripeGateway;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

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
