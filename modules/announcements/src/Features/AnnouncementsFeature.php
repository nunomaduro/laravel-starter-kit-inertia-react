<?php

declare(strict_types=1);

namespace Modules\Announcements\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class AnnouncementsFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
