<?php

declare(strict_types=1);

namespace Modules\Hr\Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Hr\Models\Department;
use Modules\Hr\Models\Employee;

/**
 * @extends Factory<Employee>
 */
final class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'employee_number' => fake()->unique()->numerify('EMP-####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'termination_date' => null,
            'salary' => fake()->randomFloat(2, 30000, 200000),
            'status' => 'active',
        ];
    }

    public function terminated(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'terminated',
            'termination_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function onLeave(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'on_leave',
        ]);
    }
}
