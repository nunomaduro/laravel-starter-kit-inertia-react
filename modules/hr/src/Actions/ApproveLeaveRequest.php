<?php

declare(strict_types=1);

namespace Modules\Hr\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\Employee;
use Modules\Hr\Models\LeaveRequest;

final readonly class ApproveLeaveRequest
{
    public function handle(LeaveRequest $leaveRequest, Employee $approver, string $status = 'approved'): LeaveRequest
    {
        return DB::transaction(function () use ($leaveRequest, $approver, $status): LeaveRequest {
            $leaveRequest->update([
                'status' => $status,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $leaveRequest->refresh();
        });
    }
}
