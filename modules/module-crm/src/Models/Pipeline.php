<?php

declare(strict_types=1);

namespace Cogneiss\ModuleCrm\Models;

use App\Models\Concerns\BelongsToOrganization;
use Cogneiss\ModuleCrm\Database\Factories\PipelineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Pipeline extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'crm_pipelines';

    protected $fillable = [
        'organization_id',
        'name',
        'stages',
        'is_default',
    ];

    /**
     * @return HasMany<Deal, $this>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    protected static function newFactory(): PipelineFactory
    {
        return PipelineFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stages' => 'array',
            'is_default' => 'boolean',
        ];
    }
}
