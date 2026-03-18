<?php

declare(strict_types=1);

namespace Modules\Gamification\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class GamificationFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
