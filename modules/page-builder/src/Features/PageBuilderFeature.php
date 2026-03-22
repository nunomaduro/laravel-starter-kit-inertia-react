<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class PageBuilderFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
