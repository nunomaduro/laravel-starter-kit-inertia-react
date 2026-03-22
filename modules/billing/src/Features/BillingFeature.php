<?php

declare(strict_types=1);

namespace Modules\Billing\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class BillingFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
