<?php

declare(strict_types=1);

namespace Modules\Reports\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Reports\Enums\OutputFormat;
use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportOutput;

/**
 * @extends Factory<ReportOutput>
 */
final class ReportOutputFactory extends Factory
{
    protected $model = ReportOutput::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $format = fake()->randomElement(OutputFormat::cases());

        return [
            'report_id' => Report::factory(),
            'format' => $format->value,
            'disk' => 'local',
            'path' => 'reports/'.fake()->uuid().'.'.$format->value,
            'size_bytes' => fake()->numberBetween(1024, 10485760),
            'is_scheduled' => false,
        ];
    }

    public function scheduled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_scheduled' => true,
        ]);
    }

    public function pdf(): self
    {
        return $this->state(fn (array $attributes): array => [
            'format' => OutputFormat::Pdf->value,
            'path' => 'reports/'.fake()->uuid().'.pdf',
        ]);
    }

    public function csv(): self
    {
        return $this->state(fn (array $attributes): array => [
            'format' => OutputFormat::Csv->value,
            'path' => 'reports/'.fake()->uuid().'.csv',
        ]);
    }
}
