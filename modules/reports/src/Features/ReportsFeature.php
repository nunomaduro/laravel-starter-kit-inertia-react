<?php

declare(strict_types=1);

namespace Modules\Reports\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class ReportsFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
