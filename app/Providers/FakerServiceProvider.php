<?php

declare(strict_types=1);

namespace App\Providers;

use Faker\Generator;
use Illuminate\Support\ServiceProvider;

final class FakerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->extend(Generator::class,
            // Add custom Faker providers here
            // Example: $faker->addProvider(new CustomProvider($faker));
            fn (Generator $faker): Generator => $faker);
    }
}
