<?php

declare(strict_types=1);

namespace Modules\Reports\Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Reports\Enums\OutputFormat;
use Modules\Reports\Models\Report;

/**
 * @extends Factory<Report>
 */
final class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'puck_json' => null,
            'schedule' => null,
            'output_format' => fake()->randomElement(OutputFormat::cases()),
        ];
    }

    public function scheduled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'schedule' => '0 9 * * 1',
        ]);
    }

    public function pdf(): self
    {
        return $this->state(fn (array $attributes): array => [
            'output_format' => OutputFormat::Pdf,
        ]);
    }

    public function csv(): self
    {
        return $this->state(fn (array $attributes): array => [
            'output_format' => OutputFormat::Csv,
        ]);
    }

    public function html(): self
    {
        return $this->state(fn (array $attributes): array => [
            'output_format' => OutputFormat::Html,
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
