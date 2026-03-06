<?php

declare(strict_types=1);

namespace App\Features;

use Illuminate\Support\Facades\App;
use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class ComponentShowcaseFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = false;

    public function resolve(): bool
    {
        return App::environment(['local', 'staging']);
    }
}
