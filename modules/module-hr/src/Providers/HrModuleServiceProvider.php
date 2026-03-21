<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Providers;

use App\Modules\Contracts\ProvidesAIContext;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Cogneiss\ModuleHr\Models\Department;
use Cogneiss\ModuleHr\Models\Employee;
use Cogneiss\ModuleHr\Models\LeaveRequest;

final class HrModuleServiceProvider extends ModuleProvider implements ProvidesAIContext
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'HR',
            version: '1.0.0',
            description: 'Human Resources management: employees, departments, leave requests',
            models: [
                Employee::class,
                Department::class,
                LeaveRequest::class,
            ],
            pages: [
                'hr.employees.index' => 'hr/employees/index',
                'hr.employees.create' => 'hr/employees/create',
                'hr.employees.edit' => 'hr/employees/edit',
            ],
            navigation: [
                ['label' => 'Employees', 'route' => 'hr.employees.index', 'icon' => 'users'],
                ['label' => 'Departments', 'route' => 'hr.departments.index', 'icon' => 'building'],
                ['label' => 'Leave Requests', 'route' => 'hr.leaves.index', 'icon' => 'calendar'],
            ],
        );
    }

    public function systemPrompt(): string
    {
        return <<<'PROMPT'
        ## HR Module
        This application manages human resources data:
        - **Employees**: Staff records with employee number, name, email, position, department, hire date, salary, and status (active/inactive/terminated)
        - **Departments**: Organizational units with a name, description, and optional department head
        - **Leave Requests**: Employee time-off requests with type (annual, sick, personal, unpaid), date range, reason, and approval status (pending, approved, rejected)

        Key relationships: Employees belong to Departments. Leave Requests belong to Employees. Departments have a head Employee.
        All data is scoped to the current organization (multi-tenant).
        PROMPT;
    }

    public function tools(): array
    {
        return [];
    }

    public function searchableModels(): array
    {
        return [
            Employee::class,
        ];
    }
}
