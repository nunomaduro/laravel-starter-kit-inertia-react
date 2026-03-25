<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Pgvector\Laravel\Vector;

final class ModelEmbedding extends Model
{
    protected $fillable = [
        'organization_id',
        'embeddable_type',
        'embeddable_id',
        'chunk_index',
        'embedding',
        'content_hash',
        'metadata',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function embeddable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
            'metadata' => 'array',
            'chunk_index' => 'integer',
        ];
    }
}
