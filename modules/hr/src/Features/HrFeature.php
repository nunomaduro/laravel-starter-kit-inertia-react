<?php

declare(strict_types=1);

namespace Modules\Hr\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class HrFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
