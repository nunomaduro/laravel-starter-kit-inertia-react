<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelFlags\Models\Flag as BaseFlag;

final class ModelFlag extends BaseFlag
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use LogsActivity;

    protected $table = 'model_flags';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll();
    }
}
