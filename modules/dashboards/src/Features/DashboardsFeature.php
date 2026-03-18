<?php

declare(strict_types=1);

namespace Modules\Dashboards\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class DashboardsFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
