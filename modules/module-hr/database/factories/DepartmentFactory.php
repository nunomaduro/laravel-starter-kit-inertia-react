<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Database\Factories;

use App\Models\Organization;
use Cogneiss\ModuleHr\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
final class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->unique()->randomElement([
                'Engineering', 'Marketing', 'Sales', 'Human Resources',
                'Finance', 'Operations', 'Legal', 'Customer Support',
                'Product', 'Design', 'Research', 'Quality Assurance',
            ]),
            'description' => fake()->sentence(),
            'head_employee_id' => null,
        ];
    }

    public function withHead(int $employeeId): self
    {
        return $this->state(fn (array $attributes): array => [
            'head_employee_id' => $employeeId,
        ]);
    }
}
