<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\File;
use RuntimeException;

trait LoadsJsonData
{
    /**
     * Load JSON data from the seeders data directory.
     *
     * @return array<string, mixed>
     */
    protected function loadJson(string $filename): array
    {
        $path = database_path('seeders/data/'.$filename);

        throw_unless(File::exists($path), RuntimeException::class, 'JSON file not found: '.$path);

        $contents = File::get($path);
        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        throw_unless(is_array($data), RuntimeException::class, 'Invalid JSON structure in file: '.$filename);

        return $data;
    }
}
