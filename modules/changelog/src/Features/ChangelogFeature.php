<?php

declare(strict_types=1);

namespace Modules\Changelog\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class ChangelogFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
