<?php

declare(strict_types=1);

namespace Cogneiss\ModuleCrm\Models;

use App\Models\Concerns\BelongsToOrganization;
use Cogneiss\ModuleCrm\Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Deal extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'crm_deals';

    protected $fillable = [
        'organization_id',
        'contact_id',
        'pipeline_id',
        'title',
        'value',
        'currency',
        'stage',
        'probability',
        'expected_close_date',
        'closed_at',
        'status',
    ];

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<Pipeline, $this>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    protected static function newFactory(): DealFactory
    {
        return DealFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }
}
