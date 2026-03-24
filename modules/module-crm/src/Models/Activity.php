<?php

declare(strict_types=1);

namespace Cogneiss\ModuleCrm\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Models\User;
use Cogneiss\ModuleCrm\Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Activity extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    protected $table = 'crm_activities';

    protected $fillable = [
        'organization_id',
        'contact_id',
        'deal_id',
        'user_id',
        'type',
        'subject',
        'description',
        'scheduled_at',
        'completed_at',
    ];

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<Deal, $this>
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
