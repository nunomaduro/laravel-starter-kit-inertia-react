<?php

declare(strict_types=1);

namespace Modules\Dashboards\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Dashboards\Models\Dashboard;

/**
 * @extends Factory<Dashboard>
 */
final class DashboardFactory extends Factory
{
    protected $model = Dashboard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'puck_json' => null,
            'is_default' => false,
            'refresh_interval' => null,
        ];
    }

    public function default(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }

    public function withRefreshInterval(int $seconds = 30): self
    {
        return $this->state(fn (array $attributes): array => [
            'refresh_interval' => $seconds,
        ]);
    }

    /**
     * @param  array<string, mixed>  $puckJson
     */
    public function withPuckJson(array $puckJson = []): self
    {
        return $this->state(fn (array $attributes): array => [
            'puck_json' => $puckJson ?: ['content' => [], 'root' => ['props' => []]],
        ]);
    }
}
