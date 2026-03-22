<?php

declare(strict_types=1);

namespace Modules\Workflows\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class WorkflowsFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
