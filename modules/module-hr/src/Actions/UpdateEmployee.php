<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Actions;

use Cogneiss\ModuleHr\Models\Employee;
use Illuminate\Support\Facades\DB;

final readonly class UpdateEmployee
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data): Employee {
            $employee->update($data);

            return $employee->refresh();
        });
    }
}
