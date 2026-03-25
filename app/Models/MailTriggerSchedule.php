<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MailTriggerScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $event_class
 * @property int|null $template_id
 * @property int|null $delay_minutes
 * @property bool $is_active
 * @property string|null $feature_flag
 * @property int|null $created_by
 */
final class MailTriggerSchedule extends Model
{
    /** @use HasFactory<MailTriggerScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'event_class',
        'template_id',
        'delay_minutes',
        'is_active',
        'feature_flag',
        'created_by',
    ];

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<MailTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MailTemplate::class, 'template_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delay_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
