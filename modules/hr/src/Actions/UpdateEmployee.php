<?php

declare(strict_types=1);

namespace Modules\Hr\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\Employee;

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
