<?php

declare(strict_types=1);

namespace Modules\Contact\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class ContactFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
