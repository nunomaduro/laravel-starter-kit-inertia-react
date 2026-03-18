<?php

declare(strict_types=1);

namespace Modules\Help\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class HelpFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
