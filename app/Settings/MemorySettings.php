<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class MemorySettings extends Settings
{
    public int $dimensions = 1536;

    public float $similarity_threshold = 0.5;

    public int $recall_limit = 10;

    public int $middleware_recall_limit = 5;

    public int $recall_oversample_factor = 2;

    public string $table = 'memories';

    public static function group(): string
    {
        return 'memory';
    }
}
