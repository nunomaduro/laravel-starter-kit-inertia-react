<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Actions;

use Cogneiss\ModuleHr\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

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
