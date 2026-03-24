<?php

declare(strict_types=1);

namespace Modules\Crm\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class CrmFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
