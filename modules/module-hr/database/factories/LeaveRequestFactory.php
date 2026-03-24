<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Database\Factories;

use App\Models\Organization;
use Cogneiss\ModuleHr\Models\Employee;
use Cogneiss\ModuleHr\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
final class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, (clone $startDate)->modify('+14 days'));

        return [
            'organization_id' => Organization::factory(),
            'employee_id' => Employee::factory(),
            'type' => fake()->randomElement(['annual', 'sick', 'personal', 'maternity', 'paternity', 'unpaid']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => fake()->sentence(),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function approved(int $approverEmployeeId): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'approved',
            'approved_by' => $approverEmployeeId,
            'approved_at' => now(),
        ]);
    }

    public function rejected(int $approverEmployeeId): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'rejected',
            'approved_by' => $approverEmployeeId,
            'approved_at' => now(),
        ]);
    }

    public function sick(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'sick',
        ]);
    }

    public function annual(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'annual',
        ]);
    }
}
