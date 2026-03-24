<?php

declare(strict_types=1);

namespace Modules\Hr\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Hr\Database\Factories\DepartmentFactory;

final class Department extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'hr_departments';

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'head_employee_id',
    ];

    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    protected static function newFactory(): DepartmentFactory
    {
        return DepartmentFactory::new();
    }
}
