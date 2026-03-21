<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hr;

use App\Http\Requests\Hr\StoreEmployeeRequest;
use App\Http\Requests\Hr\UpdateEmployeeRequest;
use Cogneiss\ModuleHr\Actions\CreateEmployee;
use Cogneiss\ModuleHr\Actions\UpdateEmployee;
use Cogneiss\ModuleHr\Models\Department;
use Cogneiss\ModuleHr\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class EmployeeController
{
    public function index(Request $request): Response
    {
        $employees = Employee::query()
            ->with('department')
            ->latest()
            ->paginate(15);

        return Inertia::render('hr/employees/index', [
            'employees' => $employees,
        ]);
    }

    public function create(): Response
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('hr/employees/create', [
            'departments' => $departments,
        ]);
    }

    public function store(StoreEmployeeRequest $request, CreateEmployee $action): RedirectResponse
    {
        $action->handle($request->validated());

        return to_route('hr.employees.index')
            ->with('status', __('Employee created.'));
    }

    public function show(Employee $employee): RedirectResponse
    {
        return to_route('hr.employees.edit', $employee);
    }

    public function edit(Employee $employee): Response
    {
        $employee->load('department');

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('hr/employees/edit', [
            'employee' => $employee,
            'departments' => $departments,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, UpdateEmployee $action): RedirectResponse
    {
        $action->handle($employee, $request->validated());

        return to_route('hr.employees.index')
            ->with('status', __('Employee updated.'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return to_route('hr.employees.index')
            ->with('status', __('Employee deleted.'));
    }
}
