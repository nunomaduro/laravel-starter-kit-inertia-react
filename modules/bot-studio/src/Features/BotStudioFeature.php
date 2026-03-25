<?php

declare(strict_types=1);

namespace Modules\BotStudio\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class BotStudioFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
