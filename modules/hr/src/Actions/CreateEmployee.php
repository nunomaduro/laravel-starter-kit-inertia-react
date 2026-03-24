<?php

declare(strict_types=1);

namespace Modules\Hr\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Hr\Models\Employee;

final readonly class CreateEmployee
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Employee
    {
        return DB::transaction(fn (): Employee => Employee::query()->create($data));
    }
}
