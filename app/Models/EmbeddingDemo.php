<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class EmbeddingDemo extends Model
{
    use HasNeighbors;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use LogsActivity;

    protected $fillable = ['content', 'embedding'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logOnly(['content']);
    }

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
        ];
    }
}
