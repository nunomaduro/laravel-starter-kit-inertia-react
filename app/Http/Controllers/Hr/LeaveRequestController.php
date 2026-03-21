<?php

declare(strict_types=1);

namespace App\Http\Controllers\Hr;

use App\Http\Requests\Hr\StoreLeaveRequestRequest;
use Cogneiss\ModuleHr\Actions\CreateLeaveRequest;
use Cogneiss\ModuleHr\Models\Employee;
use Cogneiss\ModuleHr\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class LeaveRequestController
{
    public function index(Request $request): Response
    {
        $leaveRequests = LeaveRequest::query()
            ->with('employee')
            ->latest()
            ->paginate(15);

        return Inertia::render('hr/leave-requests/index', [
            'leaveRequests' => $leaveRequests,
        ]);
    }

    public function create(): Response
    {
        $employees = Employee::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('hr/leave-requests/create', [
            'employees' => $employees,
        ]);
    }

    public function store(StoreLeaveRequestRequest $request, CreateLeaveRequest $action): RedirectResponse
    {
        $action->handle($request->validated());

        return to_route('hr.leave-requests.index')
            ->with('status', __('Leave request created.'));
    }

    public function show(LeaveRequest $leaveRequest): RedirectResponse
    {
        return to_route('hr.leave-requests.index');
    }

    public function edit(LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->load('employee');

        $employees = Employee::query()
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return Inertia::render('hr/leave-requests/edit', [
            'leaveRequest' => $leaveRequest,
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update($request->validate([
            'type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]));

        return to_route('hr.leave-requests.index')
            ->with('status', __('Leave request updated.'));
    }

    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->delete();

        return to_route('hr.leave-requests.index')
            ->with('status', __('Leave request deleted.'));
    }
}
