<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Actions;

use Cogneiss\ModuleHr\Models\Employee;
use Illuminate\Support\Facades\DB;

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
