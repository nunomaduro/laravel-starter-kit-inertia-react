<?php

declare(strict_types=1);

namespace Modules\Hr\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\LeaveRequest;

final readonly class CreateLeaveRequest
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): LeaveRequest
    {
        return DB::transaction(fn (): LeaveRequest => LeaveRequest::query()->create($data));
    }
}
