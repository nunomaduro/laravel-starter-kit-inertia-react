<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use Illuminate\Database\Seeder;

final class BillingSeeder extends Seeder
{
    private array $dependencies = ['CreditPackSeeder'];

    public function run(): void
    {
        $this->call(CreditPackSeeder::class);
    }
}
