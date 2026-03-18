<?php

declare(strict_types=1);

namespace Modules\Blog\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class BlogFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
